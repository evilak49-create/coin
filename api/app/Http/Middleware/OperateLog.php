<?php

namespace App\Http\Middleware;

use Closure;

class OperateLog
{
    const REMARK = [
        '/api/web/auth/sendSms' => '短信验证码',
        '/api/web/home' => '访问首页',
        '/api/admin/home/fileTemplate' => '首页附件模板',
        '/api/admin/home/importTemplate' => '导入模板下载',
        '/api/web/auth/updatePassword' => '修改密码',
        '/api/web/auth/updatePhone' => '修改绑定手机号',
        '/api/web/auth/loginOut' => '退出登录',
        '/api/web/auth/menu' => '获取用户权限',
        '/api/web/info' => '获取账号信息',
        '/api/web/proxyInfo' => '获取代理机构信息',
        '/api/web/auth/update' => '修改账号',
        '/api/web/operateLog/list' => '访问日志记录',
        '/api/web/upload' => '文件上传',
        '/api/web/upload/url' => '链接上传',

        '/api/admin/auth/menu' => '用户权限列表',
        '/api/admin/info' => '获取账号信息',
        '/api/admin/auth/update' => '修改账号',
        //日志记录列表
        '/api/admin/operateLog/list' => '日志记录列表',

        //权限管理
        '/api/web/permission/list' => '权限列表',
        '/api/web/permission/info' => '查看指定权限',
//        '/api/web/permission/select' => '获取权限下拉列表',
        '/api/web/permission/add' => '新增权限',
        '/api/web/permission/update' => '修改权限',
        '/api/web/permission/del' => '删除权限',

        //角色管理
        '/api/web/role/list' => '角色列表',
        '/api/web/role/info' => '获取指定角色',
        '/api/web/role/add' => '新增角色',
        '/api/web/role/update' => '编辑角色',
        '/api/web/role/del' => '删除角色',

        //管理员列表
        '/api/web/user/list' => '用户列表',
        '/api/web/user/info' => '获取指定用户信息',
        '/api/web/user/add' => '添加用户',
        '/api/web/user/update' => '编辑用户',
        '/api/web/user/del' => '删除用户',
        '/api/web/user/resetPassword' => '重置密码',
        '/api/web/user/changeStatus' => '账号禁用/启用',

    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $path = $request->getPathInfo();
        $remark = isset(OperateLog::REMARK[$path]) ? OperateLog::REMARK[$path] : '';
        
        // 在 LaravelS 环境中，需要尝试从不同的 guard 获取用户信息
        $user = null;
        if (strpos($path, '/api/admin/') === 0) {
            // 管理端路由，使用 admin_api guard
            $user = $request->user('admin_api') ?: auth('admin_api')->user();
        } else {
            // 其他路由，尝试默认 guard
            $user = $request->user() ?: auth()->user();
        }
        
        $userId = $user ? $user->id : 0;
        (new \App\Models\OperateLog())->store($userId, $path, $request->getMethod(), $request->getClientIp(), $request->all(), $remark);

        return $next($request);
    }
}
