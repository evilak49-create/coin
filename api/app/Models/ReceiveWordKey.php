<?php

namespace App\Models;

class ReceiveWordKey extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'receive_word_keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'receive_word_id',
        'currency_type',
        'address',
        'private_key',
        'balance',
        'total_sent',
        'balance_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'balance' => 'decimal:8',
        'total_sent' => 'decimal:8',
        'balance_updated_at' => 'datetime',
    ];

    const BASE_CURRENCY_TYPES = ['BTC', 'ETH', 'BSC', 'TRON', 'SOL', 'TON', 'XRP','ERC20USDT', 'BEP20USDT', 'TRC20USDT'];


    
    /**
     * 关联的助记词记录
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiveWord()
    {
        return $this->belongsTo(ReceiveWords::class, 'receive_word_id');
    }



}

