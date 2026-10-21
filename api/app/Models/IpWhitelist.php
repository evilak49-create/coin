<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class IpWhitelist extends BaseModel
{

    // 状态常量
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    // 类型常量
    const TYPE_WHITELIST = 'whitelist';
    const TYPE_BLACKLIST = 'blacklist';

    // 缓存前缀
    const CACHE_PREFIX = 'ip_whitelist:';
    // 缓存过期时间（秒），默认24小时
    const CACHE_TTL = 86400;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip', 'type', 'description', 'status', 'updated_at'
    ];

    /**
     * 获取状态文本
     *
     * @return array
     */
    public static function getStatusText()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }

    /**
     * 获取类型文本
     *
     * @return array
     */
    public static function getTypeText()
    {
        return [
            self::TYPE_WHITELIST => '白名单',
            self::TYPE_BLACKLIST => '黑名单',
        ];
    }

    /**
     * 获取缓存键名
     *
     * @param string $key
     * @return string
     */
    private static function getCacheKey($key)
    {
        return self::CACHE_PREFIX . $key;
    }

    /**
     * 清除所有IP白名单缓存
     *
     * @return void
     */
    public static function clearAllCache()
    {
        Cache::store('redis')->forget(self::CACHE_PREFIX . 'list:*');
        Cache::store('redis')->forget(self::CACHE_PREFIX . 'whitelist:all');
        Cache::store('redis')->forget(self::CACHE_PREFIX . 'blacklist:all');
    }

    /**
     * 清除指定ID的缓存
     *
     * @param int $id
     * @return void
     */
    public static function clearCacheById($id)
    {
        Cache::store('redis')->forget(self::getCacheKey('id:' . $id));
    }

    /**
     * 获取IP白名单列表（带缓存）
     *
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getList($filters = [], $page = 1, $limit = 20)
    {
        // 获取版本号，用于缓存失效
        $version = Cache::store('redis')->get(self::getCacheKey('list_version')) ?: 0;
        // 构建缓存键（包含版本号，版本号变化时缓存键也会变化，从而失效旧缓存）
        $cacheKey = self::getCacheKey('list:' . md5(json_encode($filters) . '_' . $page . '_' . $limit) . '_v' . $version);

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($filters, $page, $limit) {
            $model = self::query();

            // 按IP搜索
            if (!empty($filters['ip'])) {
                $model->where('ip', 'like', '%' . $filters['ip'] . '%');
            }

            // 按类型筛选
            if (!empty($filters['type'])) {
                $model->where('type', $filters['type']);
            }

            // 按状态筛选
            if (isset($filters['status'])) {
                $model->where('status', $filters['status']);
            }

            $count = $model->count();
            $offset = ($page - 1) * $limit;
            $list = $model->offset($offset)
                ->limit($limit)
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            return ['total_num' => $count, 'list' => $list];
        });
    }

    /**
     * 根据ID获取IP白名单（带缓存）
     *
     * @param int $id
     * @return array|null
     */
    public static function getById($id)
    {
        $cacheKey = self::getCacheKey('id:' . $id);

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $ipWhitelist = self::query()->where('id', $id)->first();
            return $ipWhitelist ? $ipWhitelist->toArray() : null;
        });
    }

    /**
     * 获取所有启用的白名单IP列表（带缓存）
     *
     * @return array
     */
    public static function getAllWhitelistIps()
    {
        $cacheKey = self::getCacheKey('whitelist:all');

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () {
            return self::query()
                ->where('type', self::TYPE_WHITELIST)
                ->where('status', self::STATUS_ENABLED)
                ->pluck('ip')
                ->toArray();
        });
    }

    /**
     * 获取所有启用的黑名单IP列表（带缓存）
     *
     * @return array
     */
    public static function getAllBlacklistIps()
    {
        $cacheKey = self::getCacheKey('blacklist:all');

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () {
            return self::query()
                ->where('type', self::TYPE_BLACKLIST)
                ->where('status', self::STATUS_ENABLED)
                ->pluck('ip')
                ->toArray();
        });
    }

    /**
     * 检查IP是否在白名单中
     *
     * @param string $ip 要检查的IP地址
     * @return bool
     */
    public static function isWhitelisted($ip)
    {
        $whitelistIps = self::getAllWhitelistIps();
        
        foreach ($whitelistIps as $whitelistIp) {
            if (self::ipMatches($ip, $whitelistIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查IP是否在黑名单中
     *
     * @param string $ip 要检查的IP地址
     * @return bool
     */
    public static function isBlacklisted($ip)
    {
        $blacklistIps = self::getAllBlacklistIps();
        
        foreach ($blacklistIps as $blacklistIp) {
            if (self::ipMatches($ip, $blacklistIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查IP是否匹配（支持CIDR格式）
     *
     * @param string $ip 要检查的IP地址
     * @param string $rule IP规则（可以是单个IP或CIDR格式）
     * @return bool
     */
    public static function ipMatches($ip, $rule)
    {
        // 如果是CIDR格式（包含/）
        if (strpos($rule, '/') !== false) {
            list($subnet, $mask) = explode('/', $rule);
            
            // 将IP和子网转换为长整型
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            
            if ($ipLong === false || $subnetLong === false) {
                return false;
            }
            
            // 计算掩码
            $maskLong = -1 << (32 - (int)$mask);
            
            // 检查IP是否在子网内
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }
        
        // 精确匹配
        return $ip === $rule;
    }

    /**
     * 清除列表缓存（通过设置版本号）
     *
     * @return void
     */
    public static function clearListCache()
    {
        // 通过更新版本号来使列表缓存失效
        $versionKey = self::getCacheKey('list_version');
        Cache::store('redis')->put($versionKey, time(), self::CACHE_TTL * 2);
    }

    /**
     * 模型更新后清除相关缓存
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // 保存后清除缓存
        static::saved(function ($ipWhitelist) {
            self::clearCacheById($ipWhitelist->id);
            self::clearAllCache();
            self::clearListCache();
        });

        // 删除后清除缓存
        static::deleted(function ($ipWhitelist) {
            self::clearCacheById($ipWhitelist->id);
            self::clearAllCache();
            self::clearListCache();
        });
    }

}

