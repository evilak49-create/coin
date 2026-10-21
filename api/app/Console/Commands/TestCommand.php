<?php

namespace App\Console\Commands;

use App\Enums\AnnouncementStatus;
use App\Jobs\MediaGroupJob;
use App\Jobs\PublicPlatformJob;
use App\Models\AdminUser;
use App\Models\ProjectAnnouncementDetail;
use App\Models\ProjectAnnouncement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        $this->line('测试');
    }
}
