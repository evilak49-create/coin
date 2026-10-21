<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class SystemConfig extends BaseModel
{


/*
// 获取配置列表（自动缓存）
$result = SystemConfig::getList(['status' => 1], 1, 20);

// 根据key获取配置（自动缓存）
$config = SystemConfig::getByKey('site_name', true);

// 手动清除缓存
SystemConfig::clearCacheByKey('site_name');
SystemConfig::clearListCache();
*/

    // 状态常量
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    // 配置类型常量
    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_IMAGE = 'image';

    // 缓存前缀
    const CACHE_PREFIX = 'system_config:';
    // 缓存过期时间（秒），默认24小时
    const CACHE_TTL = 86400;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value', 'type', 'group', 'description', 'sort', 'status', 'updated_at'
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
     * 获取配置类型文本
     *
     * @return array
     */
    public static function getTypeText()
    {
        return [
            self::TYPE_STRING => '字符串',
            self::TYPE_NUMBER => '数字',
            self::TYPE_IMAGE => '图片',
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
     * 清除所有配置缓存
     *
     * @return void
     */
    public static function clearAllCache()
    {
        Cache::store('redis')->forget(self::CACHE_PREFIX . 'list:*');
        Cache::store('redis')->forget(self::CACHE_PREFIX . 'groups');
        Cache::store('redis')->forget(self::CACHE_PREFIX . 'group:*');
    }

    /**
     * 清除指定key的缓存
     *
     * @param string $key
     * @return void
     */
    public static function clearCacheByKey($key)
    {
        // 清除所有可能的缓存键（包括启用和未启用的）
        Cache::store('redis')->forget(self::getCacheKey('key:' . $key));
        Cache::store('redis')->forget(self::getCacheKey('key:' . $key . ':enabled'));
    }

    /**
     * 清除指定分组的缓存
     *
     * @param string $group
     * @return void
     */
    public static function clearCacheByGroup($group)
    {
        // 清除所有可能的缓存键（包括启用和未启用的）
        Cache::store('redis')->forget(self::getCacheKey('group:' . $group));
        Cache::store('redis')->forget(self::getCacheKey('group:' . $group . ':enabled'));
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
     * 获取配置列表（带缓存）
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

            // 按key搜索
            if (!empty($filters['key'])) {
                $model->where('key', 'like', '%' . $filters['key'] . '%');
            }

            // 按分组筛选
            if (!empty($filters['group'])) {
                $model->where('group', $filters['group']);
            }

            // 按状态筛选
            if (isset($filters['status'])) {
                $model->where('status', $filters['status']);
            }

            // 按类型筛选
            if (!empty($filters['type'])) {
                $model->where('type', $filters['type']);
            }

            $count = $model->count();
            $offset = ($page - 1) * $limit;
            $list = $model->offset($offset)
                ->limit($limit)
                ->orderBy('sort', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            return ['total_num' => $count, 'list' => $list];
        });
    }

    /**
     * 根据ID获取配置（带缓存）
     *
     * @param int $id
     * @return array|null
     */
    public static function getById($id)
    {
        $cacheKey = self::getCacheKey('id:' . $id);

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $config = self::query()->where('id', $id)->first();
            return $config ? $config->toArray() : null;
        });
    }

    /**
     * 根据key获取配置（带缓存）
     *
     * @param string $key
     * @param bool $onlyEnabled 是否只获取启用状态的配置
     * @return array|null
     */
    public static function getByKey($key, $onlyEnabled = true)
    {
        $cacheKey = self::getCacheKey('key:' . $key . ($onlyEnabled ? ':enabled' : ''));

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($key, $onlyEnabled) {
            $model = self::query()->where('key', $key);
            
            if ($onlyEnabled) {
                $model->where('status', self::STATUS_ENABLED);
            }

            $config = $model->first();
            
            if (empty($config)) {
                return null;
            }

            return [
                'key' => $config->key,
                'value' => $config->value,
                'type' => $config->type,
                'group' => $config->group,
                'description' => $config->description,
            ];
        });
    }

    /**
     * 根据分组获取配置列表（带缓存）
     *
     * @param string $group
     * @param bool $onlyEnabled 是否只获取启用状态的配置
     * @return array
     */
    public static function getListByGroup($group, $onlyEnabled = true)
    {
        $cacheKey = self::getCacheKey('group:' . $group . ($onlyEnabled ? ':enabled' : ''));

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($group, $onlyEnabled) {
            $model = self::query()->where('group', $group);
            
            if ($onlyEnabled) {
                $model->where('status', self::STATUS_ENABLED);
            }

            return $model->orderBy('sort', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * 获取所有分组列表（带缓存）
     *
     * @param bool $onlyEnabled 是否只获取启用状态配置的分组
     * @return array
     */
    public static function getAllGroups($onlyEnabled = true)
    {
        $cacheKey = self::getCacheKey('groups' . ($onlyEnabled ? ':enabled' : ''));

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($onlyEnabled) {
            $model = self::query();
            
            if ($onlyEnabled) {
                $model->where('status', self::STATUS_ENABLED);
            }

            return $model->distinct()
                ->pluck('group')
                ->toArray();
        });
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
        static::saved(function ($config) {
            self::clearCacheById($config->id);
            self::clearCacheByKey($config->key);
            self::clearCacheByGroup($config->group);
            self::clearListCache();
        });

        // 删除后清除缓存
        static::deleted(function ($config) {
            self::clearCacheById($config->id);
            self::clearCacheByKey($config->key);
            self::clearCacheByGroup($config->group);
            self::clearListCache();
        });
    }

}

