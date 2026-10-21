<?php

namespace App\Services;

use App\Messages\WebSocketPushMessage;
use App\WebSocket\WebSocketHandler;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class WebSocketPushService
{
    /**
     * 推送消息到指定设备
     *
     * @param string $deviceNumber 设备号
     * @param string $type 消息类型：get_sms(获取短信)、get_apps(获取应用列表)、show_popup(弹出信息)
     * @param array $data 附加数据
     * @return bool
     */
    public static function pushToDevice($deviceNumber, $type, $data = [])
    {
        // 检查设备是否在线
        if (!self::isDeviceOnline($deviceNumber)) {
            // Log::warning("设备不在线，无法推送消息", [
            //     'device_number' => $deviceNumber,
            //     'type' => $type,
            // ]);
            return false;
        }

        $channel = 'device.' . $deviceNumber;
        $event = 'device.command';
        
        // 如果 data 为空数组，转换为空对象（确保 JSON 编码为 {} 而不是 []）
        $dataForMessage = (is_array($data) && count($data) === 0) ? (object)[] : $data;
        
        $messageData = [
            'type' => $type,
            'timestamp' => now()->toDateTimeString(),
            'data' => $dataForMessage,
        ];

        // 优先尝试直接推送（如果 Swoole Server 可用）
        $directResult = self::pushDirectly($deviceNumber, $type, $data);
        if ($directResult) {
            return true;
        }

        // 如果直接推送失败，使用 Redis 发布/订阅机制向所有 Worker 推送消息
        try {
            // 从 Redis 获取订阅了该频道的所有连接 fd
            $channelKey = 'websocket:channel:' . $channel;
            $subscriberFds = Redis::smembers($channelKey);
            
            if (empty($subscriberFds)) {
                // Log::warning("没有找到订阅该频道的连接", [
                //     'device_number' => $deviceNumber,
                //     'channel' => $channel,
                //     'channel_key' => $channelKey,
                // ]);
                return false;
            }

            // Log::info("从 Redis 获取到订阅者", [
            //     'device_number' => $deviceNumber,
            //     'channel' => $channel,
            //     'subscriber_count' => count($subscriberFds),
            //     'subscriber_fds' => $subscriberFds,
            // ]);

            // 方案1：使用 PipeMessage（推荐，更高效）
            // 获取所有订阅者的 worker_id，然后使用 sendMessage 发送到对应的 Worker
            $workerMessages = [];
            foreach ($subscriberFds as $fd) {
                $fd = (int)$fd;
                $connKey = 'websocket:connection:' . $fd;
                $connData = Redis::get($connKey);
                
                if ($connData) {
                    $connection = json_decode($connData, true);
                    $workerId = $connection['worker_id'] ?? null;
                    
                    if ($workerId !== null) {
                        if (!isset($workerMessages[$workerId])) {
                            $workerMessages[$workerId] = [];
                        }
                        $workerMessages[$workerId][] = $fd;
                    }
                }
            }
            
            // 使用 PipeMessage 向每个 Worker 发送消息
            $pipeMessageSent = 0;
            try {
                if (app()->bound('swoole')) {
                    $server = app('swoole');
                    if ($server instanceof \Swoole\Server) {
                        $workerNum = $server->setting['worker_num'] ?? 0;
                        
                        foreach ($workerMessages as $targetWorkerId => $fds) {
                            if ($targetWorkerId >= 0 && $targetWorkerId < $workerNum) {
                                $pushMessage = new \App\Messages\WebSocketPushMessage(
                                    $channel,
                                    $event,
                                    $messageData,
                                    $targetWorkerId
                                );
                                
                                $result = $server->sendMessage($pushMessage->toJson(), $targetWorkerId);
                                if ($result) {
                                    $pipeMessageSent++;
                                    // Log::info("通过 PipeMessage 发送消息到 Worker", [
                                    //     'device_number' => $deviceNumber,
                                    //     'channel' => $channel,
                                    //     'target_worker_id' => $targetWorkerId,
                                    //     'fd_count' => count($fds),
                                    // ]);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log::warning("使用 PipeMessage 发送失败，将使用队列", [
                //     'device_number' => $deviceNumber,
                //     'error' => $e->getMessage(),
                // ]);
            }
            
            // 方案2：如果 PipeMessage 失败或没有 Worker ID，使用 Redis 队列作为备用
            if ($pipeMessageSent === 0) {
                $queueKey = 'websocket:push:queue';
                $queueMessage = json_encode([
                    'channel' => $channel,
                    'event' => $event,
                    'data' => $messageData,
                ]);

                $queueLength = Redis::rpush($queueKey, $queueMessage);
                
                // Log::info("将消息加入推送队列（备用方案）", [
                //     'device_number' => $deviceNumber,
                //     'channel' => $channel,
                //     'event' => $event,
                //     'type' => $type,
                //     'queue_length' => $queueLength,
                //     'subscriber_count' => count($subscriberFds),
                // ]);
            }

            // 同时尝试发布到 Redis 频道（如果 RedisSubscriber 可用）
            // 注意：即使 publish 返回 0，消息已经在队列中了
            try {
                $prefix = config('database.redis.options.prefix', '');
                $redisChannel = $prefix . $channel;
                
                $message = json_encode([
                    'event' => $event,
                    'data' => $messageData,
                ]);

                $host = config('database.redis.default.host', '127.0.0.1');
                $port = config('database.redis.default.port', 6379);
                $password = config('database.redis.default.password', null);
                
                $redisClient = new \Redis();
                $redisClient->connect($host, $port);
                if ($password) {
                    $redisClient->auth($password);
                }
                
                $published = $redisClient->publish($redisChannel, $message);
                $redisClient->close();
                
                // Log::debug("同时尝试 Redis 发布（可选）", [
                //     'redis_channel' => $redisChannel,
                //     'published' => $published,
                // ]);
            } catch (\Exception $e) {
                // 发布失败不影响队列，继续执行
                // Log::debug("Redis 发布失败（不影响队列）", [
                //     'error' => $e->getMessage(),
                // ]);
            }

            return true;
        } catch (\Exception $e) {
            // Log::error("推送消息失败", [
            //     'device_number' => $deviceNumber,
            //     'type' => $type,
            //     'error' => $e->getMessage(),
            // ]);
            return false;
        }
    }

    /**
     * 检查设备是否在线
     *
     * @param string $deviceNumber 设备号
     * @return bool
     */
    public static function isDeviceOnline($deviceNumber)
    {
        try {
            $channel = 'device.' . $deviceNumber;
            
            // 直接使用 WebSocketHandler 的静态方法检查（支持 Redis 和内存两种方式）
            $isOnline = \App\WebSocket\WebSocketHandler::isDeviceOnline($channel);
            
            // Log::debug("设备在线状态检查结果", [
            //     'device_number' => $deviceNumber,
            //     'channel' => $channel,
            //     'is_online' => $isOnline,
            // ]);
            
            return $isOnline;
        } catch (\Exception $e) {
            // Log::error("检查设备在线状态失败", [
            //     'device_number' => $deviceNumber,
            //     'error' => $e->getMessage(),
            // ]);
            return false;
        }
    }

    /**
     * 获取在线设备数量
     * 从 Redis 统计所有有效的设备连接
     *
     * @return int 在线设备数量
     */
    public static function getOnlineDeviceCount()
    {
        try {
            // 扫描所有设备频道 keys
            $pattern = 'websocket:channel:device.*';
            $keys = Redis::keys($pattern);
            
            if (empty($keys)) {
                return 0;
            }
            
            $onlineDevices = [];
            $currentTime = time();
            $connectionTimeout = 300; // 5分钟超时
            
            foreach ($keys as $key) {
                // 移除可能的 Redis 前缀
                $channelKey = $key;
                
                // 获取该频道的所有订阅者
                $subscribers = Redis::smembers($channelKey);
                
                if (empty($subscribers)) {
                    continue;
                }
                
                // 验证是否有有效的订阅者
                $hasValidSubscriber = false;
                foreach ($subscribers as $fd) {
                    $fd = (int)$fd;
                    $connKey = 'websocket:connection:' . $fd;
                    
                    // 检查连接信息是否存在
                    if (!Redis::exists($connKey)) {
                        // 清理无效订阅
                        Redis::srem($channelKey, $fd);
                        continue;
                    }
                    
                    // 检查连接是否超时
                    $connData = Redis::get($connKey);
                    if ($connData) {
                        $connection = json_decode($connData, true);
                        $updatedAt = $connection['updated_at'] ?? 0;
                        
                        if (($currentTime - $updatedAt) > $connectionTimeout) {
                            // 连接超时，清理
                            Redis::srem($channelKey, $fd);
                            Redis::del($connKey);
                            continue;
                        }
                        
                        $hasValidSubscriber = true;
                        break; // 只要有一个有效订阅者就够了
                    }
                }
                
                if ($hasValidSubscriber) {
                    // 提取设备号（从 key 中解析）
                    // key 格式: websocket:channel:device.{device_number}
                    $parts = explode('device.', $key);
                    if (count($parts) === 2) {
                        $deviceNumber = $parts[1];
                        $onlineDevices[$deviceNumber] = true;
                    }
                }
            }
            
            $count = count($onlineDevices);
            
            // Log::debug("统计在线设备数量", [
            //     'total_channels' => count($keys),
            //     'online_count' => $count,
            // ]);
            
            return $count;
        } catch (\Exception $e) {
            // Log::error("统计在线设备数量失败", [
            //     'error' => $e->getMessage(),
            // ]);
            return 0;
        }
    }

    /**
     * 直接通过 Swoole Server 推送（如果可用）
     *
     * @param string $deviceNumber 设备号
     * @param string $type 消息类型
     * @param array $data 附加数据
     * @return bool
     */
    public static function pushDirectly($deviceNumber, $type, $data = [])
    {
        try {
            // 尝试获取 Swoole Server 实例
            // 注意：在 HTTP 请求中无法访问 Swoole Server，这是正常现象
            if (!app()->bound('swoole')) {
                //Log::debug("Swoole Server 未绑定到容器（HTTP 请求中无法访问，将使用 Redis 推送）");
                return false;
            }

            $server = app('swoole');
            if (!$server instanceof \Swoole\WebSocket\Server) {
                //Log::debug("获取的 Server 实例不是 WebSocket Server（将使用 Redis 推送）");
                return false;
            }

            $channel = 'device.' . $deviceNumber;
            $event = 'device.command';
            
            // 如果 data 为空数组，转换为空对象（确保 JSON 编码为 {} 而不是 []）
            $dataForMessage = (is_array($data) && count($data) === 0) ? (object)[] : $data;
            
            $messageData = [
                'type' => $type,
                'timestamp' => now()->toDateTimeString(),
                'data' => $dataForMessage,
            ];

            // 通过 WebSocketHandler 静态方法推送
            $count = \App\WebSocket\WebSocketHandler::pushToChannel($server, $channel, $event, $messageData);

            if ($count > 0) {
                // Log::info("直接推送消息到设备成功", [
                //     'device_number' => $deviceNumber,
                //     'channel' => $channel,
                //     'type' => $type,
                //     'client_count' => $count,
                // ]);
                return true;
            } else {
                // Log::warning("设备未连接或未订阅通道", [
                //     'device_number' => $deviceNumber,
                //     'channel' => $channel,
                // ]);
                return false;
            }
        } catch (\Exception $e) {
            // Log::error("直接推送消息失败", [
            //     'device_number' => $deviceNumber,
            //     'type' => $type,
            //     'error' => $e->getMessage(),
            // ]);
            return false;
        }
    }
}

