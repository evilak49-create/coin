<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
    
        // 每2小时执行一次，查询钱包余额
        $schedule->command('wallet:query-balances --stale-minutes=60')
            ->everySixHours()
            ->withoutOverlapping(240)
            ->appendOutputTo(storage_path('logs/wallet-balance-query.log'))
            ->runInBackground();

        // 每10分钟执行一次，为receive_words表中的助记词生成钱包地址和私钥，存入receive_word_keys表
        $schedule->command('wallet:generate-keys --limit=20')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/wallet-keys-generation.log'))
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
