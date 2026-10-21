<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password {username=admin} {password=111111}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置管理员密码';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $adminUser = AdminUser::query()->where('username', $username)->first();

        if (empty($adminUser)) {
            $this->error("用户 {$username} 不存在！");
            return Command::FAILURE;
        }

        $adminUser->password = Hash::make($password);
        $adminUser->save();

        $this->info("用户 {$username} 的密码已成功重置为: {$password}");
        return Command::SUCCESS;
    }
}

