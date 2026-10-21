<?php

namespace App\Console\Commands;

use App\Jobs\QueryWalletBalanceJob;
use App\Models\ReceiveWordKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class QueryWalletBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:query-balances 
                            {--sync : 同步执行（不使用队列）}
                            {--limit= : 限制处理数量}
                            {--stale-minutes=30 : 只处理余额更新时间早于该分钟数的记录（0表示不过滤）}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '查询receive_word_keys表中所有钱包地址的账户余额（队列模式）';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 移除PHP执行时间限制，避免超时
        set_time_limit(0);
        
        $this->info("开始分发钱包余额查询任务...");
        
        // 构建查询
        $query = ReceiveWordKey::query()
            ->whereNotNull('address')
            ->where('address', '!=', '');
        
        // 如果设置了stale-minutes选项，过滤近期已更新的记录
        $staleMinutes = (int) $this->option('stale-minutes');
        if ($staleMinutes > 0) {
            $cutoff = now()->subMinutes($staleMinutes);
            $query->where(function ($sub) use ($cutoff) {
                $sub->whereNull('balance_updated_at')
                    ->orWhere('balance_updated_at', '<', $cutoff);
            });
            $this->info("仅处理余额更新时间早于 {$staleMinutes} 分钟的记录");
        }
        
        // 统计总记录数
        $totalCount = $query->count();
        
        if ($totalCount === 0) {
            $this->info("没有需要查询余额的记录。");
            return Command::SUCCESS;
        }
        
        // 如果设置了limit选项，限制处理数量
        if ($this->option('limit')) {
            $limit = (int) $this->option('limit');
            if ($limit > 0) {
                $query->limit($limit);
                $totalCount = min($totalCount, $limit);
                $this->info("限制处理数量：{$limit} 条");
            }
        }
        
        $this->info("找到 {$totalCount} 条需要查询余额的记录。");
        
        // 如果使用同步模式
        if ($this->option('sync')) {
            $this->warn("使用同步模式执行（不使用队列）");
            return $this->handleSync($query, $totalCount);
        }
        
        // 队列模式：分发任务到队列
        return $this->handleQueue($query, $totalCount);
    }

    /**
     * 队列模式：分发任务到队列
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $totalCount
     * @return int
     */
    private function handleQueue($query, int $totalCount)
    {
        $dispatchedCount = 0;
        $skipCount = 0;
        $failCount = 0;
        
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();
        
        // 使用chunk分批处理，避免内存占用过大
        $batchSize = 100;
        $index = 0; // 用于计算延迟时间
        
        $query->orderByDesc('id')
            ->chunk($batchSize, function ($records) use (&$dispatchedCount, &$skipCount, &$failCount, &$index, $bar) {
                foreach ($records as $record) {
                    try {
                        // 检查币种类型是否支持
                        if (!in_array($record->currency_type, ReceiveWordKey::BASE_CURRENCY_TYPES)) {
                            $skipCount++;
                            $bar->advance();
                            continue;
                        }
                        
                        // 验证地址不为空
                        if (empty($record->address)) {
                            $skipCount++;
                            $bar->advance();
                            continue;
                        }
                        
                        // 分发任务到队列
                        // 注意：执行时会在QueryWalletBalanceJob中验证币种类型和地址，确保数据一致性
                        // 通过递增延迟平滑分发，避免瞬时并发触发频繁限流
                        $delayMs = $index * 100; // 每个任务间隔100ms
                        QueryWalletBalanceJob::dispatch(
                            $record->id,
                            $record->currency_type,
                            $record->address
                        )
                        ->onQueue('wallet-balance-query')
                        ->delay(now()->addMilliseconds($delayMs));
                        
                        $dispatchedCount++;
                        $index++;
                        
                    } catch (\Exception $e) {
                        $failCount++;
                        Log::error('分发钱包余额查询任务失败', [
                            'receive_word_key_id' => $record->id ?? null,
                            'currency_type' => $record->currency_type ?? null,
                            'address' => $record->address ?? null,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                    
                    // 释放当前记录的内存
                    unset($record);
                    
                    $bar->advance();
                }
                
                // 每批处理完后释放内存并强制垃圾回收
                unset($records);
                gc_collect_cycles();
            });
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("任务分发完成！");
        $this->table(
            ['状态', '数量'],
            [
                ['已分发到队列', $dispatchedCount],
                ['跳过（不支持币种）', $skipCount],
                ['分发失败', $failCount],
                ['总计', $totalCount],
            ]
        );
        
        $this->newLine();
        $this->info("提示：请确保队列worker正在运行：");
        $this->line("  php artisan queue:work --queue=wallet-balance-query");
        
        return Command::SUCCESS;
    }

    /**
     * 同步模式：直接处理（不使用队列）
     * 保留此方法作为备选方案，但不推荐使用
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $totalCount
     * @return int
     */
    private function handleSync($query, int $totalCount)
    {
        $this->error("同步模式暂未实现，请使用队列模式。");
        $this->line("如需同步模式，请移除 --sync 选项使用队列，或联系开发人员实现同步模式。");
        return Command::FAILURE;
    }
}
