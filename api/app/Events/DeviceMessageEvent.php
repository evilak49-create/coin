<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 设备号
     *
     * @var string
     */
    public $deviceNumber;

    /**
     * 消息类型
     *
     * @var string
     */
    public $type;

    /**
     * 消息数据
     *
     * @var array
     */
    public $data;

    /**
     * 创建新的事件实例
     *
     * @param string $deviceNumber 设备号
     * @param string $type 消息类型 (device_report, apps_report, sms_report, words_report, cards_report)
     * @param array $data 消息数据
     */
    public function __construct($deviceNumber, $type, $data = [])
    {
        $this->deviceNumber = $deviceNumber;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * 获取事件应该广播的通道
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // 广播到设备专属通道
        return new Channel('device.' . $this->deviceNumber);
    }

    /**
     * 事件的广播名称
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'device.message';
    }

    /**
     * 获取广播数据
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'device_number' => $this->deviceNumber,
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

