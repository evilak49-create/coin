<?php

namespace App\Cleaners;

use Hhxsv5\LaravelS\Illuminate\Cleaners\BaseCleaner;
use Illuminate\Support\Facades\Auth;

/**
 * 自定义 JWT Auth Cleaner
 * 用于在 LaravelS 环境中清理 JWT guard 的状态，避免不同请求之间的用户信息污染
 */
class JwtAuthCleaner extends BaseCleaner
{
    public function clean()
    {
        // 获取所有配置的 guards
        $guards = config('auth.guards', []);
        
        foreach ($guards as $name => $config) {
            // 只处理 JWT driver 的 guards
            if (isset($config['driver']) && $config['driver'] === 'jwt') {
                try {
                    $guard = Auth::guard($name);
                    
                    // 清理 JWT guard 的 token
                    if (method_exists($guard, 'unsetToken')) {
                        $guard->unsetToken();
                    }
                    
                    // 清理缓存的用户对象
                    if (property_exists($guard, 'user')) {
                        $reflection = new \ReflectionClass($guard);
                        $property = $reflection->getProperty('user');
                        $property->setAccessible(true);
                        $property->setValue($guard, null);
                    }
                    
                    // 如果有 lastAttempted 属性也需要清理
                    if (property_exists($guard, 'lastAttempted')) {
                        $reflection = new \ReflectionClass($guard);
                        $property = $reflection->getProperty('lastAttempted');
                        $property->setAccessible(true);
                        $property->setValue($guard, null);
                    }
                } catch (\Exception $e) {
                    // 忽略清理过程中的异常
                }
            }
        }
        
        // 重置 Auth Manager 中缓存的 guards
        try {
            $authManager = $this->currentApp->make('auth');
            if (method_exists($authManager, 'forgetGuards')) {
                $authManager->forgetGuards();
            } else {
                // 手动清理 guards 属性
                $reflection = new \ReflectionClass($authManager);
                if ($reflection->hasProperty('guards')) {
                    $property = $reflection->getProperty('guards');
                    $property->setAccessible(true);
                    $property->setValue($authManager, []);
                }
            }
        } catch (\Exception $e) {
            // 忽略异常
        }
    }
}

