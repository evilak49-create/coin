<?php

namespace App\Models;

class ActionRecord extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'action_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_number',
        'app_package',
        'action',
        'user_action',
        'operate_date',
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
     * 设置应用包名属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setAppPackageAttribute($value)
    {
        $this->attributes['app_package'] = $this->sanitizeString($value);
    }

    /**
     * 设置动作属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setActionAttribute($value)
    {
        $this->attributes['action'] = $this->sanitizeString($value);
    }

    /**
     * 设置用户动作属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setUserActionAttribute($value)
    {
        $this->attributes['user_action'] = $this->sanitizeString($value);
    }
}

