<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/*
使用方法
1. 预览模式（推荐先运行）
php artisan receive-apps:clean-duplicates --dry-run
2. 执行删除
php artisan receive-apps:clean-duplicates
3. 自定义判断字段
# 例如：基于 device_number 和 app_package 判断重复
php artisan receive-apps:clean-duplicates --field=device_number,app_package
*/

class CleanDuplicateReceiveApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receive-apps:clean-duplicates 
                            {--dry-run : 仅显示将要删除的记录，不实际删除}
                            {--field=device_id,app_package : 用于判断重复的字段，多个字段用逗号分隔}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除 receive_apps 表中的重复数据';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $fields = explode(',', $this->option('field'));
        $fields = array_map('trim', $fields);

        $this->info('开始检查重复数据...');
        $this->line('判断重复的字段: ' . implode(', ', $fields));

        // 构建用于分组的字段列表
        $groupFields = implode(', ', $fields);
        
        // 查找重复数据
        $duplicates = DB::table('receive_apps')
            ->select($fields)
            ->selectRaw('COUNT(*) as count, GROUP_CONCAT(id ORDER BY id) as ids')
            ->groupBy($fields)
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✓ 未发现重复数据');
            return 0;
        }

        $this->warn("发现 {$duplicates->count()} 组重复数据");

        $totalToDelete = 0;
        $idsToDelete = [];

        foreach ($duplicates as $duplicate) {
            $ids = explode(',', $duplicate->ids);
            // 保留ID最小的记录，删除其他
            $toDelete = array_slice($ids, 1);
            $idsToDelete = array_merge($idsToDelete, $toDelete);
            $totalToDelete += count($toDelete);

            // 显示详细信息
            $this->line('');
            $this->line("重复组: " . $this->formatDuplicateInfo($duplicate, $fields));
            $this->line("  保留ID: {$ids[0]}");
            $this->line("  删除ID: " . implode(', ', $toDelete));
        }

        $this->line('');
        $this->warn("总计将删除 {$totalToDelete} 条重复记录");

        if ($dryRun) {
            $this->info('✓ 这是预览模式，未实际删除数据');
            $this->info('  运行命令时不加 --dry-run 参数将执行实际删除');
            return 0;
        }

        // 确认删除
        if (!$this->confirm('确定要删除这些重复数据吗？', true)) {
            $this->info('操作已取消');
            return 0;
        }

        // 执行删除
        $this->info('正在删除重复数据...');
        
        try {
            DB::beginTransaction();
            
            $deleted = DB::table('receive_apps')
                ->whereIn('id', $idsToDelete)
                ->delete();

            DB::commit();

            $this->info("✓ 成功删除 {$deleted} 条重复记录");
            Log::info("清理 receive_apps 重复数据", [
                'deleted_count' => $deleted,
                'fields' => $fields
            ]);

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('删除失败: ' . $e->getMessage());
            Log::error("清理 receive_apps 重复数据失败", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * 格式化重复信息显示
     *
     * @param object $duplicate
     * @param array $fields
     * @return string
     */
    private function formatDuplicateInfo($duplicate, $fields)
    {
        $info = [];
        foreach ($fields as $field) {
            $value = $duplicate->$field ?? 'NULL';
            $info[] = "{$field}={$value}";
        }
        return implode(', ', $info) . " (共 {$duplicate->count} 条)";
    }
}

