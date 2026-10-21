<?php

namespace App\Http\Controllers\Admin;

use App\Handlers\UploadHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadRequest;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UploadController extends Controller
{
    /**
     * 单文件上传
     * @param UploadRequest $request
     * @param UploadHandler $uploader
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(UploadRequest $request, UploadHandler $uploader)
    {
        $result = $uploader->fileUpload($request->file, $request->type);
        if($result['code'] != 0) {
            return $this->fail($result['code']);
        }
        return $this->success(['file_id'=>$result['file_id'], 'display_name'=>$result['display_name'], 'path' => $result['path'], 'url' => $result['url'], 'file_size' => $result['file_size'], 'created_at' => $result['created_at']]);
    }

    /**
     * 多文件上传
     * @param Request $request
     * @param UploadHandler $uploader
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function manyStore(Request $request, UploadHandler $uploader)
    {
        $image_arr = [];
        foreach ($request->file as $file) {
            $result = $uploader->fileUpload($file);
            if($result['code'] != 0) {
                return $this->fail($result['code']);
            }
            $image_arr[] = $result['path'];
        }

        return $this->success($image_arr);
    }

    /**
     * 链接上传
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function urlStore(Request $request)
    {
        $request->validate([
            'path' => 'required|unique:files,path',
        ],[
            'path' => '链接已存在',
        ]);
        $file = File::query()->create([
            'path' => $request->path,
        ]);

        return $this->success(['file_id'=>$file->id, 'path'=>$file->path, 'created_at'=>Carbon::parse($file->created_at)->format('Y-m-d H:i:s')]);
    }


    /**
     * 保存文件链接
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function urlSave(Request $request)
    {
        $request->validate([
            'path' => 'required',
            'display_name' => 'required',
            'extension' => 'required',
            'size' => 'required'
        ]);
        $file = File::query()->create([
            'path' => $request->path,
            'display_name' => $request->display_name,
            'extension' => $request->extension,
            'size' => $request->size,
        ]);
        return $this->success(['file_id'=>$file->id, 'display_name'=>$file->display_name, 'path' => $file->path, 'url' => Env('OBS_URL').$file->path, 'file_size' => $file->size, 'created_at' => Carbon::parse($file->created_at)->format('Y-m-d H:i:s')]);
    }


    public function click(Request $request){
        File::query()->where('id','=',$request->id)->increment('click');

        return $this->success();
    }


}
