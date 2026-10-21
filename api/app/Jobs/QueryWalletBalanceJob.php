<?php

namespace App\Jobs;

use App\Models\ReceiveWordKey;
use App\Service\CurrencyRateLimitService;
use App\Service\WalletBalanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QueryWalletBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务重试次数
     *
     * @var int
     */
    public $tries = 10;

    /**
     * 任务超时时间（秒）
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * 延迟重试时间（秒）
     *
     * @var array
     */
    public $backoff = [5, 10, 30, 60, 120];

    /**
     * 钱包密钥记录ID
     *
     * @var int
     */
    public $receiveWordKeyId;

    /**
     * 币种类型
     *
     * @var string
     */
    public $currencyType;

    /**
     * 钱包地址
     *
     * @var string
     */
    public $address;

    /**
     * 币种类型到查询方法的映射
     *
     * @var array
     */
    private static $currencyMethodMap = [
        'BTC' => 'queryBtcBalance',
        'ETH' => 'queryEthBalance',
        'BSC' => 'queryBscBalance',
        'TRON' => 'queryTronBalance',
        'SOL' => 'querySolBalance',
        'TON' => 'queryTonBalance',
        'XRP' => 'queryXrpBalance',
        'ERC20USDT' => 'queryEthUsdtBalance',
        'BEP20USDT' => 'queryBscUsdtBalance',
        'TRC20USDT' => 'queryTronUsdtBalance',
    ];

    /**
     * 创建一个新的任务实例
     *
     * @param int $receiveWordKeyId 钱包密钥记录ID
     * @param string $currencyType 币种类型
     * @param string $address 钱包地址
     */
    public function __construct(int $receiveWordKeyId, string $currencyType, string $address)
    {
        $this->receiveWordKeyId = $receiveWordKeyId;
        $this->currencyType = $currencyType;
        $this->address = $address;
    }

    /**
     * 执行任务
     *
     * @return void
     */
    public function handle()
    {
        try {
            // 增加内存限制到512MB，防止处理大型响应时内存耗尽
            $currentLimit = ini_get('memory_limit');
            $currentBytes = $this->parseMemoryLimit($currentLimit);
            $minBytes = 4 * 1024 * 1024 * 1024; // 512MB
            
            if ($currentBytes < $minBytes) {
                ini_set('memory_limit', '4g');
            }
            
            // 检查限流：如果达到限流，延迟释放任务
            if (!CurrencyRateLimitService::canExecute($this->currencyType)) {
                $waitSeconds = CurrencyRateLimitService::getWaitSeconds($this->currencyType);
                $waitSeconds = max(5, $waitSeconds);
                $waitSeconds += random_int(0, 2);
                
                Log::debug('币种API限流，延迟执行', [
                    'currency_type' => $this->currencyType,
                    'wait_seconds' => $waitSeconds,
                    'receive_word_key_id' => $this->receiveWordKeyId,
                ]);
                
                // 重新分发新任务，避免因限流反复占用同一任务的重试次数
                self::dispatch($this->receiveWordKeyId, $this->currencyType, $this->address)
                    ->onQueue($this->queue ?? 'wallet-balance-query')
                    ->delay(now()->addSeconds($waitSeconds));

                $this->delete();
                return;
            }
            
            // 获取对应的查询方法
            $method = self::getQueryMethod($this->currencyType);
            
            if (!$method) {
                Log::warning('查询钱包余额失败：不支持的币种类型', [
                    'receive_word_key_id' => $this->receiveWordKeyId,
                    'currency_type' => $this->currencyType,
                    'address' => $this->address,
                ]);
                return;
            }

            // 记录请求（增加限流计数器）
            CurrencyRateLimitService::recordRequest($this->currencyType);
            
            // 调用对应的查询方法
            $balance = WalletBalanceService::$method($this->address);

            if ($balance === null) {
                Log::debug('钱包余额查询失败：返回空', [
                    'receive_word_key_id' => $this->receiveWordKeyId,
                    'currency_type' => $this->currencyType,
                    'address' => $this->address,
                ]);
                return;
            }

            // 更新数据库
            $receiveWordKey = ReceiveWordKey::find($this->receiveWordKeyId);
            
            if (!$receiveWordKey) {
                Log::warning('查询钱包余额失败：记录不存在', [
                    'receive_word_key_id' => $this->receiveWordKeyId,
                    'currency_type' => $this->currencyType,
                    'address' => $this->address,
                ]);
                return;
            }

            // 验证记录的币种类型是否匹配，防止更新错误的记录
            if ($receiveWordKey->currency_type !== $this->currencyType) {
                Log::error('查询钱包余额失败：币种类型不匹配', [
                    'receive_word_key_id' => $this->receiveWordKeyId,
                    'expected_currency_type' => $this->currencyType,
                    'actual_currency_type' => $receiveWordKey->currency_type,
                    'address' => $this->address,
                ]);
                return;
            }

            // 验证地址是否匹配，防止更新错误的记录
            if ($receiveWordKey->address !== $this->address) {
                Log::error('查询钱包余额失败：地址不匹配', [
                    'receive_word_key_id' => $this->receiveWordKeyId,
                    'currency_type' => $this->currencyType,
                    'expected_address' => $this->address,
                    'actual_address' => $receiveWordKey->address,
                ]);
                return;
            }

            // 只有查询成功时才更新余额
            $receiveWordKey->balance = $balance;
            $receiveWordKey->balance_updated_at = now();
            $receiveWordKey->save();

            // 记录日志（可选，避免日志过多）
            Log::debug('钱包余额查询成功', [
                'receive_word_key_id' => $this->receiveWordKeyId,
                'currency_type' => $this->currencyType,
                'address' => $this->address,
                'balance' => $balance,
            ]);

        } catch (\Exception $e) {
            Log::error('查询钱包余额任务执行失败', [
                'receive_word_key_id' => $this->receiveWordKeyId,
                'currency_type' => $this->currencyType,
                'address' => $this->address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // 重新抛出异常，触发重试机制
            throw $e;
        }
    }

    /**
     * 任务失败后的处理
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('查询钱包余额任务最终失败', [
            'receive_word_key_id' => $this->receiveWordKeyId,
            'currency_type' => $this->currencyType,
            'address' => $this->address,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * 根据币种类型获取查询方法名
     *
     * @param string $currencyType
     * @return string|null
     */
    private static function getQueryMethod(string $currencyType): ?string
    {
        return self::$currencyMethodMap[$currencyType] ?? null;
    }
    
    /**
     * 解析内存限制字符串为字节数
     *
     * @param string $limit
     * @return int
     */
    private function parseMemoryLimit(string $limit): int
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
}

