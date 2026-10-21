<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpWhitelist;
use Illuminate\Http\Request;

class IpWhitelistController extends Controller
{

    /**
     * IP白名单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'nullable|int',
            'limit' => 'nullable|int',
            'ip' => 'nullable|string',
            'type' => 'nullable|string|in:whitelist,blacklist',
            'status' => 'nullable|int|in:0,1',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);

        $filters = [];
        if (!empty($request->ip)) {
            $filters['ip'] = $request->ip;
        }
        if (!empty($request->type)) {
            $filters['type'] = $request->type;
        }
        if ($request->has('status')) {
            $filters['status'] = $request->status;
        }

        $result = IpWhitelist::getList($filters, $page, $limit);

        return $this->success($result);
    }

    /**
     * 获取指定IP白名单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = IpWhitelist::getById($request->id);

        if (empty($info)) {
            return $this->fail(404, 'IP白名单不存在');
        }

        return $this->success($info);
    }

    /**
     * 添加IP白名单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'ip' => 'required|string|max:50',
            'type' => 'nullable|string|in:whitelist,blacklist',
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|int|in:0,1',
        ]);

        // 验证IP格式（支持单个IP或CIDR格式）
        $ip = $request->ip;
        if (strpos($ip, '/') !== false) {
            // CIDR格式验证
            list($subnet, $mask) = explode('/', $ip);
            if (!filter_var($subnet, FILTER_VALIDATE_IP) || !is_numeric($mask) || $mask < 0 || $mask > 32) {
                return $this->fail(400, 'IP格式不正确，CIDR格式应为：192.168.1.0/24');
            }
        } else {
            // 单个IP验证
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return $this->fail(400, 'IP格式不正确');
            }
        }

        // 检查是否已存在
        $exists = IpWhitelist::query()->where('ip', $ip)->first();
        if ($exists) {
            return $this->fail(400, '该IP已存在');
        }

        $data = [
            'ip' => $ip,
            'type' => $request->get('type', IpWhitelist::TYPE_WHITELIST),
            'description' => $request->get('description', ''),
            'status' => $request->get('status', IpWhitelist::STATUS_ENABLED),
        ];

        $ipWhitelist = IpWhitelist::create($data);

        return $this->success($ipWhitelist->toArray());
    }

    /**
     * 更新IP白名单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'ip' => 'nullable|string|max:50',
            'type' => 'nullable|string|in:whitelist,blacklist',
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|int|in:0,1',
        ]);

        $ipWhitelist = IpWhitelist::query()->find($request->id);
        if (empty($ipWhitelist)) {
            return $this->fail(404, 'IP白名单不存在');
        }

        // 如果更新IP，验证格式
        if ($request->has('ip')) {
            $ip = $request->ip;
            if (strpos($ip, '/') !== false) {
                // CIDR格式验证
                list($subnet, $mask) = explode('/', $ip);
                if (!filter_var($subnet, FILTER_VALIDATE_IP) || !is_numeric($mask) || $mask < 0 || $mask > 32) {
                    return $this->fail(400, 'IP格式不正确，CIDR格式应为：192.168.1.0/24');
                }
            } else {
                // 单个IP验证
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $this->fail(400, 'IP格式不正确');
                }
            }

            // 检查是否与其他记录重复
            $exists = IpWhitelist::query()
                ->where('ip', $ip)
                ->where('id', '!=', $request->id)
                ->first();
            if ($exists) {
                return $this->fail(400, '该IP已存在');
            }
        }

        $data = [];
        if ($request->has('ip')) {
            $data['ip'] = $request->ip;
        }
        if ($request->has('type')) {
            $data['type'] = $request->type;
        }
        if ($request->has('description')) {
            $data['description'] = $request->description;
        }
        if ($request->has('status')) {
            $data['status'] = $request->status;
        }

        $ipWhitelist->update($data);
        // 确保 updated_at 字段被更新
        $ipWhitelist->touch();

        return $this->success($ipWhitelist->toArray());
    }

    /**
     * 删除IP白名单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $ipWhitelist = IpWhitelist::query()->find($request->id);
        if (empty($ipWhitelist)) {
            return $this->fail(404, 'IP白名单不存在');
        }

        $ipWhitelist->delete();

        return $this->success();
    }

    /**
     * 检查IP是否在白名单中
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function check(Request $request)
    {

        // 检查白名单检测开关
        $whitelistEnabled = config('ip_whitelist.enabled', false);
        // 如果开关关闭，则全部通过
        if (!$whitelistEnabled) {
            return $this->success([
                'ip' => '0.0.0.0',
                'result' => 'pass',
            ]);
        }

        $ip = getRealIp($request);
        
        info('白名单ip', ['ip' => $ip]);
        
        // 验证IP格式
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->success([
                'ip' => $ip,
                'result' => 'fail',
            ]);
        }

        //检测白名单
        $isWhitelisted = IpWhitelist::isWhitelisted($ip);

        $result = $isWhitelisted ? 'pass' : 'fail';

        return $this->success([
            'ip' => $ip,
            'result' => $result,
        ]);
    }

    /**
     * 获取所有启用的白名单IP
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getAllWhitelistIps(Request $request)
    {
        $ips = IpWhitelist::getAllWhitelistIps();
        return $this->success($ips);
    }

    /**
     * 获取所有启用的黑名单IP
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getAllBlacklistIps(Request $request)
    {
        $ips = IpWhitelist::getAllBlacklistIps();
        return $this->success($ips);
    }

}

