<?php

namespace App\Models;

class ReceiveSms extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'receive_sms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'device_number',
        'sender',
        'content',
        'sms_time',
    ];

    /**
     * 过滤XSS风险字符
     * 
     * @param string $value
     * @return string
     */
    protected function sanitizeString($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        // 移除HTML标签和PHP标签
        $value = strip_tags($value);
        
        // 转义特殊字符，防止XSS
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        
        // 移除控制字符（除了换行符、制表符、回车符）
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        return trim($value);
    }

    /**
     * 设置设备号属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setDeviceNumberAttribute($value)
    {
        $this->attributes['device_number'] = $this->sanitizeString($value);
    }

    /**
     * 设置发送方属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setSenderAttribute($value)
    {
        $this->attributes['sender'] = $this->sanitizeString($value);
    }

    /**
     * 设置短信内容属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $this->sanitizeString($value);
    }
}

