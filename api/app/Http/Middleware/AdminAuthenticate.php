<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminAuthenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $guard = !empty($guards) ? $guards[0] : 'admin_api';
        
        // 在 LaravelS 环境中，必须从请求头的 token 重新解析用户
        // 避免使用可能被污染的 guard 缓存
        $user = $this->getAuthenticatedUserFromToken($request, $guard);
        
        if (!$user) {
            throw new HttpException(Response::HTTP_OK, '账号登录异常', null, [], 401);
        }

        // 账号禁用登出
        if($user->status == 1) {
            auth($guard)->logout();
            throw new HttpException(Response::HTTP_OK, '账号已被禁用', null, [], 401);
        }

        // 账号路由权限判断
        if(!in_array(1, $user->role_ids)) {
            //获取当前访问路由
            $route = $request->getPathInfo();
            $arr = explode('/api/admin/', $route);
            $route = $arr[1] ?? '';

            //查询路由是否需要权限
            $permission = Permission::query()->where('route', $route)->first();
            if(!empty($permission)) {
                //验证用户是否有权限
                $role_permission = RolePermission::query()->whereIn('role_id', $user->role_ids)->where('permission_id', $permission->id)->first();
                if(empty($role_permission)) {
                    throw new HttpException(Response::HTTP_OK, '没有权限', null, [], 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * 从请求头的 token 中解析用户
     * 在 LaravelS 环境中必须使用此方法，避免 guard 状态污染
     *
     * @param \Illuminate\Http\Request $request
     * @param string $guard
     * @return \App\Models\AdminUser|null
     */
    protected function getAuthenticatedUserFromToken($request, $guard)
    {
        $authGuard = auth($guard);
        
        // 从请求头获取 token 并重新设置
        $token = $request->bearerToken();
        if (!$token) {
            \Illuminate\Support\Facades\Log::warning('AdminAuthenticate: No bearer token found');
            return null;
        }
        
        try {
            // 设置 token 并强制从 token 解析用户
            $authGuard->setToken($token);
            return $authGuard->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            \Illuminate\Support\Facades\Log::warning('AdminAuthenticate: Token expired - ' . $e->getMessage());
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            \Illuminate\Support\Facades\Log::warning('AdminAuthenticate: Token invalid - ' . $e->getMessage());
            return null;
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            \Illuminate\Support\Facades\Log::warning('AdminAuthenticate: Token blacklisted - ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('AdminAuthenticate: Auth failed - ' . get_class($e) . ': ' . $e->getMessage());
            return null;
        }
    }

    protected function unauthenticated($request, array $guards)
    {
        throw new HttpException(Response::HTTP_OK, '账号登录异常', null, [], 401);
        //throw new ApiException('未登录',401);
    }
}
