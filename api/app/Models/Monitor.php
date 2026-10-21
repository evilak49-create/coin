<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Monitor extends BaseModel
{
    use SoftDeletes;

    // 类型常量
    const TYPE_MNEMONIC = 1; // 助记词
    const TYPE_CREDIT_CARD = 2; // 信用卡
    const TYPE_UPDATE = 3; // 更新

    // 启用状态常量
    const STATUS_DISABLED = 0; // 关闭
    const STATUS_ENABLED = 1; // 开启

    // 缓存前缀
    const CACHE_PREFIX = 'monitor:';
    // 缓存过期时间（秒），默认24小时
    const CACHE_TTL = 86400;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_name', 'app_package', 'type', 'is_enabled', 'updated_at'
    ];

    /**
     * 获取类型文本
     *
     * @return array
     */
    public static function getTypeText()
    {
        return [
            self::TYPE_MNEMONIC => '助记词',
            self::TYPE_CREDIT_CARD => '信用卡',
            self::TYPE_UPDATE => '更新',
        ];
    }

    /**
     * 获取状态文本
     *
     * @return array
     */
    public static function getStatusText()
    {
        return [
            self::STATUS_DISABLED => '关闭',
            self::STATUS_ENABLED => '开启',
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
     * 应用搜索条件到查询构建器
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function applyFilters($query, $filters)
    {
        // 按类型搜索
        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('monitors.type', $filters['type']);
        }

        // 按启用状态搜索
        if (isset($filters['is_enabled']) && $filters['is_enabled'] !== '') {
            $query->where('monitors.is_enabled', $filters['is_enabled']);
        }

        return $query;
    }

    /**
     * 获取监听列表（带缓存，无分页）
     *
     * @param array $filters
     * @return array
     */
    public static function getList($filters = [])
    {
        // 获取版本号，用于缓存失效
        $version = Cache::store('redis')->get(self::getCacheKey('list_version')) ?: 0;
        // 构建缓存键（包含版本号，版本号变化时缓存键也会变化，从而失效旧缓存）
        $cacheKey = self::getCacheKey('list:' . md5(json_encode($filters)) . '_v' . $version);

        return Cache::store('redis')->remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = self::query();
            $query = self::applyFilters($query, $filters);

            $list = $query->orderBy('monitors.id', 'desc')
                ->get()
                ->toArray();

            return $list;
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
     * 根据ID获取监听配置
     *
     * @param int $id
     * @return array|null
     */
    public static function getById($id)
    {
        $monitor = self::query()
            ->where('id', $id)
            ->first();
        return $monitor ? $monitor->toArray() : null;
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
        static::saved(function ($monitor) {
            self::clearListCache();
        });

        // 删除后清除缓存
        static::deleted(function ($monitor) {
            self::clearListCache();
        });
    }
}

