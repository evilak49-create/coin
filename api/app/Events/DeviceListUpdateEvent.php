<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceListUpdateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 设备信息
     *
     * @var array
     */
    public $device;

    /**
     * 操作类型 (created, updated, deleted)
     *
     * @var string
     */
    public $action;

    /**
     * 创建新的事件实例
     *
     * @param array $device 设备信息
     * @param string $action 操作类型
     */
    public function __construct($device, $action = 'updated')
    {
        $this->device = $device;
        $this->action = $action;
    }

    /**
     * 获取事件应该广播的通道
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // 广播到设备列表更新通道（管理员使用）
        return [
            new Channel('device.list'),
            new Channel('device.' . ($this->device['device_number'] ?? '')),
        ];
    }

    /**
     * 事件的广播名称
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'device.list.update';
    }

    /**
     * 获取广播数据
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'device' => $this->device,
            'action' => $this->action,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

