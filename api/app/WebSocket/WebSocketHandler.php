<?php

namespace App\WebSocket;

use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Redis;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketHandler implements WebSocketHandlerInterface
{
    protected static $connections = [];
    
    /**
     * 移除指定连接
     *
     * @param int $fd
     * @return void
     */
    public static function removeConnection($fd)
    {
        if (isset(self::$connections[$fd])) {
            // 从 Redis 中移除
            try {
                $key = 'websocket:connection:' . $fd;
                $data = Redis::get($key);
                
                if ($data) {
                    $connection = json_decode($data, true);
                    foreach ($connection['channels'] ?? [] as $channel) {
                        $channelKey = 'websocket:channel:' . $channel;
                        Redis::srem($channelKey, $fd);
                    }
                }
                
                Redis::del($key);
            } catch (\Exception $e) {
                // \Log::warning("从 Redis 移除连接失败", [
                //     'fd' => $fd,
                //     'error' => $e->getMessage(),
                // ]);
            }
            
            unset(self::$connections[$fd]);
        }
    }

    /**
     * 构造函数（必须无参，符合 WebSocketHandlerInterface 接口要求）
     */
    public function __construct()
    {
        // 无参构造函数
    }

    /**
     * WebSocket 连接建立时
     */
    public function onOpen(Server $server, Request $request)
    {
        $fd = $request->fd;
        self::$connections[$fd] = [
            'fd' => $fd,
            'channels' => [],
        ];
        
        // 同步到 Redis（用于跨进程访问）
        $this->syncConnectionToRedis($server, $fd, []);
        
        // \Log::info("WebSocket 连接建立: FD={$fd}", [
        //     'worker_id' => $server->worker_id ?? 'unknown',
        // ]);
    }

    /**
     * 接收 WebSocket 消息
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $fd = $frame->fd;
        $rawData = $frame->data;
        $data = json_decode($rawData, true);

        // 记录收到的原始消息
        // \Log::debug("收到 WebSocket 消息", [
        //     'fd' => $fd,
        //     'raw_data' => $rawData,
        //     'parsed_data' => $data,
        //     'json_error' => json_last_error_msg(),
        // ]);

        if (!$data) {
            // \Log::warning("WebSocket 消息解析失败", [
            //     'fd' => $fd,
            //     'raw_data' => $rawData,
            //     'json_error' => json_last_error_msg(),
            // ]);
            return;
        }

        $event = $data['event'] ?? '';
        // \Log::debug("处理 WebSocket 事件", [
        //     'fd' => $fd,
        //     'event' => $event,
        //     'data' => $data,
        // ]);

        // 处理 Pusher 协议的消息
        switch ($event) {
            case 'pusher:subscribe':
                $channel = $data['data']['channel'] ?? '';
                // \Log::info("收到订阅请求", [
                //     'fd' => $fd,
                //     'channel' => $channel,
                //     'data' => $data,
                // ]);
                $this->handleSubscribe($server, $fd, $channel);
                break;
            case 'pusher:unsubscribe':
                $this->handleUnsubscribe($server, $fd, $data['data']['channel'] ?? '');
                break;
            case 'pusher:ping':
                // 客户端发送心跳，服务端响应
                $response = json_encode(['event' => 'pusher:pong', 'data' => []]);
                $result = $server->push($fd, $response);
                
                // 更新连接的 updated_at（保持在线状态）
                $this->updateConnectionHeartbeat($fd);
                
                // \Log::debug("响应客户端心跳", [
                //     'fd' => $fd,
                //     'push_result' => $result,
                // ]);
                break;
            default:
                // \Log::debug("未处理的事件类型", [
                //     'fd' => $fd,
                //     'event' => $event,
                // ]);
                break;
        }
    }

    /**
     * WebSocket 连接关闭时
     */
    public function onClose(Server $server, $fd, $reactorId)
    {
        // \Log::info("WebSocket 连接关闭: FD={$fd}", [
        //     'fd' => $fd,
        //     'connection_exists' => isset(self::$connections[$fd]),
        //     'channels' => self::$connections[$fd]['channels'] ?? [],
        // ]);
        
        // 从 Redis 中移除连接信息（无论连接是否在内存中）
        $this->removeConnectionFromRedis($fd);
        
        // 从内存中移除连接
        if (isset(self::$connections[$fd])) {
            unset(self::$connections[$fd]);
        }
    }

    /**
     * 处理订阅通道
     */
    protected function handleSubscribe(Server $server, $fd, $channel)
    {
        // \Log::info("开始处理订阅请求", [
        //     'fd' => $fd,
        //     'channel' => $channel,
        //     'channel_empty' => empty($channel),
        //     'connection_exists' => isset(self::$connections[$fd]),
        //     'all_connections' => array_keys(self::$connections),
        // ]);

        if (empty($channel)) {
            // \Log::warning("订阅失败: 通道名为空", [
            //     'fd' => $fd,
            //     'channel' => $channel,
            // ]);
            return;
        }

        // 如果连接不存在，自动创建连接记录（多进程环境下，连接可能在不同进程中）
        if (!isset(self::$connections[$fd])) {
            // \Log::info("连接不存在，自动创建连接记录（多进程环境）", [
            //     'fd' => $fd,
            //     'channel' => $channel,
            // ]);
            self::$connections[$fd] = [
                'fd' => $fd,
                'channels' => [],
            ];
        }

        // 添加到订阅列表
        if (!in_array($channel, self::$connections[$fd]['channels'])) {
            self::$connections[$fd]['channels'][] = $channel;
        }
        
        // 同步到 Redis（用于跨进程访问）
        $this->syncConnectionToRedis($server, $fd, self::$connections[$fd]['channels']);
        
        // 发送订阅成功消息
        $response = json_encode([
            'event' => 'pusher_internal:subscription_succeeded',
            'channel' => $channel,
        ]);
        
        $pushResult = $server->push($fd, $response);
        
        // \Log::info("客户端 FD={$fd} 订阅通道成功: {$channel}", [
        //     'fd' => $fd,
        //     'channel' => $channel,
        //     'push_result' => $pushResult,
        //     'response' => $response,
        //     'all_channels' => self::$connections[$fd]['channels'] ?? [],
        //     'total_connections' => count(self::$connections),
        // ]);
    }

    /**
     * 处理取消订阅
     */
    protected function handleUnsubscribe(Server $server, $fd, $channel)
    {
        if (!isset(self::$connections[$fd])) {
            return;
        }

        $key = array_search($channel, self::$connections[$fd]['channels']);
        if ($key !== false) {
            unset(self::$connections[$fd]['channels'][$key]);
            self::$connections[$fd]['channels'] = array_values(self::$connections[$fd]['channels']);
            
            // 同步到 Redis（需要 Server 参数来获取 worker_id）
            // 注意：在 unsubscribe 时可能没有 Server 参数，这里使用容器获取
            $server = app()->bound('swoole') ? app('swoole') : null;
            if ($server instanceof \Swoole\Server) {
                $this->syncConnectionToRedis($server, $fd, self::$connections[$fd]['channels']);
            } else {
                // 如果无法获取 Server，使用旧方法
                $this->syncConnectionToRedis($fd, self::$connections[$fd]['channels']);
            }
        }

        \Log::info("客户端 FD={$fd} 取消订阅通道: {$channel}");
    }

    /**
     * 获取 Redis 频道名称
     */
    protected function getRedisChannel($channel)
    {
        $prefix = config('database.redis.options.prefix', '');
        return $prefix . $channel;
    }

    /**
     * 推送消息到客户端（由 Redis 订阅触发）
     */
    public function pushToClient(Server $server, $channel, $event, $data)
    {
        foreach (self::$connections as $connection) {
            if (in_array($channel, $connection['channels'])) {
                $message = json_encode([
                    'event' => $event,
                    'channel' => $channel,
                    'data' => $data,
                ]);
                $server->push($connection['fd'], $message);
            }
        }
    }

    /**
     * 静态方法：获取所有连接
     *
     * @return array
     */
    public static function getConnections()
    {
        return self::$connections;
    }

    /**
     * 同步连接信息到 Redis（用于跨进程访问）
     *
     * @param int $fd 文件描述符
     * @param array $channels 订阅的通道列表
     * @return void
     */
    /**
     * 同步连接信息到 Redis
     *
     * @param Server|null $server Swoole Server 实例（用于获取 worker_id）
     * @param int $fd 文件描述符
     * @param array $channels 订阅的通道列表
     * @return void
     */
    protected function syncConnectionToRedis($server, $fd, $channels)
    {
        // 如果第一个参数是整数，说明是旧的方法签名（向后兼容）
        if (is_int($server)) {
            $channels = $fd;
            $fd = $server;
            $server = null;
        }
        
        try {
            $key = 'websocket:connection:' . $fd;
            
            // 获取当前 Worker ID
            $workerId = null;
            if ($server instanceof \Swoole\Server) {
                $workerId = $server->worker_id ?? null;
            }
            
            // 如果无法从 Server 获取，尝试从容器获取
            if ($workerId === null && app()->bound('swoole')) {
                $swooleServer = app('swoole');
                if ($swooleServer instanceof \Swoole\Server) {
                    $workerId = $swooleServer->worker_id ?? null;
                }
            }
            
            $data = [
                'fd' => $fd,
                'channels' => $channels,
                'worker_id' => $workerId,
                'updated_at' => time(),
            ];
            
            Redis::setex($key, 3600, json_encode($data)); // 1小时过期
            
            // 为每个通道维护一个订阅者列表
            foreach ($channels as $channel) {
                $channelKey = 'websocket:channel:' . $channel;
                Redis::sadd($channelKey, $fd);
                Redis::expire($channelKey, 3600);
            }
            
            // \Log::debug("同步连接信息到 Redis", [
            //     'fd' => $fd,
            //     'worker_id' => $workerId,
            //     'channels' => $channels,
            //     'has_server' => $server !== null,
            // ]);
        } catch (\Exception $e) {
            // \Log::warning("同步连接信息到 Redis 失败", [
            //     'fd' => $fd,
            //     'error' => $e->getMessage(),
            // ]);
        }
    }

    /**
     * 从 Redis 中移除连接信息
     *
     * @param int $fd 文件描述符
     * @return void
     */
    protected function removeConnectionFromRedis($fd)
    {
        try {
            $key = 'websocket:connection:' . $fd;
            $data = Redis::get($key);
            
            $channels = [];
            if ($data) {
                $connection = json_decode($data, true);
                $channels = $connection['channels'] ?? [];
            } else {
                // 如果 Redis 中没有数据，尝试从内存中获取
                if (isset(self::$connections[$fd])) {
                    $channels = self::$connections[$fd]['channels'] ?? [];
                }
            }
            
            // 从所有通道的订阅者列表中移除
            foreach ($channels as $channel) {
                $channelKey = 'websocket:channel:' . $channel;
                $removed = Redis::srem($channelKey, $fd);
                // \Log::debug("从通道移除连接", [
                //     'fd' => $fd,
                //     'channel' => $channel,
                //     'removed' => $removed,
                // ]);
            }
            
            // 删除连接信息
            Redis::del($key);
            
            // \Log::info("从 Redis 移除连接信息完成", [
            //     'fd' => $fd,
            //     'channels' => $channels,
            // ]);
        } catch (\Exception $e) {
            // \Log::error("从 Redis 移除连接信息失败", [
            //     'fd' => $fd,
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
        }
    }

    /**
     * 更新连接的心跳时间（仅更新 updated_at）
     * 用于客户端发送心跳时保持在线状态
     *
     * @param int $fd 文件描述符
     * @return void
     */
    protected function updateConnectionHeartbeat($fd)
    {
        try {
            $key = 'websocket:connection:' . $fd;
            $data = Redis::get($key);
            
            if ($data) {
                $connection = json_decode($data, true);
                $connection['updated_at'] = time();
                
                Redis::setex($key, 3600, json_encode($connection));
                
                // \Log::debug("更新连接心跳时间", [
                //     'fd' => $fd,
                //     'updated_at' => $connection['updated_at'],
                // ]);
            }
        } catch (\Exception $e) {
            // \Log::warning("更新连接心跳时间失败", [
            //     'fd' => $fd,
            //     'error' => $e->getMessage(),
            // ]);
        }
    }

    /**
     * 静态方法：检查设备是否在线（是否有客户端订阅了该设备的通道）
     * 优先从 Redis 检查（支持跨进程），如果 Redis 不可用则从内存检查
     * 
     * 会验证连接信息的有效性（检查更新时间，超时的连接会被视为无效）
     *
     * @param string $channel 通道名称
     * @return bool
     */
    public static function isDeviceOnline($channel)
    {
        // 优先从 Redis 检查（支持跨进程访问）
        try {
            $channelKey = 'websocket:channel:' . $channel;
            $subscribers = Redis::smembers($channelKey);
            
            if (!empty($subscribers)) {
                $currentTime = time();
                $connectionTimeout = 120; // 5分钟超时（连接信息超过5分钟未更新视为无效）
                
                // 过滤掉无效的连接（检查连接信息是否存在且未超时）
                $validSubscribers = [];
                foreach ($subscribers as $fd) {
                    $fd = (int)$fd;
                    $connKey = 'websocket:connection:' . $fd;
                    
                    // 检查连接信息是否存在
                    if (!Redis::exists($connKey)) {
                        // 连接信息不存在，从订阅列表中移除
                        Redis::srem($channelKey, $fd);
                        // \Log::debug("清理无效订阅者（连接信息不存在）", [
                        //     'channel' => $channel,
                        //     'fd' => $fd,
                        // ]);
                        continue;
                    }
                    
                    // 检查连接信息是否超时
                    $connData = Redis::get($connKey);
                    if ($connData) {
                        $connection = json_decode($connData, true);
                        $updatedAt = $connection['updated_at'] ?? 0;
                        $channels = $connection['channels'] ?? [];
                        
                        // 检查连接是否订阅了该频道
                        if (!in_array($channel, $channels)) {
                            // 连接未订阅该频道，从订阅列表中移除
                            Redis::srem($channelKey, $fd);
                            // \Log::debug("清理无效订阅者（未订阅该频道）", [
                            //     'channel' => $channel,
                            //     'fd' => $fd,
                            //     'subscribed_channels' => $channels,
                            // ]);
                            continue;
                        }
                        
                        // 检查连接信息是否超时
                        if (($currentTime - $updatedAt) > $connectionTimeout) {
                            // 连接信息超时，视为无效连接
                            Redis::srem($channelKey, $fd);
                            Redis::del($connKey);
                            // \Log::debug("清理超时订阅者", [
                            //     'channel' => $channel,
                            //     'fd' => $fd,
                            //     'last_updated' => $updatedAt,
                            //     'timeout_seconds' => $connectionTimeout,
                            // ]);
                            continue;
                        }
                        
                        // 连接有效
                        $validSubscribers[] = $fd;
                    }
                }
                
                if (!empty($validSubscribers)) {
                    // \Log::debug("设备在线检查通过", [
                    //     'channel' => $channel,
                    //     'valid_subscriber_count' => count($validSubscribers),
                    //     'valid_subscribers' => $validSubscribers,
                    // ]);
                    return true;
                } else {
                    // \Log::debug("设备在线检查失败（无有效订阅者）", [
                    //     'channel' => $channel,
                    //     'total_subscribers' => count($subscribers),
                    // ]);
                }
            } else {
                // \Log::debug("设备在线检查失败（无订阅者）", [
                //     'channel' => $channel,
                // ]);
            }
        } catch (\Exception $e) {
            // \Log::debug("从 Redis 检查设备在线状态失败，使用内存检查", [
            //     'channel' => $channel,
            //     'error' => $e->getMessage(),
            // ]);
        }
        
        // 如果 Redis 不可用，从内存检查（仅限当前进程）
        foreach (self::$connections as $connection) {
            if (in_array($channel, $connection['channels'])) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 静态方法：推送消息到指定通道的所有客户端
     *
     * @param Server $server Swoole WebSocket Server 实例
     * @param string $channel 通道名称
     * @param string $event 事件名称
     * @param array $data 消息数据
     * @return int 推送成功的客户端数量
     */
    public static function pushToChannel(Server $server, $channel, $event, $data)
    {
        $count = 0;
        $message = json_encode([
            'event' => $event,
            'channel' => $channel,
            'data' => $data,
        ]);

        $currentWorkerId = $server->worker_id ?? null;

        // 优先从 Redis 获取订阅了该频道的所有连接 fd（支持跨进程）
        try {
            $channelKey = 'websocket:channel:' . $channel;
            $subscriberFds = Redis::smembers($channelKey);
            
            if (!empty($subscriberFds)) {
                // \Log::debug("从 Redis 获取订阅者", [
                //     'channel' => $channel,
                //     'subscriber_fds' => $subscriberFds,
                //     'current_worker_connections' => array_keys(self::$connections),
                //     'current_worker_id' => $currentWorkerId,
                // ]);

                foreach ($subscriberFds as $fd) {
                    $fd = (int)$fd;
                    
                    // 检查连接是否在当前 Worker 进程中
                    if (isset(self::$connections[$fd])) {
                        // 验证连接确实订阅了该频道
                        if (in_array($channel, self::$connections[$fd]['channels'])) {
                            try {
                                if ($server->push($fd, $message)) {
                                    $count++;
                                    \Log::debug("推送消息成功", [
                                        'fd' => $fd,
                                        'channel' => $channel,
                                        'event' => $event,
                                        'worker_id' => $currentWorkerId,
                                    ]);
                                } else {
                                    // 推送失败，连接可能已断开，从 Redis 中移除
                                    // \Log::warning("推送消息失败，连接可能已断开", [
                                    //     'fd' => $fd,
                                    //     'channel' => $channel,
                                    // ]);
                                    Redis::srem($channelKey, $fd);
                                }
                            } catch (\Exception $e) {
                                // \Log::warning("推送消息异常", [
                                //     'fd' => $fd,
                                //     'channel' => $channel,
                                //     'error' => $e->getMessage(),
                                // ]);
                                Redis::srem($channelKey, $fd);
                            }
                        }
                    } else {
                        // fd 不在当前 Worker 进程中，这是正常的（多进程环境）
                        // 其他 Worker 进程会处理这个连接（通过 Redis 订阅机制）

                        
                    }
                }
            }
        } catch (\Exception $e) {
            // \Log::warning("从 Redis 获取订阅者失败，使用内存检查", [
            //     'channel' => $channel,
            //     'error' => $e->getMessage(),
            // ]);
        }

        // 如果 Redis 不可用，回退到内存检查（仅限当前进程）
        if ($count === 0) {
            foreach (self::$connections as $connection) {
                if (in_array($channel, $connection['channels'])) {
                    try {
                        if ($server->push($connection['fd'], $message)) {
                            $count++;
                        }
                    } catch (\Exception $e) {
                        // \Log::warning("推送消息异常（内存检查）", [
                        //     'fd' => $connection['fd'],
                        //     'channel' => $channel,
                        //     'error' => $e->getMessage(),
                        // ]);
                    }
                }
            }
        }

        return $count;
    }
}

