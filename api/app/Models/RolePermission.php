<?php

namespace App\Models;

class RolePermission extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'permission_id', 'status', 'updated_at'
    ];
}
