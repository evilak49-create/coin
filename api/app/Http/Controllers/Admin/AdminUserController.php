<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{

    /**
     * 管理员列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'username' => 'nullable|string',
        ]);
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $page = $this->paging($page, $limit);

        $model = AdminUser::query();

        if(!empty($request->username)) {
            $model->where('username', 'like', '%'.$request->username.'%');
        }

        $count = $model->count();
        $list = $model->offset($page)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->select('id', 'username', 'status', 'role_ids', 'ip_last', 'login_time_last', 'created_at')
            ->get()->toArray();

        if(!empty($list)) {
            foreach ($list as $key => $value) {
                $list[$key]['role'] = Role::query()->whereIn('id', $value['role_ids'])->select('id', 'name')->get();
            }
        }

        return $this->success(['total_num'=> $count, 'list'=>$list]);
    }

    /**
     * 获取指定管理员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);
        $info =  AdminUser::query()
            ->where('id', $request->id)
            ->select('id', 'username',  'status', 'ip_last', 'login_time_last', 'created_at')
            ->first();
        return $this->success($info);
    }

    /**
     * 添加管理员
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'username' => 'required|min:2|max:30',
            'status' => 'required|int',
            'role_ids' => 'required|array',
        ]);

        $user = AdminUser::query()->where('username', $request->username)->first();
        if(!empty($user)) {
            return $this->fail(20007);
        }

        AdminUser::query()->create([
            'username' => $request->username,
            'password' => Hash::make(md5('111111')),
            'status' => $request->status,
            'role_ids' => $request->role_ids
        ]);

        return $this->success();
    }

    /**
     * 编辑管理员
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'username' => 'required|min:2|max:30',
            'status' => 'required|int',
            'role_ids' => 'required|array',
        ]);

        $check_user = AdminUser::query()->where('username', $request->username)->where('id', '!=', $request->id)->first();
        if(!empty($check_user)) {
            return $this->fail(20007);
        }

        $user = AdminUser::query()->where('id', $request->id)->firstOrFail();
        $user->username = $request->username;
        $user->status = $request->status;
        $user->role_ids = $request->role_ids;
        $user->save();

        return $this->success();
    }

    /**
     * 重置密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $user = AdminUser::query()->where('id', $request->id)->firstOrFail();
        $user->password = Hash::make(md5('111111'));
        $user->save();
        return $this->success();
    }

    /**
     * 账号禁用/启用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function changeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $user = AdminUser::query()->where('id', $request->id)->firstOrFail();
        $user->status = $user->status == 0 ? 1 : 0;
        $user->save();
        return $this->success();
    }

    /**
     * 删除指定管理员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);
        AdminUser::query()->where('id', $request->id)->delete();
        return $this->success();
    }

    /**
     * 角色列表
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function roleList()
    {
        $list = Role::query()->whereNotIn('slug', ['admin', 'agency'])->select('id', 'name', 'slug')->get();
        return $this->success($list);
    }


}
