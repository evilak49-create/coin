<?php

namespace App\Messages;

/**
 * WebSocket 推送消息（用于 PipeMessage 通信）
 */
class WebSocketPushMessage
{
    public $channel;
    public $event;
    public $data;
    public $targetWorkerId;

    public function __construct($channel, $event, $data, $targetWorkerId = null)
    {
        $this->channel = $channel;
        $this->event = $event;
        $this->data = $data;
        $this->targetWorkerId = $targetWorkerId;
    }

    /**
     * 将消息序列化为 JSON 字符串（用于 sendMessage）
     */
    public function toJson()
    {
        return json_encode([
            'type' => 'websocket_push',
            'channel' => $this->channel,
            'event' => $this->event,
            'data' => $this->data,
            'target_worker_id' => $this->targetWorkerId,
        ]);
    }

    /**
     * 从 JSON 字符串反序列化消息
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);
        if (!$data || $data['type'] !== 'websocket_push') {
            return null;
        }

        return new self(
            $data['channel'] ?? '',
            $data['event'] ?? '',
            $data['data'] ?? [],
            $data['target_worker_id'] ?? null
        );
    }
}

