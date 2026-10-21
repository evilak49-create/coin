<?php

namespace App\Models;

class Channel extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'url', 'remark', 'updated_at'
    ];

    /**
     * 应用搜索条件到查询构建器
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private static function applyFilters($query, $filters)
    {
        // 按编号搜索
        if (!empty($filters['code'])) {
            $query->where('channels.code', 'like', '%' . $filters['code'] . '%');
        }

        // 按名称搜索
        if (!empty($filters['name'])) {
            $query->where('channels.name', 'like', '%' . $filters['name'] . '%');
        }

        // 按网址搜索
        if (!empty($filters['url'])) {
            $query->where('channels.url', 'like', '%' . $filters['url'] . '%');
        }

        return $query;
    }

    /**
     * 获取渠道列表（优化版本 - 使用LEFT JOIN）
     * 
     * 方案1：使用LEFT JOIN + GROUP BY（性能最优，适合大数据量）
     * 优点：单次查询，性能好
     * 缺点：需要处理GROUP BY后的count问题
     *
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public static function getList($filters = [], $page = 1, $limit = 20)
    {
        // 构建基础查询用于统计总数（不包含JOIN）
        $countQuery = self::query();
        $countQuery = self::applyFilters($countQuery, $filters);
        $count = $countQuery->count();

        // 构建带设备数量统计的查询（使用LEFT JOIN优化性能）
        $query = self::query();
        $query = self::applyFilters($query, $filters);

        // 使用LEFT JOIN统计设备数量（性能优于子查询）
        $query->leftJoin('devices', function($join) {
            $join->on('channels.code', '=', 'devices.app_channel')
                 ->whereNull('devices.deleted_at');
        })
        ->select('channels.*')
        ->selectRaw('COUNT(devices.id) as device_count')
        ->groupBy('channels.id', 'channels.code', 'channels.name', 'channels.url', 'channels.remark', 'channels.created_at', 'channels.updated_at');

        $offset = ($page - 1) * $limit;
        $list = $query->offset($offset)
            ->limit($limit)
            ->orderBy('channels.id', 'desc')
            ->get()
            ->toArray();

        return ['total_num' => $count, 'list' => $list];
    }


    

    /**
     * 根据ID获取渠道
     *
     * @param int $id
     * @return array|null
     */
    public static function getById($id)
    {
        $channel = self::query()
            ->selectRaw('channels.*, (
                SELECT COUNT(*) 
                FROM devices 
                WHERE devices.app_channel = channels.code 
                AND devices.deleted_at IS NULL
            ) as device_count')
            ->where('id', $id)
            ->first();
        return $channel ? $channel->toArray() : null;
    }
}
