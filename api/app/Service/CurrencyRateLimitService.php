<?php

namespace App\Service;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 币种API限流服务
 * 基于Redis缓存实现每个币种的限频控制
 */
class CurrencyRateLimitService
{
    /**
     * 缓存键前缀
     */
    const CACHE_PREFIX = 'currency_rate_limit:';

    /**
     * 默认限流配置（每个币种的默认限流规则）
     * 格式：['币种' => ['max_requests' => 最大请求数, 'per_seconds' => 时间窗口（秒）]]
     */
    private static $defaultLimits = [
        'BTC' => ['max_requests' => 5, 'per_seconds' => 1],      // 每秒最多5次
        'ETH' => ['max_requests' => 5, 'per_seconds' => 1],      // 每秒最多5次
        'BSC' => ['max_requests' => 5, 'per_seconds' => 1],      // 每秒最多5次
        'TRON' => ['max_requests' => 1, 'per_seconds' => 1],    // 每秒最多10次
        'SOL' => ['max_requests' => 5, 'per_seconds' => 1],     // 每秒最多10次
        'TON' => ['max_requests' => 5, 'per_seconds' => 1],     // 每秒最多10次
        'XRP' => ['max_requests' => 5, 'per_seconds' => 1],      // 每秒最多5次
        'ERC20USDT' => ['max_requests' => 5, 'per_seconds' => 1], // 每秒最多5次（使用ETH API）
        'BEP20USDT' => ['max_requests' => 5, 'per_seconds' => 1], // 每秒最多5次（使用BSC API）
        'TRC20USDT' => ['max_requests' => 1, 'per_seconds' => 1], // 每秒最多10次（使用TRON API）
    ];

    /**
     * 检查是否可以执行请求（是否达到限流）
     *
     * @param string $currencyType 币种类型
     * @return bool true=可以执行，false=达到限流需要等待
     */
    public static function canExecute(string $currencyType): bool
    {
        $limit = self::getLimit($currencyType);
        $cacheKey = self::getCacheKey($currencyType);
        
        // 获取当前时间窗口内的请求次数
        $currentCount = Cache::get($cacheKey, 0);
        
        // 如果未达到限制，允许执行
        return $currentCount < $limit['max_requests'];
    }

    /**
     * 记录一次请求（增加计数器）
     *
     * @param string $currencyType 币种类型
     * @return void
     */
    public static function recordRequest(string $currencyType): void
    {
        $limit = self::getLimit($currencyType);
        $cacheKey = self::getCacheKey($currencyType);
        
        try {
            // 使用原子操作增加计数器并设置过期时间
            // 如果key不存在，先设置初始值和过期时间
            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, 1, $limit['per_seconds']);
                $currentCount = 1;
            } else {
                // 如果key已存在，增加计数
                $currentCount = Cache::increment($cacheKey);
            }
        } catch (\Exception $e) {
            // 如果缓存操作失败，记录日志但不影响主流程
            Log::warning('记录币种限流计数失败', [
                'currency_type' => $currencyType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取需要等待的时间（秒）
     * 如果达到限流，返回需要等待的秒数；否则返回0
     *
     * @param string $currencyType 币种类型
     * @return int 需要等待的秒数（0表示不需要等待）
     */
    public static function getWaitSeconds(string $currencyType): int
    {
        if (self::canExecute($currencyType)) {
            return 0;
        }
        
        $limit = self::getLimit($currencyType);
        
        try {
            // 尝试从Redis获取TTL
            $cacheKey = self::getCacheKey($currencyType);
            $redis = Cache::getRedis()->connection();
            $ttl = $redis->ttl($cacheKey);
            
            // 如果TTL有效，返回剩余时间+1（确保完全过期）
            return $ttl > 0 ? ($ttl + 1) : $limit['per_seconds'];
        } catch (\Exception $e) {
            // 如果无法获取TTL，返回时间窗口
            return $limit['per_seconds'];
        }
    }

    /**
     * 获取当前时间窗口内的请求次数
     *
     * @param string $currencyType 币种类型
     * @return int
     */
    public static function getCurrentCount(string $currencyType): int
    {
        $cacheKey = self::getCacheKey($currencyType);
        return Cache::get($cacheKey, 0);
    }

    /**
     * 清除限流记录（重置计数器）
     *
     * @param string $currencyType 币种类型
     * @return void
     */
    public static function clearLimit(string $currencyType): void
    {
        $cacheKey = self::getCacheKey($currencyType);
        Cache::forget($cacheKey);
    }

    /**
     * 获取缓存键名
     *
     * @param string $currencyType
     * @return string
     */
    private static function getCacheKey(string $currencyType): string
    {
        // 使用时间窗口的起始时间作为键的一部分，实现滑动窗口
        // 这里简化实现，使用固定时间窗口
        return self::CACHE_PREFIX . strtolower($currencyType);
    }

    /**
     * 获取币种的限流配置
     *
     * @param string $currencyType
     * @return array
     */
    private static function getLimit(string $currencyType): array
    {
        return self::$defaultLimits[$currencyType] ?? ['max_requests' => 5, 'per_seconds' => 1];
    }

    /**
     * 设置币种的限流配置（动态配置）
     *
     * @param string $currencyType
     * @param int $maxRequests
     * @param int $perSeconds
     * @return void
     */
    public static function setLimit(string $currencyType, int $maxRequests, int $perSeconds): void
    {
        self::$defaultLimits[$currencyType] = [
            'max_requests' => $maxRequests,
            'per_seconds' => $perSeconds,
        ];
    }

    /**
     * 获取所有币种的限流配置
     *
     * @return array
     */
    public static function getAllLimits(): array
    {
        return self::$defaultLimits;
    }
}

