<?php
namespace App\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\File;
use Obs\ObsClient;
use ZipArchive;

class Zip
{

//    public static function createZip($pathArr,$zipName)
//    {
//        $zip = new ZipArchive();
//        if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
////            print_r($pathArr);die();
//            foreach($pathArr as $file){
//                if(!file_exists($file)){
//                    continue;
//                }
//                //向压缩包中添加文件
//                $zip->addFile($file,basename($file));
//            }
//            $zip->close();
//            return ['code'=>0,'msg'=>"创建成功",'path'=>$zipName];
//        }else{
//            return ['code'=>1,'msg'=>'创建失败'];
//        }
//    }

    /**
     * 临时文件生成压缩包
     * @param $pathArr
     * @param $zipName
     * @return array
     */
    public static function createZipTemporary($pathArr,$zipName)
    {
        $zip = new ZipArchive();
        if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
            foreach($pathArr as $key => $file){

                // 临时文件路径
                $tempFilePath = tempnam(sys_get_temp_dir(), 'remote_file_');
                // 下载远程文件到临时文件
                file_put_contents($tempFilePath, file_get_contents($file));

                //向压缩包中添加文件
                $zip->addFile($tempFilePath,$key);
            }
            $zip->close();
            return ['code'=>0,'msg'=>"创建成功",'path'=>$zipName];
        }else{
            return ['code'=>1,'msg'=>'创建失败'];
        }
    }

    /**
     * 文件下载本地
     * @param $pathArr
     * @param $url_array
     * @param $path
     * @param $directory_name
     * @return array[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function downFile($pathArr, $url_array, $path, $directory_name)
    {
        //下载成功文件
        $success_array = [];
        //下载失败文件
        $fail_array = [];

        //判断文件夹是否存在
        $directory_path = $path.$directory_name;
        if (file_exists($directory_path)) {
            $directory_name = $directory_name.'_'.uniqid();
            $directory_path = $path.$directory_name;
        }
        // 创建文件夹
        File::makeDirectory($directory_path, 0755, true);

        //所有链接生成一个txt文件
        if(!empty($url_array)) {
            $url_file_path = $directory_path.'/链接.txt';
            $content = '';
            foreach ($url_array as $value) {
                $content .= $value['title'].'：'.$value['url'].PHP_EOL;
            }
            file_put_contents($url_file_path, $content, FILE_APPEND);
            $success_array['链接.txt'] = $url_file_path;
        }

        //下载远程文件
        $client = new Client();
//        $url = 'http://example.com/largefile.zip'; // 替换为你的文件URL
//        $savePath = storage_path('app/largefile.zip'); // 指定保存路径
        foreach($pathArr as $key => $url){
            $savePath = $directory_path.'/'.$key;
            try {
                $response = $client->request('GET', $url, ['sink' => fopen($savePath, 'w')]); // 使用sink选项直接保存到文件
                if ($response->getStatusCode() == 200) {
                    $success_array[$key] = $savePath;
                } else {
                    $fail_array[$key] = $url;
                }
            } catch (RequestException $e) {
                $fail_array[$key] = $url;
            }
        }

        return ['success_array' => $success_array, 'fail_array' => $fail_array, 'directory_path' => $directory_path, 'directory_name' =>$directory_name];
    }

    /**
     * 文件下载本地生成压缩包
     * @param $pathArr
     * @param $zipName
     * @return array
     */
    public static function createZip($pathArr,$zipName)
    {
//        print_r($zipName);die();
        $zip = new ZipArchive();
        if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
            foreach($pathArr as $key => $file){
                //向压缩包中添加文件
                $zip->addFile($file,$key);
            }
            $zip->close();
            return ['code'=>0,'msg'=>"创建成功",'path'=>$zipName];
        }else{
            return ['code'=>1,'msg'=>'创建失败'];
        }
    }

    //上次华为obs
    public static function uploadObs($zip)
    {
        $obsClient = new ObsClient ( [
            'key' => Env('ACCESS_KEY_ID'),
            'secret' => Env('SECRET_ACCESS_KEY'),
            'endpoint' => Env('ENDPOINT')
        ] );

        $key = "zip/" . time() . "_" . uniqid() . ".zip";
        $resp = $obsClient->putObject([
            'Bucket' => Env('BUCKET_NAME'),
            'Key' => $key,
            'SourceFile' => $zip,  // localfile为待上传的本地文件路径，需要指定到具体的文件名
            'ContentType' => 'zip',
        ]);

        return $resp;
    }

}
