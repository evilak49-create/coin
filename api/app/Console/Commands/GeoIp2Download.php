<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class GeoIp2Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoip:download 
                            {--license-key= : MaxMind License Key (可选，优先使用环境变量 MAXMIND_LICENSE_KEY)}
                            {--db=all : 要下载的数据库类型 (country/city/asn/all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '下载并更新 GeoIP2 数据库文件';

    /**
     * MaxMind API 基础 URL
     */
    const MAXMIND_BASE_URL = 'https://download.maxmind.com/app/geoip_download';

    /**
     * 数据库类型配置
     */
    const DATABASES = [
        'country' => [
            'edition_id' => 'GeoLite2-Country',
            'filename' => 'GeoLite2-Country.mmdb',
        ],
        'city' => [
            'edition_id' => 'GeoLite2-City',
            'filename' => 'GeoLite2-City.mmdb',
        ],
        'asn' => [
            'edition_id' => 'GeoLite2-ASN',
            'filename' => 'GeoLite2-ASN.mmdb',
        ],
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $licenseKey = $this->option('license-key') ?: env('MAXMIND_LICENSE_KEY');

        if (empty($licenseKey)) {
            $this->error('错误: 未提供 MaxMind License Key');
            $this->line('');
            $this->line('请使用以下方式之一提供 License Key:');
            $this->line('1. 使用 --license-key 参数: php artisan geoip:download --license-key=YOUR_KEY');
            $this->line('2. 在 .env 文件中设置: MAXMIND_LICENSE_KEY=YOUR_KEY');
            $this->line('');
            $this->line('获取 License Key: https://www.maxmind.com/en/accounts/current/license-key');
            return Command::FAILURE;
        }

        $dbType = $this->option('db');
        $databasesToDownload = [];

        if ($dbType === 'all') {
            $databasesToDownload = array_keys(self::DATABASES);
        } elseif (isset(self::DATABASES[$dbType])) {
            $databasesToDownload = [$dbType];
        } else {
            $this->error("错误: 无效的数据库类型 '{$dbType}'");
            $this->line('可用类型: country, city, asn, all');
            return Command::FAILURE;
        }

        $databasePath = base_path('vendor/larva/laravel-geoip2/database');
        
        if (!File::exists($databasePath)) {
            File::makeDirectory($databasePath, 0755, true);
            $this->info("创建数据库目录: {$databasePath}");
        }

        $client = new Client([
            'timeout' => 300, // 5分钟超时
            'verify' => true,
        ]);

        $successCount = 0;
        $failCount = 0;

        foreach ($databasesToDownload as $db) {
            $this->line('');
            $this->info("正在下载 {$db} 数据库...");
            
            $config = self::DATABASES[$db];
            $downloadUrl = self::MAXMIND_BASE_URL . '?' . http_build_query([
                'edition_id' => $config['edition_id'],
                'license_key' => $licenseKey,
                'suffix' => 'tar.gz',
            ]);

            $tempFile = sys_get_temp_dir() . '/' . uniqid('geoip2_') . '.tar.gz';
            $targetFile = $databasePath . '/' . $config['filename'];

            try {
                // 下载文件
                $this->line("下载地址: {$config['edition_id']}");
                $response = $client->get($downloadUrl, [
                    'sink' => $tempFile,
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception("HTTP 状态码: {$response->getStatusCode()}");
                }

                // 解压文件
                $this->line("解压文件...");
                $phar = new \PharData($tempFile);
                
                // 查找 mmdb 文件
                $mmdbFound = false;
                foreach (new \RecursiveIteratorIterator($phar) as $file) {
                    if ($file->isFile() && $file->getExtension() === 'mmdb') {
                        $mmdbPath = $file->getPathname();
                        // 复制到目标位置
                        File::copy($mmdbPath, $targetFile);
                        $mmdbFound = true;
                        break;
                    }
                }

                if (!$mmdbFound) {
                    throw new \Exception("在压缩包中未找到 .mmdb 文件");
                }

                // 设置文件权限
                chmod($targetFile, 0644);

                // 清理临时文件
                @unlink($tempFile);

                $fileSize = File::size($targetFile);
                $this->info("✓ {$config['filename']} 下载成功 (" . $this->formatBytes($fileSize) . ")");
                $successCount++;

                Log::info("GeoIP2 数据库更新成功", [
                    'database' => $db,
                    'filename' => $config['filename'],
                    'size' => $fileSize,
                ]);

            } catch (\Exception $e) {
                @unlink($tempFile);
                $this->error("✗ {$config['filename']} 下载失败: " . $e->getMessage());
                $failCount++;

                Log::error("GeoIP2 数据库更新失败", [
                    'database' => $db,
                    'filename' => $config['filename'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line('');
        if ($successCount > 0) {
            $this->info("成功: {$successCount} 个数据库已更新");
        }
        if ($failCount > 0) {
            $this->error("失败: {$failCount} 个数据库更新失败");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 格式化字节大小
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

