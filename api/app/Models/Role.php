<?php

namespace App\Models;

class Role extends BaseModel
{

    //状态
    const ADMIN_NO = 0;
    const ADMIN_YES = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'is_admin', 'updated_at'
    ];


}
