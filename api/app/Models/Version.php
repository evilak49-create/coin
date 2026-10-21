<?php

namespace App\Models;

class Version extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'version_code',
        'version_number',
        'platform',
        'channel_code',
        'package_name',
        'download_url',
        'update_log',
        'force_update',
        'status',
        'sort',
        'download_count',
        'updated_at'
    ];

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 平台常量
     */
    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS = 'ios';

    /**
     * 应用搜索条件到查询构建器
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function applyFilters($query, $filters)
    {
        // 按版本号搜索
        if (!empty($filters['version_code'])) {
            $query->where('version_code', 'like', '%' . $filters['version_code'] . '%');
        }

        // 按平台搜索
        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        // 按渠道编号搜索
        if (!empty($filters['channel_code'])) {
            $query->where('channel_code', 'like', '%' . $filters['channel_code'] . '%');
        }

        // 按状态搜索
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * 获取版本列表
     *
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getList($filters = [], $page = 1, $limit = 20)
    {
        $query = self::query();
        $query = self::applyFilters($query, $filters);

        $count = $query->count();

        $offset = ($page - 1) * $limit;
        $list = $query->offset($offset)
            ->limit($limit)
            ->orderBy('version_number', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        return ['total_num' => $count, 'list' => $list];
    }

    /**
     * 根据ID获取版本
     *
     * @param int $id
     * @return array|null
     */
    public static function getById($id)
    {
        $version = self::query()->where('id', $id)->first();
        return $version ? $version->toArray() : null;
    }


    /**
     * 版本号转换为数字（如：1.0.0 -> 100）
     *
     * @param string $versionCode 版本号（如：1.0.0）
     * @return int
     */
    public static function versionCodeToNumber($versionCode)
    {
        $parts = explode('.', $versionCode);
        $number = 0;
        $multiplier = 10000; // 支持 x.xx.xx 格式

        foreach ($parts as $part) {
            $number += intval($part) * $multiplier;
            $multiplier = intval($multiplier / 100);
        }

        return $number;
    }
}

