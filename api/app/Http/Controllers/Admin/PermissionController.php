<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{

    /**
     * 权限列表
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function list()
    {
        $list = Permission::query()
            ->where('pid', 0)
            ->with(['allChildren'=>function($q) {
                $q->orderBy('sort', 'asc');
            }])
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();
        $list = $this->treess($list);
        return $this->success($list);
    }

    /**
     * 获取指定权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);
        $info =  Permission::query()->where('id', $request->id)->first()->toArray();
        $data = [];
        if(!empty($info)) {
            $data = [
                'id' => $info['id'],
                'pid' => $info['pid'],
                'sort' => $info['sort'],
                'route' => $info['route'],
                'path' => $info['path'],
                'name' => $info['name'],
                'redirect' => $info['redirect'],
                'component' => $info['component'],
                'type' => $info['type'],
                'meta' => [
                    'icon' => $info['icon'],
                    'title' => $info['title'],
                    'isHide' => $info['is_hide'] == 0 ? false : true,
                    'isFull' => $info['is_full'] == 0 ? false : true,
                    'isAffix' => $info['is_affix'] == 0 ? false : true,
                ],
            ];
        }
        return $this->success($data);
    }

    /**
     * 下拉列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function select()
    {
        $list =  Permission::query()
            ->where('pid', 0)
            ->with(['child'=>function($q) {
                $q->select('id', 'pid', 'title', 'sort')->orderBy('sort', 'asc');
            }])
            ->orderBy('sort', 'asc')
            ->select('id', 'pid', 'title', 'sort')
            ->get();
        return $this->success($list);
    }

    /**
     * 添加权限
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function add(Request $request)
    {
        $request->validate([
            'pid' => 'required|int',
            'title' => 'required|string',
            'sort' => 'required|int',
            'type' => 'required|in:1,2,3',
        ]);

        Permission::query()->create([
            'pid' => $request->pid,
            'title' => $request->title,
            'redirect' => !empty($request->get('redirect')) ? $request->get('redirect') : '',
            'route' => !empty($request->route) ? $request->route : '',
            'name' => !empty($request->name) ? $request->name : '',
            'path' => !empty($request->path) ? $request->path : '',
            'component' => !empty($request->component) ? $request->component : '',
            'icon' => !empty($request->icon) ? $request->icon : '',
            'is_hide' => !empty($request->is_hide) ? $request->is_hide : false,
            'is_full' => !empty($request->is_full) ? $request->is_full : false,
            'is_affix' => !empty($request->is_affix) ? $request->is_affix : false,
            'sort' => $request->sort,
            'type' => $request->type,
        ]);
        return $this->success();
    }

    /**
     * 编辑权限
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|int',
            'pid' => 'required|int',
            'title' => 'required|string',
            'sort' => 'required|int',
            'type' => 'required|in:1,2,3',
        ]);

        $info = Permission::query()->where('id', $request->id)->firstOrFail();
        $info->pid = $request->pid;
        $info->title = $request->title;
        $info->redirect = !empty($request->get('redirect')) ? $request->get('redirect') : '';
        $info->route = !empty($request->route) ? $request->route : '';
        $info->name = !empty($request->name) ? $request->name : '';
        $info->path = !empty($request->path) ? $request->path : '';
        $info->component = !empty($request->component) ? $request->component : '';
        $info->icon = !empty($request->icon) ? $request->icon : '';
        $info->is_hide = !empty($request->is_hide) ? $request->is_hide : false;
        $info->is_full = !empty($request->is_full) ? $request->is_full : false;
        $info->is_affix = !empty($request->is_affix) ? $request->is_affix : false;
        $info->sort = $request->sort;
        $info->type = $request->type;
        $info->save();

        return $this->success();
    }

    /**
     * 删除指定权限
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);
        Permission::query()->where('id', $request->id)->delete();
        return $this->success();
    }

    public function treess($arr)
    {
        $result_data = [];
        foreach ($arr as $key=>$value) {
            $data = [
                'id' => $value['id'],
                'pid' => $value['pid'],
                'sort' => $value['sort'],
                'route' => $value['route'],
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
