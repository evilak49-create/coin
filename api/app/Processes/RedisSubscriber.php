<?php

namespace App\Processes;

use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Swoole\Http\Server;
use Swoole\Process;

class RedisSubscriber implements CustomProcessInterface
{
    public static function callback(Server $server, Process $process)
    {
        \Log::info('Redis 订阅进程启动', ['pid' => $process->pid]);

        try {
            // 获取 Redis 配置
            $host = config('database.redis.default.host', '127.0.0.1');
            $port = config('database.redis.default.port', 6379);
            $password = config('database.redis.default.password', null);
            $prefix = config('database.redis.options.prefix', '');

            \Log::info('Redis 订阅进程配置', [
                'host' => $host,
                'port' => $port,
                'prefix' => $prefix,
            ]);

            // 使用原生 Redis 客户端（不在协程中，直接运行）
            $redis = new \Redis();
            $connected = $redis->connect($host, $port);
            
            if (!$connected) {
                \Log::error('Redis 连接失败', [
                    'host' => $host,
                    'port' => $port,
                ]);
                return;
            }

            if ($password) {
                $redis->auth($password);
            }

            // 设置读取超时为 -1（无限等待）
            $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);

            // 创建一个独立的 Redis 连接用于队列操作（订阅模式的连接不能用于其他操作）
            $redisQueue = new \Redis();
            $redisQueue->connect($host, $port);
            if ($password) {
                $redisQueue->auth($password);
            }

            // 订阅所有以设备通道开头的频道
            $pattern = $prefix . 'device.*';

            \Log::info('Redis 订阅进程开始监听频道模式', ['pattern' => $pattern]);

            // 订阅 Redis 频道（使用 psubscribe）
            // 使用回调函数处理消息
            \Log::info('准备订阅 Redis 频道', ['pattern' => $pattern]);
            
            // 订阅模式频道（psubscribe 是阻塞的，会一直运行）
            // 注意：回调函数的参数顺序：$redis, $pattern, $channel, $message
            $redis->psubscribe([$pattern], function ($redis, $matchedPattern, $channel, $message) use ($server, $prefix, $pattern, $redisQueue) {
                try {
                    \Log::info('Redis 收到消息（回调函数被调用）', [
                        'matched_pattern' => $matchedPattern,
                        'subscribed_pattern' => $pattern,
                        'channel' => $channel,
                        'message' => $message,
                        'message_length' => strlen($message),
                    ]);

                    // 移除前缀获取原始频道名
                    $originalChannel = str_replace($prefix, '', $channel);
                    
                    // 解析消息
                    $data = json_decode($message, true);
                    if (!$data) {
                        \Log::warning('Redis 消息解析失败', ['message' => $message]);
                        return;
                    }

                    // 获取事件名和数据
                    $event = $data['event'] ?? '';
                    $payload = $data['data'] ?? [];

                    // 如果 payload 中的 data 字段是空数组，转换为空对象（json_decode 会将 {} 转换为 []）
                    if (isset($payload['data']) && is_array($payload['data']) && count($payload['data']) === 0) {
                        $payload['data'] = (object)[];
                    }

                    \Log::info('通过 Redis 收到推送消息', [
                        'channel' => $originalChannel,
                        'event' => $event,
                    ]);

                    // 获取订阅了该频道的所有连接，并根据 Worker ID 分发消息
                    // 这样每个 Worker 只处理自己进程中的连接
                    $channelKey = 'websocket:channel:' . $originalChannel;
                    $subscriberFds = $redisQueue->smembers($channelKey);
                    
                    if (empty($subscriberFds)) {
                        \Log::warning('Redis 消息无订阅者', [
                            'channel' => $originalChannel,
                            'event' => $event,
                        ]);
                        return;
                    }

                    // 准备消息
                    $queueMessage = json_encode([
                        'channel' => $originalChannel,
                        'event' => $event,
                        'data' => $payload,
                    ]);

                    // 统计每个 Worker 的订阅者
                    $workerQueues = [];
                    $totalQueued = 0;
                    $missingWorkerIds = [];
                    
                    foreach ($subscriberFds as $fd) {
                        $fd = (int)$fd;
                        $connKey = 'websocket:connection:' . $fd;
                        $connData = $redisQueue->get($connKey);
                        
                        if ($connData) {
                            $connection = json_decode($connData, true);
                            $workerId = $connection['worker_id'] ?? null;
                            
                            if ($workerId !== null) {
                                if (!isset($workerQueues[$workerId])) {
                                    $workerQueues[$workerId] = [];
                                }
                                $workerQueues[$workerId][] = $fd;
                            } else {
                                // worker_id 为 null，记录日志
                                $missingWorkerIds[] = $fd;
                                \Log::warning('连接信息缺少 worker_id', [
                                    'fd' => $fd,
                                    'channel' => $originalChannel,
                                    'connection_data' => $connection,
                                ]);
                            }
                        } else {
                            // 连接信息不存在，从订阅列表中移除
                            $redisQueue->srem($channelKey, $fd);
                            \Log::warning('连接信息不存在，已从订阅列表移除', [
                                'fd' => $fd,
                                'channel' => $originalChannel,
                            ]);
                        }
                    }
                    
                    // 向每个有订阅者的 Worker 的队列中放入消息
                    $queuePrefix = $prefix; // 与 Laravel Redis 前缀保持一致，消费端使用门面会自动带前缀
                    foreach ($workerQueues as $workerId => $fds) {
                        $workerQueueKey = $queuePrefix . 'websocket:push:queue:worker:' . $workerId;
                        $queueLength = $redisQueue->rpush($workerQueueKey, $queueMessage);
                        $totalQueued++;
                        
                        \Log::info('消息已加入 Worker 队列', [
                            'channel' => $originalChannel,
                            'event' => $event,
                            'worker_id' => $workerId,
                            'fd_count' => count($fds),
                            'fd_list' => $fds,
                            'queue_length' => $queueLength,
                        ]);
                    }
                    
                    // 如果没有找到有效的 Worker，或者有缺失 worker_id 的连接，放入全局队列作为备用
                    if ($totalQueued === 0 || !empty($missingWorkerIds)) {
                        $globalQueueKey = $queuePrefix . 'websocket:push:queue';
                        $globalQueueLength = $redisQueue->rpush($globalQueueKey, $queueMessage);
                        \Log::info('消息已加入全局队列（备用）', [
                            'channel' => $originalChannel,
                            'event' => $event,
                            'reason' => $totalQueued === 0 ? '无有效 Worker' : '存在缺失 worker_id 的连接',
                            'missing_worker_id_fds' => $missingWorkerIds,
                            'queue_length' => $globalQueueLength,
                        ]);
                    }

                    \Log::info('Redis 消息已分发到 Worker 队列', [
                        'channel' => $originalChannel,
                        'event' => $event,
                        'worker_count' => $totalQueued,
                        'subscriber_count' => count($subscriberFds),
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Redis 订阅处理错误', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            });
            
            \Log::info('Redis 订阅成功，等待消息...');
        } catch (\Exception $e) {
            \Log::error('Redis 订阅进程异常', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public static function onReload(Server $server, Process $process)
    {
        \Log::info('Redis 订阅进程重载', ['pid' => $process->pid]);
    }
}

