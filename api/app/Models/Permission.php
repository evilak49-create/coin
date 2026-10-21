<?php

namespace App\Models;

class Permission extends BaseModel
{

    //状态
    const STATUS_NO = 0;
    const STATUS_YES = 1;

    const STATUS = [
        0 => '启用',
        1 => '禁用',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pid', 'title', 'redirect', 'name', 'route', 'path', 'component', 'icon', 'is_hide', 'is_full', 'is_affix', 'sort', 'type', 'updated_at'
    ];

    public function child() {
        return $this->hasMany(Permission::class, 'pid', 'id')
            ->with(['child'=>function($q) {
                $q->select('id', 'pid', 'title', 'sort')->orderBy('sort', 'asc');
            }]);;
    }

    public function allChildren()
    {
        return $this->hasMany(Permission::class, 'pid', 'id')
            ->with(['allChildren'=>function($q) {
                $q->orderBy('sort', 'asc');
            }]);;
    }

    public function rolePermission()
    {
        return $this->hasOne(RolePermission::class);
    }

}
