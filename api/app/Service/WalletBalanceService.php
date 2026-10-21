<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * 钱包余额查询服务
 * 提供各种加密货币的余额查询功能
 */
class WalletBalanceService
{
    /**
     * 最大响应体大小（10MB），防止内存耗尽
     */
    private const MAX_RESPONSE_SIZE = 10 * 1024 * 1024; // 10MB
    
    /**
     * 初始化内存设置
     */
    private static function initMemorySettings(): void
    {
        // 增加内存限制到512MB（如果当前限制小于512MB）
        $currentLimit = ini_get('memory_limit');
        $currentBytes = self::parseMemoryLimit($currentLimit);
        $minBytes = 4 * 1024 * 1024 * 1024; // 512MB
        
        if ($currentBytes < $minBytes) {
            ini_set('memory_limit', '4g');
        }
    }
    
    /**
     * 解析内存限制字符串为字节数
     */
    private static function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    /**
     * 查询BTC钱包余额
     *
     * @param string $address BTC钱包地址
     * @return string|null 余额（字符串格式），查询失败返回null
     */
    public static function queryBtcBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 方案1：使用Blockchain.info API（免费，无需API Key）
            $btcBalance = null;
            $blockchainResponse = Http::timeout($timeout)
                ->withOptions([
                    'stream' => false, // 不使用流式处理，但限制响应大小
                    'http_errors' => false,
                ])
                ->get("https://blockchain.info/q/addressbalance/{$address}");
            
            if ($blockchainResponse->successful()) {
                $body = $blockchainResponse->body();
                // 检查响应体大小
                if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                    Log::warning('BTC余额查询响应体过大', [
                        'address' => $address,
                        'size' => strlen($body)
                    ]);
                    unset($blockchainResponse, $body);
                    return null;
                }
                $balanceSatoshi = $body;
                // 立即释放响应对象和body（在使用body()之后）
                unset($blockchainResponse, $body);
                // 验证返回的是数字
                if (is_numeric($balanceSatoshi)) {
                    // BTC余额单位是Satoshi，1 BTC = 100,000,000 Satoshi
                    $btcBalance = bcdiv((string)$balanceSatoshi, '100000000', 8);
                }
                unset($balanceSatoshi);
            } else {
                // 立即释放响应对象
                unset($blockchainResponse);
            }
            
            // 如果方案1失败，尝试方案2：使用BlockCypher API
            if ($btcBalance === null) {
                $blockcypherResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("https://api.blockcypher.com/v1/btc/main/addrs/{$address}/balance");
                
                if ($blockcypherResponse->successful()) {
                    // 先检查响应体大小
                    $body = $blockcypherResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('BTC余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($blockcypherResponse, $body);
                    } else {
                        $blockcypherData = $blockcypherResponse->json();
                        // 立即释放响应对象（在使用json()之后）
                        unset($blockcypherResponse, $body);
                        if (isset($blockcypherData['balance'])) {
                            // BlockCypher返回的余额单位是Satoshi
                            $balanceSatoshi = $blockcypherData['balance'];
                            $btcBalance = bcdiv((string)$balanceSatoshi, '100000000', 6);
                            unset($balanceSatoshi);
                        }
                        // 立即释放数据
                        unset($blockcypherData);
                    }
                } else {
                    unset($blockcypherResponse);
                }
            }
            
            // 如果方案2也失败，尝试方案3：使用Blockstream API
            if ($btcBalance === null) {
                $blockstreamResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("https://blockstream.info/api/address/{$address}");
                
                if ($blockstreamResponse->successful()) {
                    // 先检查响应体大小
                    $body = $blockstreamResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('BTC余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($blockstreamResponse, $body);
                    } else {
                        $blockstreamData = $blockstreamResponse->json();
                        // 立即释放响应对象（在使用json()之后）
                        unset($blockstreamResponse, $body);
                        if (isset($blockstreamData['chain_stats']['funded_txo_sum']) && 
                            isset($blockstreamData['chain_stats']['spent_txo_sum'])) {
                            // 计算余额 = 收到的总额 - 花费的总额
                            $funded = $blockstreamData['chain_stats']['funded_txo_sum'];
                            $spent = $blockstreamData['chain_stats']['spent_txo_sum'];
                            $balanceSatoshi = $funded - $spent;
                            $btcBalance = bcdiv((string)$balanceSatoshi, '100000000', 6);
                            unset($funded, $spent, $balanceSatoshi);
                        }
                        // 立即释放数据
                        unset($blockstreamData);
                    }
                } else {
                    unset($blockstreamResponse);
                }
            }
            
            // 如果所有方案都失败，返回null
            if ($btcBalance === null) {
                return null;
            }
            
            // 格式化返回字符串
            return $btcBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询BTC余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询ETH钱包余额（仅ETH，不包含USDT）
     *
     * @param string $address ETH钱包地址
     * @return string|null 余额（decimal格式字符串），查询失败返回null
     */
    public static function queryEthBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // Etherscan API基础URL（如果没有API Key，可以使用公共RPC节点）
            $etherscanApiKey = env('ETHERSCAN_API_KEY', '');
            $baseUrl = 'https://api.etherscan.io';
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 查询ETH余额
            $ethBalance = null;
            
            if (!empty($etherscanApiKey)) {
                // 使用Etherscan API查询ETH余额
                $ethResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("{$baseUrl}/api", [
                        'module' => 'account',
                        'action' => 'balance',
                        'address' => $address,
                        'tag' => 'latest',
                        'apikey' => $etherscanApiKey
                    ]);
                
                if ($ethResponse->successful()) {
                    // 先检查响应体大小
                    $body = $ethResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('ETH余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($ethResponse, $body);
                    } else {
                        $ethData = $ethResponse->json();
                        unset($ethResponse, $body); // 立即释放响应对象
                        if (isset($ethData['status']) && $ethData['status'] == '1' && isset($ethData['result'])) {
                            // ETH余额单位是Wei，1 ETH = 10^18 Wei
                            $balanceWei = $ethData['result'];
                            $ethBalance = bcdiv((string)$balanceWei, '1000000000000000000', 8);
                            unset($balanceWei);
                        }
                        unset($ethData);
                    }
                } else {
                    unset($ethResponse);
                }
            } else {
                // 如果没有API Key，使用公共RPC节点查询ETH余额
                // 使用多个备用RPC节点，提高可靠性
                $rpcUrls = [
                    'https://eth.llamarpc.com',
                    'https://rpc.ankr.com/eth',
                    'https://ethereum.publicnode.com',
                    'https://eth.merkle.io',
                ];
                
                foreach ($rpcUrls as $rpcUrl) {
                    try {
                        $rpcResponse = Http::timeout($timeout)
                            ->retry(2, 100) // 重试2次，每次间隔100ms
                            ->withOptions([
                                'stream' => false,
                                'http_errors' => false,
                            ])
                            ->post($rpcUrl, [
                                'jsonrpc' => '2.0',
                                'method' => 'eth_getBalance',
                                'params' => [$address, 'latest'],
                                'id' => 1
                            ]);
                        
                        if ($rpcResponse->successful()) {
                            // 先检查响应体大小
                            $body = $rpcResponse->body();
                            if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                                Log::warning('ETH RPC响应体过大', [
                                    'address' => $address,
                                    'rpc_url' => $rpcUrl,
                                    'size' => strlen($body)
                                ]);
                                unset($rpcResponse, $body);
                                continue;
                            }
                            $rpcData = $rpcResponse->json();
                            unset($rpcResponse, $body); // 立即释放响应对象
                            if (isset($rpcData['result']) && !isset($rpcData['error'])) {
                                // 将十六进制转换为十进制Wei，然后转换为ETH
                                $balanceWei = self::hexToDec($rpcData['result']);
                                $ethBalance = bcdiv($balanceWei, '1000000000000000000', 8);
                                unset($balanceWei);
                                unset($rpcData);
                                break; // 成功获取余额，跳出循环
                            }
                            unset($rpcData);
                        } else {
                            unset($rpcResponse);
                        }
                    } catch (\Exception $e) {
                        // 记录错误但继续尝试下一个节点
                        Log::debug('ETH RPC节点连接失败，尝试下一个', [
                            'rpc_url' => $rpcUrl,
                            'address' => $address,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            }
            
            return $ethBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询ETH余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询BSC钱包余额（仅BNB，不包含USDT）
     *
     * @param string $address BSC钱包地址
     * @return string|null 余额（decimal格式字符串），查询失败返回null
     */
    public static function queryBscBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // BscScan API基础URL（如果没有API Key，可以使用公共RPC节点）
            $bscscanApiKey = env('BSCSCAN_API_KEY', '');
            $baseUrl = 'https://api.bscscan.com';
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 查询BNB余额
            $bnbBalance = null;
            
            if (!empty($bscscanApiKey)) {
                // 使用BscScan API查询BNB余额
                $bnbResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("{$baseUrl}/api", [
                        'module' => 'account',
                        'action' => 'balance',
                        'address' => $address,
                        'tag' => 'latest',
                        'apikey' => $bscscanApiKey
                    ]);
                
                if ($bnbResponse->successful()) {
                    // 先检查响应体大小
                    $body = $bnbResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('BSC余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($bnbResponse, $body);
                    } else {
                        $bnbData = $bnbResponse->json();
                        unset($bnbResponse, $body); // 立即释放响应对象
                        if (isset($bnbData['status']) && $bnbData['status'] == '1' && isset($bnbData['result'])) {
                            // BNB余额单位是Wei，1 BNB = 10^18 Wei
                            $balanceWei = $bnbData['result'];
                            $bnbBalance = bcdiv((string)$balanceWei, '1000000000000000000', 8);
                            unset($balanceWei);
                        }
                        unset($bnbData);
                    }
                } else {
                    unset($bnbResponse);
                }
            } else {
                // 如果没有API Key，使用公共RPC节点查询BNB余额
                $rpcUrl = 'https://bsc-dataseed.binance.org/'; // 币安官方RPC节点
                $rpcResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->post($rpcUrl, [
                        'jsonrpc' => '2.0',
                        'method' => 'eth_getBalance',
                        'params' => [$address, 'latest'],
                        'id' => 1
                    ]);
                
                if ($rpcResponse->successful()) {
                    // 先检查响应体大小
                    $body = $rpcResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('BSC RPC响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($rpcResponse, $body);
                    } else {
                        $rpcData = $rpcResponse->json();
                        unset($rpcResponse, $body); // 立即释放响应对象
                        if (isset($rpcData['result'])) {
                            // 将十六进制转换为十进制Wei，然后转换为BNB
                            $balanceWei = self::hexToDec($rpcData['result']);
                            $bnbBalance = bcdiv($balanceWei, '1000000000000000000', 8);
                            unset($balanceWei);
                        }
                        unset($rpcData);
                    }
                } else {
                    unset($rpcResponse);
                }
            }
            
            return $bnbBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询BSC余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询TRON钱包余额（仅TRX，不包含USDT）
     *
     * @param string $address TRON钱包地址
     * @return string|null 余额（decimal格式字符串），查询失败返回null
     */
    public static function queryTronBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // TronGrid API基础URL
            $baseUrl = 'https://api.trongrid.io';
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 查询TRX余额
            $trxBalance = null;
            
            // 方法1：使用RPC方法查询TRX余额（最可靠）
            try {
                $trxRpcResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->post("{$baseUrl}/wallet/getaccount", [
                        'address' => $address,
                        'visible' => true
                    ]);
                
                if ($trxRpcResponse->successful()) {
                    // 先检查响应体大小
                    $body = $trxRpcResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('TRON余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($trxRpcResponse, $body);
                    } else {
                        $trxRpcData = $trxRpcResponse->json();
                        unset($trxRpcResponse, $body);
                    
                        // TRX余额在balance字段，单位是sun
                        if (isset($trxRpcData['balance'])) {
                            $balanceSun = $trxRpcData['balance'];
                            $trxBalance = bcdiv((string)$balanceSun, '1000000', 8);
                            unset($balanceSun);
                        }
                        unset($trxRpcData);
                    }
                } else {
                    Log::warning('TRON RPC响应异常', [
                        'address' => $address,
                        'status' => $trxRpcResponse->status(),
                        'body' => substr((string) $trxRpcResponse->body(), 0, 200),
                    ]);
                    unset($trxRpcResponse);
                }
            } catch (\Exception $e) {
                Log::debug('TRON TRX余额RPC查询失败', [
                    'address' => $address,
                    'error' => $e->getMessage()
                ]);
            }
            
            // 方法2：如果RPC方法没有获取到TRX余额，尝试使用/v1/accounts/{address} API
            if ($trxBalance === null) {
                $accountResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("{$baseUrl}/v1/accounts/{$address}");
                
                if ($accountResponse->successful()) {
                    // 先检查响应体大小
                    $body = $accountResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('TRON账户查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($accountResponse, $body);
                    } else {
                        $accountData = $accountResponse->json();
                        unset($accountResponse, $body);
                    
                        if (isset($accountData['data']) && is_array($accountData['data']) && count($accountData['data']) > 0) {
                            $account = $accountData['data'][0];
                            
                            // TRX余额单位是sun，1 TRX = 1,000,000 sun
                            if (isset($account['balance'])) {
                                $balanceSun = $account['balance'];
                                $trxBalance = bcdiv((string)$balanceSun, '1000000', 8);
                                unset($balanceSun);
                            }
                            
                            unset($account);
                        }
                        unset($accountData);
                    }
                } else {
                    Log::warning('TRON账户查询响应异常', [
                        'address' => $address,
                        'status' => $accountResponse->status(),
                        'body' => substr((string) $accountResponse->body(), 0, 200),
                    ]);
                    unset($accountResponse);
                }
            }
            
            return $trxBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询TRON余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询XRP钱包余额
     *
     * @param string $address XRP钱包地址
     * @return string|null 余额（字符串格式），查询失败返回null
     */
    public static function queryXrpBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // 设置请求超时时间（30秒，因为XRP API可能返回大量数据）
            $timeout = 30;
            
            // 优先使用XRPL JSON-RPC，响应更小更稳定
            $xrpBalance = null;
            $rpcUrl = 'https://s1.ripple.com:51234/';
            $rpcResponse = Http::timeout($timeout)
                ->withOptions([
                    'stream' => false,
                    'http_errors' => false,
                ])
                ->post($rpcUrl, [
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'account_info',
                    'params' => [[
                        'account' => $address,
                        'strict' => true,
                        'ledger_index' => 'validated',
                    ]],
                ]);
            
            if ($rpcResponse->successful()) {
                $rpcData = $rpcResponse->json();
                unset($rpcResponse);
                
                if (isset($rpcData['result']['account_data']['Balance'])) {
                    // Balance单位为drops，1 XRP = 1,000,000 drops
                    $balanceDrops = $rpcData['result']['account_data']['Balance'];
                    $xrpBalance = bcdiv((string)$balanceDrops, '1000000', 6);
                    unset($balanceDrops);
                }
                unset($rpcData);
            } else {
                Log::warning('XRP RPC响应异常', [
                    'address' => $address,
                    'status' => $rpcResponse->status(),
                    'body' => substr((string) $rpcResponse->body(), 0, 200),
                ]);
                unset($rpcResponse);
            }
            
            // 多个备用API端点
            $apiEndpoints = [
                [
                    'url' => "https://api.xrpscan.com/api/v1/account/{$address}",
                    'balance_field' => 'initial_balance',
                    'type' => 'xrpscan'
                ],
            ];
            
            // 尝试每个API端点
            if ($xrpBalance === null) {
                foreach ($apiEndpoints as $endpoint) {
                    try {
                    // 先使用HEAD请求检查响应体大小（如果API支持）
                    $guzzleClient = new Client([
                        'timeout' => $timeout,
                        'http_errors' => false,
                    ]);
                    
                    $shouldSkip = false;
                    try {
                        $headResponse = $guzzleClient->head($endpoint['url']);
                        if ($headResponse->getStatusCode() === 200) {
                            $contentLength = $headResponse->getHeaderLine('Content-Length');
                            if ($contentLength && (int)$contentLength > self::MAX_RESPONSE_SIZE) {
                                Log::warning('XRP余额查询响应体过大（预检查），跳过', [
                                    'address' => $address,
                                    'api_url' => $endpoint['url'],
                                    'content_length' => $contentLength,
                                    'size_mb' => round((int)$contentLength / 1024 / 1024, 2)
                                ]);
                                $shouldSkip = true;
                            }
                        }
                    } catch (\Exception $e) {
                        // HEAD请求失败，继续使用GET请求（某些API可能不支持HEAD）
                        Log::debug('XRP API HEAD请求失败，将使用GET请求', [
                            'api_url' => $endpoint['url'],
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    if ($shouldSkip) {
                        continue; // 跳过该端点
                    }
                    
                    // 使用GET请求获取实际数据
                    $response = Http::timeout($timeout)
                        ->retry(2, 200) // 重试2次，每次间隔200ms
                        ->withOptions([
                            'stream' => false,
                            'http_errors' => false,
                        ])
                        ->get($endpoint['url']);
                    
                    if ($response->successful()) {
                        // 再次检查响应体大小（防止HEAD请求不支持的情况）
                        $body = $response->body();
                        if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                            Log::warning('XRP余额查询响应体过大，跳过', [
                                'address' => $address,
                                'api_url' => $endpoint['url'],
                                'size' => strlen($body),
                                'size_mb' => round(strlen($body) / 1024 / 1024, 2)
                            ]);
                            unset($response, $body);
                            continue; // 尝试下一个API端点
                        }
                        $data = $response->json();
                        unset($response, $body); // 立即释放响应对象
                        
                        // 根据不同的API返回格式解析余额
                        if ($endpoint['type'] === 'xrpscan') {
                            // XRPScan API返回的余额在initial_balance字段
                            if (isset($data['initial_balance'])) {
                                $xrpBalance = number_format((float)$data['initial_balance'], 2, '.', '');
                            } elseif (isset($data['balance'])) {
                                // 有些情况下可能在balance字段
                                $xrpBalance = number_format((float)$data['balance'], 2, '.', '');
                            } else {
                                $xrpBalance = '0.00';
                            }
                        } else {
                            // 其他API可能直接返回balance字段
                            if (isset($data[$endpoint['balance_field']])) {
                                $balanceValue = $data[$endpoint['balance_field']];
                                // 如果是字符串格式（如 "1000000" drops），需要转换为XRP
                                if (is_string($balanceValue) && strlen($balanceValue) > 6) {
                                    // 假设是drops格式，需要除以1000000
                                    $xrpBalance = number_format((float)$balanceValue / 1000000, 2, '.', '');
                                } else {
                                    $xrpBalance = number_format((float)$balanceValue, 2, '.', '');
                                }
                            } elseif (isset($data['balance'])) {
                                $balanceValue = $data['balance'];
                                if (is_string($balanceValue) && strlen($balanceValue) > 6) {
                                    $xrpBalance = number_format((float)$balanceValue / 1000000, 2, '.', '');
                                } else {
                                    $xrpBalance = number_format((float)$balanceValue, 2, '.', '');
                                }
                            } else {
                                $xrpBalance = '0.00';
                            }
                        }
                        
                        unset($data);
                        break; // 成功获取余额，跳出循环
                    } else {
                        Log::debug('XRP API响应异常', [
                            'address' => $address,
                            'api_url' => $endpoint['url'],
                            'status' => $response->status(),
                            'body' => substr((string) $response->body(), 0, 200),
                        ]);
                        unset($response);
                    }
                } catch (\Exception $e) {
                    // 记录错误但继续尝试下一个节点
                    Log::debug('XRP API节点连接失败，尝试下一个', [
                        'api_url' => $endpoint['url'],
                        'address' => $address,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                    }
                }
            }
            
            // 如果所有API都失败，返回null
            if ($xrpBalance === null) {
                return null;
            }
            
            // 格式化返回字符串
            return $xrpBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询XRP余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询TON钱包余额
     *
     * @param string $address TON钱包地址
     * @return string|null 余额（字符串格式），查询失败返回null
     */
    public static function queryTonBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 方案1：使用TON Center API
            $tonBalance = null;
            $toncenterResponse = Http::timeout($timeout)
                ->withOptions([
                    'stream' => false,
                    'http_errors' => false,
                ])
                ->get("https://toncenter.com/api/v2/address", [
                    'address' => $address
                ]);
            
            if ($toncenterResponse->successful()) {
                // 先检查响应体大小
                $body = $toncenterResponse->body();
                if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                    Log::warning('TON余额查询响应体过大', [
                        'address' => $address,
                        'size' => strlen($body)
                    ]);
                    unset($toncenterResponse, $body);
                } else {
                    $toncenterData = $toncenterResponse->json();
                    unset($toncenterResponse, $body); // 立即释放响应对象
                    if (isset($toncenterData['result']['balance'])) {
                        // TON余额单位是nanoTON，1 TON = 1,000,000,000 nanoTON
                        $balanceNano = $toncenterData['result']['balance'];
                        $tonBalance = bcdiv((string)$balanceNano, '1000000000', 6);
                        unset($balanceNano);
                    }
                    unset($toncenterData);
                }
            } else {
                unset($toncenterResponse);
            }
            
            // 如果方案1失败，尝试方案2：使用TON API
            if ($tonBalance === null) {
                $tonapiResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("https://tonapi.io/v2/accounts/{$address}");
                
                if ($tonapiResponse->successful()) {
                    // 先检查响应体大小
                    $body = $tonapiResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('TON API响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($tonapiResponse, $body);
                    } else {
                        $tonapiData = $tonapiResponse->json();
                        unset($tonapiResponse, $body); // 立即释放响应对象
                        if (isset($tonapiData['balance'])) {
                            // TON API返回的余额单位是nanoTON
                            $balanceNano = $tonapiData['balance'];
                            $tonBalance = bcdiv((string)$balanceNano, '1000000000', 6);
                            unset($balanceNano);
                        }
                        unset($tonapiData);
                    }
                } else {
                    unset($tonapiResponse);
                }
            }
            
            // 如果方案2也失败，尝试方案3：使用TON Center的getAddressInformation方法
            if ($tonBalance === null) {
                $toncenterInfoResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("https://toncenter.com/api/v2/getAddressInformation", [
                        'address' => $address
                    ]);
                
                if ($toncenterInfoResponse->successful()) {
                    // 先检查响应体大小
                    $body = $toncenterInfoResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('TON地址信息查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($toncenterInfoResponse, $body);
                    } else {
                        $toncenterInfoData = $toncenterInfoResponse->json();
                        unset($toncenterInfoResponse, $body); // 立即释放响应对象
                        if (isset($toncenterInfoData['result']['balance'])) {
                            $balanceNano = $toncenterInfoData['result']['balance'];
                            $tonBalance = bcdiv((string)$balanceNano, '1000000000', 6);
                            unset($balanceNano);
                        }
                        unset($toncenterInfoData);
                    }
                } else {
                    unset($toncenterInfoResponse);
                }
            }
            
            // 如果所有方案都失败，返回null
            if ($tonBalance === null) {
                return null;
            }
            
            // 格式化返回字符串
            return $tonBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询TON余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询SOL钱包余额
     *
     * @param string $address SOL钱包地址
     * @return string|null 余额（字符串格式），查询失败返回null
     */
    public static function querySolBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 方案1：使用Solana官方公共RPC节点
            $solBalance = null;
            $rpcUrl = 'https://api.mainnet-beta.solana.com';
            
            $rpcResponse = Http::timeout($timeout)
                ->withOptions([
                    'stream' => false,
                    'http_errors' => false,
                ])
                ->post($rpcUrl, [
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'getBalance',
                    'params' => [$address]
                ]);
            
            if ($rpcResponse->successful()) {
                // 先检查响应体大小
                $body = $rpcResponse->body();
                if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                    Log::warning('SOL余额查询响应体过大', [
                        'address' => $address,
                        'size' => strlen($body)
                    ]);
                    unset($rpcResponse, $body);
                } else {
                    $rpcData = $rpcResponse->json();
                    unset($rpcResponse, $body); // 立即释放响应对象
                    if (isset($rpcData['result']['value'])) {
                        // SOL余额单位是lamports，1 SOL = 1,000,000,000 lamports
                        $balanceLamports = $rpcData['result']['value'];
                        $solBalance = bcdiv((string)$balanceLamports, '1000000000', 6);
                        unset($balanceLamports);
                    }
                    unset($rpcData);
                }
            } else {
                unset($rpcResponse);
            }
            
            // 如果方案1失败，尝试方案2：使用其他公共RPC节点
            if ($solBalance === null) {
                $rpcUrl2 = 'https://solana-api.projectserum.com';
                $rpcResponse2 = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->post($rpcUrl2, [
                        'jsonrpc' => '2.0',
                        'id' => 1,
                        'method' => 'getBalance',
                        'params' => [$address]
                    ]);
                
                if ($rpcResponse2->successful()) {
                    // 先检查响应体大小
                    $body = $rpcResponse2->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('SOL RPC响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($rpcResponse2, $body);
                    } else {
                        $rpcData2 = $rpcResponse2->json();
                        unset($rpcResponse2, $body); // 立即释放响应对象
                        if (isset($rpcData2['result']['value'])) {
                            $balanceLamports = $rpcData2['result']['value'];
                            $solBalance = bcdiv((string)$balanceLamports, '1000000000', 6);
                            unset($balanceLamports);
                        }
                        unset($rpcData2);
                    }
                } else {
                    unset($rpcResponse2);
                }
            }
            
            // 如果方案2也失败，尝试方案3：使用Solscan API
            if ($solBalance === null) {
                $solscanResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("https://public-api.solscan.io/account/{$address}");
                
                if ($solscanResponse->successful()) {
                    // 先检查响应体大小
                    $body = $solscanResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('SOLscan API响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($solscanResponse, $body);
                    } else {
                        $solscanData = $solscanResponse->json();
                        unset($solscanResponse, $body); // 立即释放响应对象
                        if (isset($solscanData['lamports'])) {
                            $balanceLamports = $solscanData['lamports'];
                            $solBalance = bcdiv((string)$balanceLamports, '1000000000', 6);
                            unset($balanceLamports);
                        }
                        unset($solscanData);
                    }
                } else {
                    unset($solscanResponse);
                }
            }
            
            // 如果所有方案都失败，返回null
            if ($solBalance === null) {
                return null;
            }
            
            // 格式化返回字符串
            return $solBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询SOL余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询ERC20 USDT余额
     *
     * @param string $address ETH钱包地址
     * @return string|null 余额（decimal格式字符串），查询失败返回null
     */
    public static function queryEthUsdtBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // ERC20 USDT合约地址
            $usdtContractAddress = '0xdAC17F958D2ee523a2206206994597C13D831ec7';
            
            // Etherscan API基础URL
            $etherscanApiKey = env('ETHERSCAN_API_KEY', '');
            $baseUrl = 'https://api.etherscan.io';
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 查询ERC20 USDT余额
            $usdtBalance = null;
            
            if (!empty($etherscanApiKey)) {
                // 使用Etherscan API查询USDT余额
                $usdtResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("{$baseUrl}/api", [
                        'module' => 'account',
                        'action' => 'tokenbalance',
                        'contractaddress' => $usdtContractAddress,
                        'address' => $address,
                        'tag' => 'latest',
                        'apikey' => $etherscanApiKey
                    ]);
                
                if ($usdtResponse->successful()) {
                    // 先检查响应体大小
                    $body = $usdtResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('ERC20USDT余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($usdtResponse, $body);
                    } else {
                        $usdtData = $usdtResponse->json();
                        unset($usdtResponse, $body);
                        if (isset($usdtData['status']) && $usdtData['status'] == '1' && isset($usdtData['result'])) {
                            // USDT精度是6位小数
                            $balanceRaw = $usdtData['result'];
                            $usdtBalance = bcdiv((string)$balanceRaw, '1000000', 8);
                            unset($balanceRaw);
                        }
                        unset($usdtData);
                    }
                } else {
                    unset($usdtResponse);
                }
            } else {
                // 如果没有API Key，使用RPC调用USDT合约的balanceOf方法
                $functionSignature = '0x70a08231'; // balanceOf(address)
                $paddedAddress = '0x' . str_pad(substr($address, 2), 64, '0', STR_PAD_LEFT);
                $data = $functionSignature . substr($paddedAddress, 2);
                
                // 使用多个备用RPC节点，提高可靠性
                $rpcUrls = [
                    'https://eth.llamarpc.com',
                    'https://rpc.ankr.com/eth',
                    'https://ethereum.publicnode.com',
                    'https://eth.merkle.io',
                ];
                
                foreach ($rpcUrls as $rpcUrl) {
                    try {
                        $rpcResponse = Http::timeout($timeout)
                            ->retry(2, 100) // 重试2次，每次间隔100ms
                            ->withOptions([
                                'stream' => false,
                                'http_errors' => false,
                            ])
                            ->post($rpcUrl, [
                                'jsonrpc' => '2.0',
                                'method' => 'eth_call',
                                'params' => [
                                    [
                                        'to' => $usdtContractAddress,
                                        'data' => $data
                                    ],
                                    'latest'
                                ],
                                'id' => 1
                            ]);
                        
                        if ($rpcResponse->successful()) {
                            // 先检查响应体大小
                            $body = $rpcResponse->body();
                            if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                                Log::warning('ERC20USDT RPC响应体过大', [
                                    'address' => $address,
                                    'rpc_url' => $rpcUrl,
                                    'size' => strlen($body)
                                ]);
                                unset($rpcResponse, $body);
                                continue;
                            }
                            $rpcData = $rpcResponse->json();
                            unset($rpcResponse, $body);
                            if (isset($rpcData['result']) && !isset($rpcData['error']) && $rpcData['result'] !== '0x') {
                                $balanceRaw = self::hexToDec($rpcData['result']);
                                $usdtBalance = bcdiv($balanceRaw, '1000000', 8);
                                unset($balanceRaw);
                                unset($rpcData);
                                break; // 成功获取余额，跳出循环
                            }
                            unset($rpcData);
                        } else {
                            unset($rpcResponse);
                        }
                    } catch (\Exception $e) {
                        // 记录错误但继续尝试下一个节点
                        Log::debug('ERC20USDT RPC节点连接失败，尝试下一个', [
                            'rpc_url' => $rpcUrl,
                            'address' => $address,
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            }
            
            return $usdtBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询ERC20 USDT余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询BSC20 USDT余额
     *
     * @param string $address BSC钱包地址
     * @return string|null 余额（decimal格式字符串），查询失败返回null
     */
    public static function queryBscUsdtBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // BEP20 USDT合约地址（BSC主网）
            $usdtContractAddress = '0x55d398326f99059fF775485246999027B3197955';
            
            // BscScan API基础URL
            $bscscanApiKey = env('BSCSCAN_API_KEY', '');
            $baseUrl = 'https://api.bscscan.com';
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 查询BEP20 USDT余额
            $usdtBalance = null;
            
            if (!empty($bscscanApiKey)) {
                // 使用BscScan API查询USDT余额
                $usdtResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->get("{$baseUrl}/api", [
                        'module' => 'account',
                        'action' => 'tokenbalance',
                        'contractaddress' => $usdtContractAddress,
                        'address' => $address,
                        'tag' => 'latest',
                        'apikey' => $bscscanApiKey
                    ]);
                
                if ($usdtResponse->successful()) {
                    // 先检查响应体大小
                    $body = $usdtResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('BEP20USDT余额查询响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($usdtResponse, $body);
                    } else {
                        $usdtData = $usdtResponse->json();
                        unset($usdtResponse, $body);
                        if (isset($usdtData['status']) && $usdtData['status'] == '1' && isset($usdtData['result'])) {
                            // BSC上的USDT精度是18位小数（不是6位）
                            $balanceRaw = $usdtData['result'];
                            $usdtBalance = bcdiv((string)$balanceRaw, '1000000000000000000', 8);
                            unset($balanceRaw);
                        }
                        unset($usdtData);
                    }
                } else {
                    unset($usdtResponse);
                }
            } else {
                // 如果没有API Key，使用RPC调用USDT合约的balanceOf方法
                $functionSignature = '0x70a08231'; // balanceOf(address)
                $paddedAddress = '0x' . str_pad(substr($address, 2), 64, '0', STR_PAD_LEFT);
                $data = $functionSignature . substr($paddedAddress, 2);
                
                $rpcUrl = 'https://bsc-dataseed.binance.org/';
                $rpcResponse = Http::timeout($timeout)
                    ->withOptions([
                        'stream' => false,
                        'http_errors' => false,
                    ])
                    ->post($rpcUrl, [
                        'jsonrpc' => '2.0',
                        'method' => 'eth_call',
                        'params' => [
                            [
                                'to' => $usdtContractAddress,
                                'data' => $data
                            ],
                            'latest'
                        ],
                        'id' => 1
                    ]);
                
                if ($rpcResponse->successful()) {
                    // 先检查响应体大小
                    $body = $rpcResponse->body();
                    if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                        Log::warning('BEP20USDT RPC响应体过大', [
                            'address' => $address,
                            'size' => strlen($body)
                        ]);
                        unset($rpcResponse, $body);
                    } else {
                        $rpcData = $rpcResponse->json();
                        unset($rpcResponse, $body);
                        if (isset($rpcData['result']) && $rpcData['result'] !== '0x') {
                            // BSC上的USDT精度是18位小数（不是6位）
                            $balanceRaw = self::hexToDec($rpcData['result']);
                            $usdtBalance = bcdiv($balanceRaw, '1000000000000000000', 8);
                            unset($balanceRaw);
                        }
                        unset($rpcData);
                    }
                } else {
                    unset($rpcResponse);
                }
            }
            
            return $usdtBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询BSC20 USDT余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 查询TRON USDT余额
     *
     * @param string $address TRON钱包地址
     * @return string|null 余额（decimal格式字符串），查询失败返回null
     */
    public static function queryTronUsdtBalance(string $address): ?string
    {
        try {
            // 初始化内存设置
            self::initMemorySettings();
            
            // USDT合约地址（TRC20）
            $usdtContractAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
            
            // TronGrid API基础URL
            $baseUrl = 'https://api.trongrid.io';
            
            // 设置请求超时时间（10秒）
            $timeout = 10;
            
            // 查询USDT余额
            $usdtBalance = null;
            
            // 方法1：使用/v1/accounts/{address} API查询账户信息
            $accountResponse = Http::timeout($timeout)
                ->withOptions([
                    'stream' => false,
                    'http_errors' => false,
                ])
                ->get("{$baseUrl}/v1/accounts/{$address}");
            
            if ($accountResponse->successful()) {
                // 先检查响应体大小（TRON账户信息可能包含大量TRC20代币数据）
                $body = $accountResponse->body();
                if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                    Log::warning('TRON USDT账户查询响应体过大', [
                        'address' => $address,
                        'size' => strlen($body)
                    ]);
                    unset($accountResponse, $body);
                } else {
                    $accountData = $accountResponse->json();
                    unset($accountResponse, $body);
                
                    if (isset($accountData['data']) && is_array($accountData['data']) && count($accountData['data']) > 0) {
                        $account = $accountData['data'][0];
                        
                        // 检查 trc20token_balances 字段
                        if (isset($account['trc20token_balances']) && is_array($account['trc20token_balances'])) {
                            foreach ($account['trc20token_balances'] as $tokenBalance) {
                                if (is_array($tokenBalance)) {
                                    $contractAddr = $tokenBalance['token_address'] ?? $tokenBalance['contract_address'] ?? null;
                                    
                                    if ($contractAddr && strtoupper($contractAddr) === strtoupper($usdtContractAddress)) {
                                        $balance = $tokenBalance['balance'] ?? $tokenBalance['balance_str'] ?? 0;
                                        
                                        if (!empty($balance) && $balance !== '0') {
                                            $balanceRaw = (string)$balance;
                                            // USDT精度是6位小数
                                            $usdtBalance = bcdiv($balanceRaw, '1000000', 8);
                                            unset($balanceRaw);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        
                        // 检查旧的 trc20 字段格式
                        if ($usdtBalance === null && isset($account['trc20']) && is_array($account['trc20'])) {
                            foreach ($account['trc20'] as $trc20) {
                                if (is_array($trc20) && isset($trc20['contract_address'])) {
                                    $contractAddr = $trc20['contract_address'];
                                    $balance = $trc20['balance'] ?? $trc20['balance_str'] ?? 0;
                                    
                                    if (strtoupper($contractAddr) === strtoupper($usdtContractAddress)) {
                                        $balanceRaw = (string)$balance;
                                        if ($balanceRaw !== '0') {
                                            $usdtBalance = bcdiv($balanceRaw, '1000000', 8);
                                            unset($balanceRaw);
                                            break;
                                        }
                                    }
                                } elseif (is_array($trc20)) {
                                    foreach ($trc20 as $contractAddr => $balance) {
                                        if (is_string($contractAddr) && strtoupper($contractAddr) === strtoupper($usdtContractAddress)) {
                                            $balanceRaw = (string)$balance;
                                            if ($balanceRaw !== '0') {
                                                $usdtBalance = bcdiv($balanceRaw, '1000000', 8);
                                                unset($balanceRaw);
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        unset($account);
                    }
                    unset($accountData);
                }
            } else {
                unset($accountResponse);
            }
            
            // 方法2：使用TronScan API查询
            if ($usdtBalance === null) {
                try {
                    $tronscanResponse = Http::timeout($timeout)
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->withOptions([
                            'stream' => false,
                            'http_errors' => false,
                        ])
                        ->get("https://apilist.tronscanapi.com/api/account", [
                            'address' => $address
                        ]);
                    
                    if ($tronscanResponse->successful()) {
                        // 先检查响应体大小
                        $body = $tronscanResponse->body();
                        if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                            Log::warning('TRON USDT TronScan API响应体过大', [
                                'address' => $address,
                                'size' => strlen($body)
                            ]);
                            unset($tronscanResponse, $body);
                        } else {
                            $tronscanData = $tronscanResponse->json();
                            unset($tronscanResponse, $body);
                        
                            if (isset($tronscanData['trc20token_balances']) && is_array($tronscanData['trc20token_balances'])) {
                                foreach ($tronscanData['trc20token_balances'] as $token) {
                                    $tokenAddr = $token['tokenId'] ?? $token['contract_address'] ?? null;
                                    if ($tokenAddr && strtoupper($tokenAddr) === strtoupper($usdtContractAddress)) {
                                        $balanceRaw = $token['balance'] ?? $token['amount'] ?? 0;
                                        
                                        if (!empty($balanceRaw) && $balanceRaw !== '0') {
                                            $usdtBalance = bcdiv((string)$balanceRaw, '1000000', 8);
                                            break;
                                        }
                                    }
                                }
                            }
                            unset($tronscanData);
                        }
                    } else {
                        unset($tronscanResponse);
                    }
                } catch (\Exception $e) {
                    Log::debug('TRON USDT TronScan API异常', ['error' => $e->getMessage()]);
                }
            }
            
            // 方法3：使用RPC方法查询USDT余额
            if ($usdtBalance === null) {
                try {
                    $parameter = self::encodeTronAddressForRPC($address);
                    
                    $rpcParams = [
                        'owner_address' => $address,
                        'contract_address' => $usdtContractAddress,
                        'function_selector' => 'balanceOf(address)',
                        'visible' => true
                    ];
                    
                    if (!empty($parameter)) {
                        $rpcParams['parameter'] = $parameter;
                    }
                    
                    $rpcResponse = Http::timeout($timeout)
                        ->withOptions([
                            'stream' => false,
                            'http_errors' => false,
                        ])
                        ->post("{$baseUrl}/wallet/triggerconstantcontract", $rpcParams);
                    
                    if ($rpcResponse->successful()) {
                        // 先检查响应体大小
                        $body = $rpcResponse->body();
                        if (strlen($body) > self::MAX_RESPONSE_SIZE) {
                            Log::warning('TRON USDT RPC响应体过大', [
                                'address' => $address,
                                'size' => strlen($body)
                            ]);
                            unset($rpcResponse, $body);
                        } else {
                            $rpcData = $rpcResponse->json();
                            unset($rpcResponse, $body);
                        
                            if (isset($rpcData['constant_result']) && is_array($rpcData['constant_result']) && count($rpcData['constant_result']) > 0) {
                                $resultHex = $rpcData['constant_result'][0];
                                if ($resultHex && $resultHex !== '0x' && $resultHex !== '') {
                                    $resultHex = str_replace('0x', '', $resultHex);
                                    $balanceRaw = self::hexToDec($resultHex);
                                    
                                    if ($balanceRaw !== '0') {
                                        // USDT精度是6位小数
                                        $usdtBalance = bcdiv($balanceRaw, '1000000', 8);
                                    }
                                    unset($balanceRaw);
                                }
                            }
                            unset($rpcData);
                        }
                    } else {
                        unset($rpcResponse);
                    }
                } catch (\Exception $e) {
                    Log::warning('TRON USDT RPC调用异常', [
                        'address' => $address,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return $usdtBalance;
            
        } catch (\Exception $e) {
            Log::warning('查询TRON USDT余额失败', [
                'address' => $address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * 安全地将十六进制字符串转换为十进制字符串
     * 避免hexdec()对大数的问题
     *
     * @param string $hex 十六进制字符串（如 "0x1234"）
     * @return string 十进制字符串
     */
    private static function hexToDec(string $hex): string
    {
        // 移除0x前缀
        $hex = str_replace('0x', '', $hex);
        
        // 如果为空，返回0
        if (empty($hex)) {
            return '0';
        }
        
        // 如果PHP有GMP扩展，使用GMP（更可靠，支持任意大数）
        if (extension_loaded('gmp') && function_exists('gmp_init') && function_exists('gmp_strval')) {
            /** @phpstan-ignore-next-line */
            return gmp_strval(gmp_init($hex, 16), 10);
        }
        
        // 否则使用base_convert（对于大数可能不够精确，但通常够用）
        // base_convert有精度限制，但对于我们的用例应该足够
        $result = base_convert($hex, 16, 10);
        
        // 确保返回字符串格式
        return (string)$result;
    }
    
    /**
     * 编码Tron地址用于RPC调用
     * balanceOf(address) 需要将地址编码为64位hex字符串
     * 
     * @param string $address Tron Base58地址
     * @return string 编码后的参数（64位hex字符串），如果编码失败返回空字符串
     */
    private static function encodeTronAddressForRPC(string $address): string
    {
        // Tron地址Base58解码比较复杂，需要专门的库
        // 这里我们尝试使用TronGrid API来获取地址的hex表示
        // 如果失败，返回空字符串，让API使用owner_address自动处理
        
        try {
            // 尝试通过TronGrid API获取地址的hex表示
            $baseUrl = 'https://api.trongrid.io';
            $timeout = 5;
            
            $response = Http::timeout($timeout)
                ->get("{$baseUrl}/v1/accounts/{$address}");
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'][0]['address'])) {
                    // 如果API返回了hex地址，使用它
                    $hexAddress = $data['data'][0]['address'];
                    // 移除0x前缀，补齐到64个字符
                    $hexAddress = str_replace('0x', '', $hexAddress);
                    return str_pad($hexAddress, 64, '0', STR_PAD_LEFT);
                }
            }
        } catch (\Exception $e) {
            // 如果API调用失败，返回空字符串
            // TronGrid API在visible=true时可能会自动处理
        }
        
        // 如果无法获取hex地址，返回空字符串
        // 某些情况下，TronGrid API可能会自动处理
        return '';
    }
}

