<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperateLog;
use Illuminate\Http\Request;

class OperateLogController extends Controller
{

    /**
     * 日志记录列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list(Request $request)
    {
        $request->validate([
            'page' => 'required|int',
            'limit' => 'required|int',
            'username' => 'nullable|string',
            'methods' => 'nullable|string',
            'path' => 'nullable|string',
            'ip' => 'nullable|string',
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $page = $this->paging($page, $limit);

        $model = OperateLog::query()->with('adminUser:id,username');
        if(!empty($request->username)) {
            $username = $request->username;
            $model->whereHas('adminUser', function ($query) use ($username) {
                $query->where('username', $username);
            });
        }
        if(!empty($request->methods)) {
            $model->where('methods', $request->methods);
        }
        if(!empty($request->path)) {
            $model->where('path', $request->path);
        }
        if(!empty($request->ip)) {
            $model->where('ip', $request->ip);
        }

        $count = $model->count();
        $list = $model->offset($page)->limit($limit)->orderBy('id', 'desc')->get();
        return $this->success(['total_num'=> $count, 'list'=>$list]);
    }
}
