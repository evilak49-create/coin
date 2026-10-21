<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperateLog extends BaseModel
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_user_id', 'path', 'methods', 'ip', 'input', 'remark', 'updated_at'
    ];

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function store($admin_user_id, $path, $method, $ip, $input, $remark)
    {
        return self::query()->create([
            'admin_user_id' => $admin_user_id,
            'path' => $path,
            'methods' => $method,
            'ip' => $ip,
            'input' => json_encode($input),
            'remark' => $remark,
        ]);
    }
}
