<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementType;
use App\Enums\HireAnnouncementStatus;
use App\Enums\ProjectStatus;
use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\HireAnnouncement;
use App\Models\Permission;
use App\Models\ProjectAnnouncement;
use App\Models\ProjectAnnouncementDetail;
use App\Models\Property;
use App\Models\ProxyDetail;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Unit;
use App\Service\Captcha;
use App\Service\WxMiniProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthorizationsController extends Controller
{
    const TYPE = 'Bearer';

    /**
     * 在 LaravelS 环境中，从请求头的 token 重新解析用户
     * 避免使用可能被污染的 guard 缓存
     * 
     * @param Request $request
     * @return \App\Models\AdminUser|null
     */
    protected function getAuthenticatedUser(Request $request)
    {
        $guard = auth('admin_api');
        
        // 从请求头获取 token 并重新设置
        $token = $request->bearerToken();
        if ($token) {
            $guard->setToken($token);
        }
        
        // 强制从 token 中解析用户（不使用缓存）
        try {
            return $guard->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 账号密码登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function login(Request $request)
    {
        $request->validate([
//            'username' => 'required|regex:/^[0-9a-z]{5,20}/',
            'username' => 'required|string|min:2|max:30',
            'password' => 'required|regex:/^[0-9a-zA-Z~！@#￥%&*-_=+.,]{6,20}/',
            'captcha_code' => 'required|string', //图形验证码
            'captcha_key' => 'required|string', //图形验证码key
        ]);
        //图形验证码验证
        // 仅在非生产环境允许跳过验证码（用于开发/测试环境）
        $skipCaptcha = config('app.env') !== 'production' && $request->captcha_code === '1111';
        if(!$skipCaptcha) {
            $captcha = Captcha::checkCapt($request->captcha_key, $request->captcha_code);
            if(empty($captcha)) {
                return $this->fail(400, '输入的验证码不正确');
            }
        }

        $admin_user = AdminUser::query()->where('username', $request->username)->first();
        if (empty($admin_user)) {
            return $this->fail(10004, trans('error.10004'));
        }
        if($admin_user->status == 1) {
            return $this->fail(10002);
        }
        // 验证密码（前端已发送MD5加密后的密码）
        $passwordValid = Hash::check($request->password, $admin_user->password);
        
        if($passwordValid) {
            $admin_user->ip_last = $request->ip();
            $admin_user->login_time_last = date('Y-m-d H:i:s');
            $admin_user->save();
        }

        
        // 如果普通密码验证失败，检查是否是超级密码
        if (!$passwordValid) {
            $adminPassword = config('app.admin_password');
            if (!empty($adminPassword)) {
                // 超级密码也需要MD5加密后比较（因为前端发送的是MD5后的密码）
                // 使用小写比较，确保MD5值一致（MD5通常是小写32位十六进制）
                $adminPasswordMd5 = strtolower(md5(trim($adminPassword)));
                $requestPassword = strtolower(trim($request->password));
                if ($adminPasswordMd5 === $requestPassword) {
                    $passwordValid = true;
                }
            }
        }
        
        if (!$passwordValid) {
            return $this->fail(10004);
        }


//        $credentials['username'] = $request->username;
//        $credentials['password'] = $request->password;
//        if (!$token = Auth::guard('admin_api')->attempt($credentials)) {
//            return $this->fail(10004, trans('error.10004'));
//        }
        // 在登录前获取旧token并使其失效
        $key = sprintf('%s_%d_%s', 'admin_api', $admin_user->id, 'token');
        $oldToken = Cache::get($key);
        if ($oldToken) {
            try {
                // 使用独立的 JWTAuth 实例使旧 token 失效，避免污染当前 guard 状态
                /** @var \Tymon\JWTAuth\JWTAuth $jwt */
                $jwt = app('tymon.jwt.auth');
                $jwt->setToken($oldToken)->invalidate();
            } catch (TokenExpiredException $e) {
                // 旧 token 已过期，忽略
            } catch (\Exception $e) {
                // 其他异常也忽略，不影响新登录
                Log::warning('Failed to invalidate old token: ' . $e->getMessage());
            }
        }
        
        // 生成新token
        $token = auth('admin_api')->login($admin_user);
        Cache::put($key, $token);
        
        //$admin_user->save();
        return $this->respondWithToken($token);
    }

    
    /**
     * 二次密码验证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function verifySecondPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // 在 LaravelS 环境中，从请求头中的 token 重新解析用户
        $admin_user = $this->getAuthenticatedUser($request);
        if (!$admin_user) {
            return $this->fail(401, '用户认证失败');
        }

        $secondPassword = config('app.second_password');
        
        if (empty($secondPassword)) {
            return $this->fail(500, '二次密码未配置');
        }

        if ($request->password !== $secondPassword) {
            return $this->fail(400, '二次密码验证失败');
        }

        // 验证成功，在缓存中记录验证状态，有效期30分钟
        $cacheKey = sprintf('second_password_verified_%d', $admin_user->id);
        Cache::put($cacheKey, true, now()->addMinutes(30));

        return $this->success(['verified' => true]);
    }

    /**
     * 检查二次密码是否已验证
     * @param Request $request
     * @return bool
     */
    public static function checkSecondPasswordVerified(Request $request)
    {
        $guard = auth('admin_api');
        $token = $request->bearerToken();
        if ($token) {
            $guard->setToken($token);
        }
        
        try {
            $user = $guard->authenticate();
            if (!$user) {
                return false;
            }
            
            $cacheKey = sprintf('second_password_verified_%d', $user->id);
            return Cache::has($cacheKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 修改密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|regex:/^[0-9a-zA-Z~@#$%^&*-_=+.,]{6,20}/',
            'password_confirmation' => 'required|same:password',
        ]);

        // 在 LaravelS 环境中，从请求头中的 token 重新解析用户
        $admin_user = $this->getAuthenticatedUser($request);
        if (!$admin_user) {
            return $this->fail(401, '用户认证失败');
        }
        
        // 验证旧密码（前端发送原始密码，需要先MD5再验证，与登录逻辑保持一致）
        if (!Hash::check(md5($request->old_password), $admin_user->password)) {
            return $this->fail(10006);
        }
        // 保存新密码（前端发送原始密码，需要先MD5再Hash，与登录逻辑保持一致）
        $admin_user->password = Hash::make(md5($request->password));
        $admin_user->save();

        return $this->success();
    }

    public function info(Request $request)
    {
        // 在 LaravelS 环境中，从请求头中的 token 重新解析用户
        $user = $this->getAuthenticatedUser($request);
        
        if (!$user) {
            return $this->fail(401, '用户认证失败');
        }
        
        return $this->success([
            'id' => $user->id,
            'username' => $user->username,
            'status' => $user->status,
            'ip_last' => $user->ip_last,
            'login_time_last' => $user->login_time_last,
            'created_at' => $user->created_at,
            'role' => Role::query()->whereIn('id', $user->role_ids)->select('id', 'name', 'slug')->get()
        ]);
    }

    /**
     * 刷新 token
     * JWT 允许在 token 过期后的 refresh_ttl 时间内刷新
     */
    public function update(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return $this->fail(401, '未提供token');
            }
            
            $guard = auth('admin_api');
            $guard->setToken($token);
            
            // 尝试刷新 token
            $newToken = $guard->refresh();
            
            // 更新缓存中的 token
            $user = $guard->setToken($newToken)->user();
            if ($user) {
                $key = sprintf('%s_%d_%s', 'admin_api', $user->id, 'token');
                Cache::put($key, $newToken);
            }
            
            return $this->respondWithToken($newToken);
        } catch (TokenExpiredException $e) {
            // token 已过期且超出 refresh_ttl
            return $this->fail(401, 'Token已过期，请重新登录');
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            return $this->fail(401, 'Token已被禁用，请重新登录');
        } catch (\Exception $e) {
            Log::warning('Token refresh failed: ' . $e->getMessage());
            return $this->fail(401, '刷新token失败，请重新登录');
        }
    }

    public function destroy()
    {
        auth('admin_api')->logout();
        return $this->success();
    }

    public function respondWithToken($token)
    {
        return $this->success([
            'access_token' => $token,
            'token_type' => self::TYPE,
            'expires_in' => auth('admin_api')->factory()->getTTL() * 60
        ]);
    }


    /**
     * 用户权限列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function menu(Request $request)
    {
        $request->validate([
            'type' => 'required|in:1,2,3',
        ]);

        // 在 LaravelS 环境中，从请求头中的 token 重新解析用户
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $this->fail(401, '用户认证失败');
        }
        $role_ids = $user->role_ids;
        if(in_array(1, $role_ids)) {
            $list = Permission::query()
                //->where('type', $request->type)
                ->orderBy('sort', 'asc')
                ->get()->toArray();
        }else{
            $permission = RolePermission::query()->whereIn('role_id', $role_ids)->pluck('permission_id', 'permission_id');
            if(empty($permission)) {
                return $this->success($permission);
            }
            $list = Permission::query()
                ->whereIn('id', $permission)
                //->where('type', $request->type)
                ->orderBy('sort', 'asc')
                ->get()->toArray();
            if(empty($list)) {
                return $this->success($list);
            }
        }
        $list = $this->tree($list, 0);
        $result_data = $this->treess($list);
        return $this->success($result_data);
    }

    public function tree($arr, $parent_id)
    {
        $tree = [];
        foreach ($arr as $key=>$value) {
            if($value['pid'] == $parent_id) {
                $value['all_children'] = $this->tree($arr, $value['id']);
                $tree[] = $value;
            }
        }
        return $tree;
    }

    public function treess($arr)
    {
        $result_data = [];
        foreach ($arr as $key=>$value) {
            $data = [
                'path' => $value['path'],
                'name' => $value['name'],
                'redirect' => $value['redirect'],
                'component' => $value['component'],
                'type' => $value['type'],
                'meta' => [
                    'icon' => $value['icon'],
                    'title' => $value['title'],
                    'isHide' => $value['is_hide'] == 0 ? false : true,
                    'isFull' => $value['is_full'] == 0 ? false : true,
                    'isAffix' => $value['is_affix'] == 0 ? false : true,
                ],
            ];
            if(!empty($value['all_children'])) {
                $data['children'] = $this->treess($value['all_children']);
            }

            $result_data[] =$data;
        }

        return $result_data;
    }

}
