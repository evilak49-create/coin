<?php

namespace App\Handlers;

use App\Messages\WebSocketPushMessage;
use App\WebSocket\WebSocketHandler;
use Swoole\Http\Server;

/**
 * PipeMessage 事件处理器
 * 用于处理跨 Worker 进程的 WebSocket 推送消息
 */
class PipeMessageHandler
{
    /**
     * 处理 PipeMessage 事件
     *
     * @param Server $server
     * @param int $fromWorkerId
     * @param mixed $message
     * @return void
     */
    public static function handle(Server $server, $fromWorkerId, $message)
    {
        try {
            // 尝试解析为 WebSocket 推送消息
            if (is_string($message)) {
                $pushMessage = WebSocketPushMessage::fromJson($message);
                if ($pushMessage && $pushMessage->channel && $pushMessage->event) {
                    // 这是我们的 WebSocket 推送消息
                    if (!$server instanceof \Swoole\WebSocket\Server) {
                        return;
                    }

                    $currentWorkerId = $server->worker_id ?? null;
                    
                    // 检查消息是否是发送给当前 Worker 的
                    if ($pushMessage->targetWorkerId !== null && $pushMessage->targetWorkerId !== $currentWorkerId) {
                        // 消息不是发送给当前 Worker 的，忽略
                        \Log::debug("PipeMessage 消息目标 Worker 不匹配", [
                            'current_worker_id' => $currentWorkerId,
                            'target_worker_id' => $pushMessage->targetWorkerId,
                            'from_worker_id' => $fromWorkerId,
                        ]);
                        return;
                    }

                    // 推送消息到当前 Worker 进程中的连接
                    $count = WebSocketHandler::pushToChannel(
                        $server,
                        $pushMessage->channel,
                        $pushMessage->event,
                        $pushMessage->data
                    );

                    \Log::info("通过 PipeMessage 推送消息完成", [
                        'current_worker_id' => $currentWorkerId,
                        'from_worker_id' => $fromWorkerId,
                        'target_worker_id' => $pushMessage->targetWorkerId,
                        'channel' => $pushMessage->channel,
                        'event' => $pushMessage->event,
                        'client_count' => $count,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error("处理 PipeMessage 失败", [
                'from_worker_id' => $fromWorkerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

