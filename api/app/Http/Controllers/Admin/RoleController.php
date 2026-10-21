<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{

    /**
     * 角色列表
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list()
    {
        $list = Role::query()->where('slug', '!=', 'admin')->get();
        return $this->success($list);
    }

    /**
     * 获取指定角色
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);
        $checked =  RolePermission::query()->where('role_id', $request->id)->pluck('permission_id', 'permission_id')->toArray();
//        $checked =  RolePermission::query()->where('role_id', $request->id)->where('status', 0)->pluck('permission_id', 'permission_id')->toArray();
//        $halfChecked =  RolePermission::query()->where('role_id', $request->id)->where('status', 1)->pluck('permission_id', 'permission_id')->toArray();
        $role = Role::query()->where('id', $request->id)->firstOrFail();
        return $this->success([
            'name' => $role->name,
            'slug' => $role->slug,
            'checked' => array_values($checked),
//            'halfChecked' => array_values($halfChecked),
        ]);
    }

    /**
     * 添加角色
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:roles,slug',
            'checked' => 'required|array',
//            'halfChecked' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::query()->create([
                'name' => $request->name,
                'slug' => $request->slug,
            ]);
            foreach ($request->checked as $value) {
                RolePermission::query()->create([
                    'role_id' => $role->id,
                    'permission_id' => $value,
                ]);
            }
//            if(!empty($request->halfChecked)) {
//                foreach ($request->halfChecked as $value) {
//                    RolePermission::query()->create([
//                        'role_id' => $role->id,
//                        'permission_id' => $value,
//                        'status' => 1,
//                    ]);
//                }
//            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('添加角色失败', ['msg'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()]);
            return $this->fail(20001);
        }

        return $this->success();
    }

    /**
     * 编辑角色
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'name' => 'required|string',
            'slug' => 'required|string',
            'checked' => 'required|array',
//            'halfChecked' => 'array',
        ]);

        $is_role = Role::query()->where('id', '!=', $request->id)->where('slug', $request->slug)->first();
        if(!empty($is_role)) {
            return $this->fail(20002, 'slug已存在');
        }
        $role = Role::query()->where('id', $request->id)->first();
        if (empty($role)) {
            return $this->fail(20003);
        }
        if($role->is_admin == Role::ADMIN_YES) {
            return $this->fail(20006);
        }

        DB::beginTransaction();
        try {
            $role->name = $request->name;
            $role->slug = $request->slug;
            $role->save();

            //删除权限
            RolePermission::query()->where('role_id', $role->id)->delete();
            //添加权限
            foreach ($request->checked as $value) {
                RolePermission::query()->create([
                    'role_id' => $role->id,
                    'permission_id' => $value,
                ]);
            }
//            if(!empty($request->halfChecked)) {
//                foreach ($request->halfChecked as $value) {
//                    RolePermission::query()->create([
//                        'role_id' => $role->id,
//                        'permission_id' => $value,
//                        'status' => 1,
//                    ]);
//                }
//            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('编辑角色失败', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return $this->fail(20002);
        }

        return $this->success();
    }

    /**
     * 删除指定角色
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);
        $role = Role::query()->where('id', $request->id)->first();
        if(empty($role)) {
            return $this->fail(20003);
        }
        if($role->is_admin == Role::ADMIN_YES) {
            return $this->fail(20005);
        }

        DB::beginTransaction();
        try {
            Role::query()->where('id', $request->id)->delete();
            RolePermission::query()->where('role_id', $request->id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('删除角色失败', ['msg' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            return $this->fail(20004);
        }
        return $this->success();
    }

}
