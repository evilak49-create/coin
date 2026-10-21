<?php

namespace App\Tasks;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use App\WebSocket\WebSocketHandler;

class WebSocketPushTask extends Task
{
    protected $channel;
    protected $event;
    protected $data;

    public function __construct($channel, $event, $data)
    {
        $this->channel = $channel;
        $this->event = $event;
        $this->data = $data;
    }

    public function handle()
    {
        try {
            // 获取 Swoole Server 实例
            $server = app('swoole');
            if (!$server instanceof \Swoole\WebSocket\Server) {
                \Log::warning("Task: Server 不是 WebSocket Server");
                return;
            }

            // 通过 WebSocketHandler 推送消息
            $count = WebSocketHandler::pushToChannel(
                $server,
                $this->channel,
                $this->event,
                $this->data
            );

            \Log::info("Task 推送消息完成", [
                'channel' => $this->channel,
                'event' => $this->event,
                'client_count' => $count,
            ]);
        } catch (\Exception $e) {
            \Log::error("Task 推送消息失败", [
                'channel' => $this->channel,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

