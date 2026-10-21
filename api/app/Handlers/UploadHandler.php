<?php


namespace App\Handlers;


use App\Models\File;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Obs\ObsClient;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;
use Illuminate\Support\Facades\Storage;

class UploadHandler
{
    protected $allowed_ext_image = ['png', 'jpg', 'gif', 'jpeg'];
    protected $allowed_ext_word = ['doc', 'docx'];
    protected $allowed_ext_pdf = ['pdf'];
    protected $allowed_ext_video = ['avi', 'mp4', 'flv', 'move', 'mpeg', 'wmv', 'rmvb', 'mp3', 'wav', 'aav', 'wma', 'flac', 'alac', 'ogg', 'aiff'];
    protected $allowed_ext_excel = ['xls', 'xlsx'];
    protected $max_size = 50; //上传文件大小限制(M)

    const TYPE = [
        1 => '项目',
        2 => '其它',
    ];
    /**
     * 文件上传 华为云obs
     * @param $img
     * @return array|int[]
     */
    public function fileUpload($img, $type): array
    {
        $allowed_ext = $this->getAllowedExt($type);
        if(empty($allowed_ext)) {
            throw new HttpException(Response::HTTP_OK, '上传文件类型错误', null, [], 10003);
        }
        // 获取文件的 MIME 类型
        $mime_type = $img->getMimeType();
        // 获取文件的后缀名，因图片从剪贴板里黏贴时后缀名为空，所以此处确保后缀一直存在
        $extension = strtolower($img->getClientOriginalExtension()) ?: 'png';
        // 如果上传的不是指定格式文件将终止操作
        if ( ! in_array($extension, $allowed_ext)) {
            throw new HttpException(Response::HTTP_OK, '上传文件类型错误', null, [], 10003);
        }
        $display_name = $img->getClientOriginalName();
        $key = date('Y-m-d') . "/" . time() . "_" . uniqid() . "." . $extension;
        //获取文件大小
        $file_size = $img->getSize();
        //检测文件大小
        if ($file_size > $this->max_size * 1024 * 1024) {
            throw new HttpException(Response::HTTP_OK, "文件大小不能超过" . $this->max_size . "M", null, [], 10003);
        }
        try {
            $obsClient = new ObsClient ( [
                'key' => Env('ACCESS_KEY_ID'),
                'secret' => Env('SECRET_ACCESS_KEY'),
                'endpoint' => Env('ENDPOINT')
            ] );
            $resp = $obsClient->putObject([
                'Bucket' => Env('BUCKET_NAME'),
                'Key' => $key,
                'SourceFile' => $img,  // localfile为待上传的本地文件路径，需要指定到具体的文件名
                'ContentType' => $mime_type,
            ]);

            //文件大小转换
            $size = $this->getFilesize($file_size);
            //存储
            $file = File::query()->create([
                'path' => $key,
                'display_name' => $display_name,
                'extension' => strtolower($extension),
                'size' => $size,
            ]);

            $url_arr = explode('https://jtzcpt.obs.cidc-rp-2065.cdmgcloud.com:443/', $resp['ObjectURL']);
        } catch (\Exception $e) {
            Log::info('文件上传失败', ['code'=>$e->getCode(), 'msg'=>$e->getMessage()]);
            throw new HttpException(Response::HTTP_OK, '文件上传失败', null, [], 10003);
        }

        return ['code'=>0, 'file_id' => $file->id, 'display_name'=>$display_name, 'path'=>$key, 'url' => Env('OBS_URL').$url_arr[1], 'file_size'=>$size, 'created_at'=>Carbon::parse($file->created_at)->format('Y-m-d H:i:s')];
    }

    /**
     * 获取允许上传的文件类型
     * @param $type
     * @return array
     */
    public function getAllowedExt($type)
    {
        $file_typ = explode(',', $type);
        $allowed_ext = [];
        foreach ($file_typ as $key => $value) {
//            array_merge($allowed_ext, )
            switch ($value) {
                case 'image':
                    $allowed_ext = array_merge($allowed_ext, $this->allowed_ext_image);
                    break;
                case 'pdf':
                    $allowed_ext = array_merge($allowed_ext, $this->allowed_ext_pdf);
                    break;
                case 'word':
                    $allowed_ext = array_merge($allowed_ext, $this->allowed_ext_word);
                    break;
                case 'video':
                    $allowed_ext = array_merge($allowed_ext, $this->allowed_ext_video);
                    break;
                case 'excel':
                    $allowed_ext = array_merge($allowed_ext, $this->allowed_ext_excel);
                    break;
                default:

                    break;
            }
        }

        return $allowed_ext;
    }

    /**
     * 获取文件大小
     * @param $num
     * @return string
     */
    public function getFilesize($num) {
        $p = 0;
        $format = 'bytes';
        if( $num > 0 && $num < 1024 ) {
            $p = 0;
            return number_format($num) . ' ' . $format;
        }
        if( $num >= 1024 && $num < pow(1024, 2) ){
            $p = 1;
            $format = 'KB';
        }
        if ( $num >= pow(1024, 2) && $num < pow(1024, 3) ) {
            $p = 2;
            $format = 'MB';
        }
        if ( $num >= pow(1024, 3) && $num < pow(1024, 4) ) {
            $p = 3;
            $format = 'GB';
        }
        if ( $num >= pow(1024, 4) && $num < pow(1024, 5) ) {
            $p = 3;
            $format = 'TB';
        }
        $num /= pow(1024, $p);
        return number_format($num, 2) . ' ' . $format;
    }


    /**
     * 分片上传 华为云OBS
     */
    public function multipartUpload( $filename, $display_name = "")
    {
        $chunkSize = 5 * 1024 * 1024; // 5MB（OBS 要求分片最小5MB）
        // 检查文件是否存在
        if (!Storage::disk('video')->exists($filename)) {
            return false;
        }

        // 获取文件信息
        $file_size = Storage::disk('video')->size($filename);      // 文件大小（字节）
        $mimeType = Storage::disk('video')->mimeType($filename); // MIME 类型
        $filepath = Storage::disk('video')->path($filename);

        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'mp4';
        $key = date('Y-m-d') . "/" . time() . "_" . uniqid() . "." . $extension;
        //info($key);
        if(empty($display_name)){
            $display_name = $filename;
        }else{
            $display_name = $display_name. "." . $extension;
        }

        //exit;
        try {
            $obsClient = new ObsClient ( [
                'key' => Env('ACCESS_KEY_ID'),
                'secret' => Env('SECRET_ACCESS_KEY'),
                'endpoint' => Env('ENDPOINT')
            ] );

            // 1. 初始化分段上传
            $uploadId = self::initiateMultipartUpload($obsClient,$key,$mimeType);

            // 2. 分片上传
            $parts = [];
            $handle = fopen($filepath, 'rb');
            $partNumber = 1;
            while (!feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                $etag = self::uploadPart($obsClient,$key, $uploadId, $partNumber, $chunk);
                $parts[] = ['PartNumber' => $partNumber, 'ETag' => $etag];
                $partNumber++;
            }
            fclose($handle);

            // 3. 完成上传
            $re = self::completeMultipartUpload($obsClient,$key, $uploadId, $parts);
            if($re === true){
                //文件大小转换
                $size = $this->getFilesize($file_size);
                //存储
                $file = File::query()->create([
                    'path' => $key,
                    'display_name' => $display_name,
                    'extension' => strtolower($extension),
                    'size' => $size,
                ]);
                return ['code'=>0, 'file_id' => $file->id, 'display_name'=>$display_name, 'path'=>$key, 'url' => Env('OBS_URL').$key, 'file_size'=>$size, 'created_at'=>Carbon::parse($file->created_at)->format('Y-m-d H:i:s')];

            }
            return false;

        } catch (\Exception $e) {
            Log::info('文件上传失败', ['code'=>$e->getCode(), 'msg'=>$e->getMessage()]);
            return false;
            //throw new Exception("文件上传失败: {$e->getCode()}");
        }

    }



    /**
     * 初始化分段上传任务
     */
    public function initiateMultipartUpload($obsClient, string $objectKey,$mimeType): string
    {
        $response = $obsClient->initiateMultipartUpload([
            'Bucket' => Env('BUCKET_NAME'),
            'Key' => $objectKey,
            // 'ContentType' => $mimeType,  //application/octet-stream
        ]);
        return $response['UploadId'];
    }

    /**
     * 上传分片
     */
    public function uploadPart(
        $obsClient,
        string $objectKey,
        string $uploadId,
        int $partNumber,
        string $body
    ): string {
        $response = $obsClient->uploadPart([
            'Bucket' => Env('BUCKET_NAME'),
            'Key' => $objectKey,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
            'Body' => $body,
        ]);

        return $response['ETag'];
    }

    /**
     * 完成分段上传
     */
    public function completeMultipartUpload(
        $obsClient,
        string $objectKey,
        string $uploadId,
        array $parts // 格式 [['PartNumber' => 1, 'ETag' => 'etag1'], ...]
    ): bool {
        $obsClient->completeMultipartUpload([
            'Bucket' => Env('BUCKET_NAME'),
            'Key' => $objectKey,
            'UploadId' => $uploadId,
            'Parts' => $parts,
        ]);

        return true;
    }


}
