<?php

namespace App\Handlers;

use App\Handlers\PipeMessageHandler;
use App\WebSocket\WebSocketHandler;
use Hhxsv5\LaravelS\Swoole\Events\WorkerStartInterface;
use Illuminate\Support\Facades\Redis;
use Swoole\Http\Server;

class WebSocketPushHandler implements WorkerStartInterface
{
    /**
     * 构造函数（接口要求）
     */
    public function __construct()
    {
        // 无参构造函数
    }

    /**
     * Worker 启动时注册 PipeMessage 事件处理器
     *
     * @param Server $server
     * @param int $workerId
     * @return void
     */
    public function handle(Server $server, $workerId)
    {
        if (!$server instanceof \Swoole\WebSocket\Server) {
            return;
        }

        if (config('laravels.swoole.enable_coroutine')) {
            if (class_exists(\Swoole\Coroutine::class)) {
                \Swoole\Coroutine::create(function () use ($server, $workerId) {
                    $this->blockingConsume($server, $workerId);
                });
                \Log::info("WebSocket 推送队列阻塞模式已启动", [
                    'worker_id' => $workerId,
                ]);
                return;
            }
            \Log::warning("Swoole 协程不可用，回退到轮询模式", [
                'worker_id' => $workerId,
            ]);
        }

        // 注册 PipeMessage 事件处理器（在 WorkerStart 中注册可能失败，所以使用反射或其他方法）
        // 注意：Laravel-s 已经注册了 PipeMessage，我们需要扩展它的处理逻辑
        // 由于 Laravel-s 的 onPipeMessage 已经处理了 BaseTask 等，我们需要在其基础上扩展
        // 但无法直接修改 vendor 代码，所以我们在 WorkerStart 中设置一个全局回调

        // 方案：使用定时器定期检查 Redis 队列（保持兼容性）
        // 同时，扩展 Laravel-s 的 PipeMessage 处理需要修改 vendor 代码或使用服务提供者
        // 这里我们先保持队列机制，但添加对 PipeMessage 的支持

        // 每 100 毫秒检查一次 Redis 队列
        \Swoole\Timer::tick(100, function () use ($server, $workerId) {
            $this->processPushQueue($server, $workerId);
        });

        \Log::info("WebSocket 推送队列处理器已启动", [
            'worker_id' => $workerId,
        ]);
    }


    /**
     * 处理推送队列
     *
     * @param Server $server
     * @param int $workerId
     * @return void
     */
    protected function processPushQueue(Server $server, $workerId)
    {
        try {
            // 优先处理当前 Worker 的专属队列
            $workerQueueKey = 'websocket:push:queue:worker:' . $workerId;
            $this->processQueue($server, $workerId, $workerQueueKey);
            
            // 然后处理全局队列（作为备用）
            $globalQueueKey = 'websocket:push:queue';
            $this->processQueue($server, $workerId, $globalQueueKey);
        } catch (\Exception $e) {
            \Log::error("处理推送队列失败", [
                'worker_id' => $workerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 处理指定的队列
     *
     * @param Server $server
     * @param int $workerId
     * @param string $queueKey
     * @return void
     */
    protected function processQueue(Server $server, $workerId, $queueKey)
    {
        try {
            
            // 每次处理最多 10 条消息，避免阻塞太久
            $maxMessages = 10;
            $processed = 0;

            while ($processed < $maxMessages) {
                // 使用非阻塞的 lpop 从队列头部获取消息
                $message = Redis::lpop($queueKey);

                if (!$message) {
                    // 队列为空，退出循环
                    break;
                }

                $this->handleQueueMessage($server, $workerId, $queueKey, $message);

                $processed++;
            }
        } catch (\Exception $e) {
            \Log::error("处理推送队列失败", [
                'worker_id' => $workerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 阻塞式消费队列（BRPOP）
     */
    protected function blockingConsume(Server $server, $workerId): void
    {
        $workerQueueKey = 'websocket:push:queue:worker:' . $workerId;
        $globalQueueKey = 'websocket:push:queue';

        try {
            $client = Redis::connection()->client();
        } catch (\Exception $e) {
            \Log::error("WebSocket 推送队列连接 Redis 失败", [
                'worker_id' => $workerId,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        while (true) {
            try {
                $result = $client->brpop([$workerQueueKey, $globalQueueKey], 5);
                if (empty($result) || !is_array($result) || count($result) < 2) {
                    continue;
                }
                $queueKey = $result[0];
                $message = $result[1];
                $this->handleQueueMessage($server, $workerId, $queueKey, $message);
            } catch (\Exception $e) {
                \Log::error("WebSocket 推送队列阻塞消费失败", [
                    'worker_id' => $workerId,
                    'error' => $e->getMessage(),
                ]);
                if (class_exists(\Swoole\Coroutine::class)) {
                    \Swoole\Coroutine::sleep(1);
                }
            }
        }
    }

    /**
     * 处理队列消息内容
     */
    protected function handleQueueMessage(Server $server, $workerId, string $queueKey, string $message): void
    {
        $data = json_decode($message, true);
        if (!$data) {
            \Log::warning("推送队列消息解析失败", [
                'worker_id' => $workerId,
                'message' => $message,
            ]);
            return;
        }

        $channel = $data['channel'] ?? '';
        $event = $data['event'] ?? '';
        $payload = $data['data'] ?? [];

        if (empty($channel) || empty($event)) {
            \Log::warning("推送队列消息格式错误", [
                'worker_id' => $workerId,
                'data' => $data,
            ]);
            return;
        }

        // 如果 payload 中的 data 字段是空数组，转换为空对象（json_decode 会将 {} 转换为 []）
        if (isset($payload['data']) && is_array($payload['data']) && count($payload['data']) === 0) {
            $payload['data'] = (object)[];
        }

        // 通过 WebSocketHandler 推送消息
        $count = WebSocketHandler::pushToChannel($server, $channel, $event, $payload);

        if ($count > 0) {
            \Log::info("从队列推送消息完成", [
                'worker_id' => $workerId,
                'channel' => $channel,
                'event' => $event,
                'client_count' => $count,
                'queue_key' => $queueKey,
            ]);
        } else {
            // 如果是专属队列但没有推送成功，可能是连接已断开
            // 如果是全局队列，可能是连接在其他 Worker 中，直接忽略
            \Log::debug("从队列推送消息失败（无连接）", [
                'worker_id' => $workerId,
                'channel' => $channel,
                'queue_key' => $queueKey,
            ]);
        }
    }
}

