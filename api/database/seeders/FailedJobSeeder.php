<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FailedJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id' => 1,
                'uuid' => 'a75f0750-0631-4262-bd9a-7a3606f39d05',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"a75f0750-0631-4262-bd9a-7a3606f39d05\",\"timeout\":60,\"id\":\"DAJqmOVLxBLWrPaNPsFcKF7kpMkB0OIo\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 16:05:28',
            ],
            [
                'id' => 2,
                'uuid' => '7094766b-c725-4bda-914b-cb0aa181871c',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"7094766b-c725-4bda-914b-cb0aa181871c\",\"timeout\":60,\"id\":\"gwNZjxN2qpDcK5Ix5LlSUs4mH3nVvNcv\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 16:05:52',
            ],
            [
                'id' => 3,
                'uuid' => '553e5e9f-caac-46ed-8040-50f11311f096',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"553e5e9f-caac-46ed-8040-50f11311f096\",\"timeout\":60,\"id\":\"bBesDd0HRNJJqBGE0C8j3mmu8A0oPlo7\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 18:05:26',
            ],
            [
                'id' => 4,
                'uuid' => 'b34efa89-ce87-429a-be52-76bbe8ee1927',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"b34efa89-ce87-429a-be52-76bbe8ee1927\",\"timeout\":60,\"id\":\"YfXgUdN2A6hBxdiDVswP5In74umFw5Kb\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 18:05:47',
            ],
            [
                'id' => 5,
                'uuid' => '84ba68c5-b071-4bd9-8fa9-e3ce8d0ae539',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"84ba68c5-b071-4bd9-8fa9-e3ce8d0ae539\",\"timeout\":60,\"id\":\"JukNxoiqphILyQNW7rf5FYCvjj8BqH5P\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 20:05:28',
            ],
            [
                'id' => 6,
                'uuid' => '7eba24ab-a2ab-4a60-8b6f-1a1e3bf1727c',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"7eba24ab-a2ab-4a60-8b6f-1a1e3bf1727c\",\"timeout\":60,\"id\":\"0gs5OBW668g0LrKMI9YmCSqLySxllMm3\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 20:05:58',
            ],
            [
                'id' => 7,
                'uuid' => '9218d158-4325-43fb-a271-9ea1b8bfbbf5',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"9218d158-4325-43fb-a271-9ea1b8bfbbf5\",\"timeout\":60,\"id\":\"LiFEwLJukX0N7X9Sc4BACeUVv6aUwNAB\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 22:05:31',
            ],
            [
                'id' => 8,
                'uuid' => '855f97c3-3993-4369-92f7-04217933fabd',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"855f97c3-3993-4369-92f7-04217933fabd\",\"timeout\":60,\"id\":\"qyIYjNm0FHQD3ZWaq0xA44WpuF09xHvj\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-05 22:05:49',
            ],
            [
                'id' => 9,
                'uuid' => 'dfa8b438-269a-4c6b-b76a-eaa3893d7d70',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"dfa8b438-269a-4c6b-b76a-eaa3893d7d70\",\"timeout\":60,\"id\":\"ctBVO2Zr0UuhxL7Wl4IBaID0q0HRr5C2\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 00:05:28',
            ],
            [
                'id' => 10,
                'uuid' => 'd9993b06-4afd-44c9-994a-37dba79e1146',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"d9993b06-4afd-44c9-994a-37dba79e1146\",\"timeout\":60,\"id\":\"nAhaii7B2V8vMmELMscXgbfjoyAxiO2U\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 00:05:52',
            ],
            [
                'id' => 11,
                'uuid' => '3ec5b9ad-3ed1-496f-a2bd-2fac26df8c62',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"3ec5b9ad-3ed1-496f-a2bd-2fac26df8c62\",\"timeout\":60,\"id\":\"KLEUT4JQsY440kmdnWCVTUXSEo5v0iBR\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 02:05:28',
            ],
            [
                'id' => 12,
                'uuid' => '2777bd8c-64bd-4c08-b5d1-46efda2b0e85',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"2777bd8c-64bd-4c08-b5d1-46efda2b0e85\",\"timeout\":60,\"id\":\"Hi980pPBKRGvclylYAymw4S0exHfMMM6\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 02:05:52',
            ],
            [
                'id' => 13,
                'uuid' => 'f32af366-1f0a-4082-803f-5655008dd340',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"f32af366-1f0a-4082-803f-5655008dd340\",\"timeout\":60,\"id\":\"tQmiJdeeywBRI9QsA7LaXOJAaD37CIcw\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 04:05:25',
            ],
            [
                'id' => 14,
                'uuid' => '5093ee15-73f2-45ef-acff-e0001a525bad',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"5093ee15-73f2-45ef-acff-e0001a525bad\",\"timeout\":60,\"id\":\"3a0bwzlkEC9u0om549O3spts77f8ORFk\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 04:05:49',
            ],
            [
                'id' => 15,
                'uuid' => 'ffa332dd-c664-44d1-8615-c8de4e5195ed',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"ffa332dd-c664-44d1-8615-c8de4e5195ed\",\"timeout\":60,\"id\":\"VrcmGEsm8kOwTZgyakOfzzbYIiU1Z1NN\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 06:05:28',
            ],
            [
                'id' => 16,
                'uuid' => 'cffdf6bd-0dc7-4f69-ab87-675510ce5d92',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"cffdf6bd-0dc7-4f69-ab87-675510ce5d92\",\"timeout\":60,\"id\":\"5ezgPViM4avosvd3Id9cEz0DfV0qoKos\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 06:05:47',
            ],
            [
                'id' => 17,
                'uuid' => '3c4802c0-0d1c-4a8d-b0d5-ed2f2831ea86',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"3c4802c0-0d1c-4a8d-b0d5-ed2f2831ea86\",\"timeout\":60,\"id\":\"Smc63kQMYrR8VIfYnROW1mILnTJeFEYI\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 08:05:27',
            ],
            [
                'id' => 18,
                'uuid' => '673359e8-4a45-4606-b40c-85f1056be476',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"673359e8-4a45-4606-b40c-85f1056be476\",\"timeout\":60,\"id\":\"RdEPV7AV4eCzC9yXIermJmOMq8XKjLGp\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 08:05:48',
            ],
            [
                'id' => 19,
                'uuid' => '53ee122a-41ef-4451-be94-d002af420b8a',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"53ee122a-41ef-4451-be94-d002af420b8a\",\"timeout\":60,\"id\":\"IqP9sMFa2JzXDgDHcNx1OsYQniRbdb5F\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 10:05:31',
            ],
            [
                'id' => 20,
                'uuid' => '06fe11a1-6e98-4c00-950f-2693ffb46a6e',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"06fe11a1-6e98-4c00-950f-2693ffb46a6e\",\"timeout\":60,\"id\":\"YhaVa0uYpD13k4IfbIfrgVP1EYWxqXy0\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 10:05:49',
            ],
            [
                'id' => 21,
                'uuid' => '43a9469f-7a2f-46c7-b49f-781f849a7160',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"43a9469f-7a2f-46c7-b49f-781f849a7160\",\"timeout\":60,\"id\":\"Nk8us3OoqQHV9XW0DHaCPehwy5LzV1ti\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 12:05:25',
            ],
            [
                'id' => 22,
                'uuid' => '31e23773-fdf7-456f-aed1-abcee753030e',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"31e23773-fdf7-456f-aed1-abcee753030e\",\"timeout\":60,\"id\":\"MU1JxB3u4sGbR4PjRcZusq9g0uDhbtbu\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 12:05:49',
            ],
            [
                'id' => 23,
                'uuid' => 'd33b2782-5be9-4832-b079-8f11237ba025',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"d33b2782-5be9-4832-b079-8f11237ba025\",\"timeout\":60,\"id\":\"O9mFMNzQrICTtJhrhgxQcyv1t8KLdLNa\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 14:05:24',
            ],
            [
                'id' => 24,
                'uuid' => '62b398e8-0cd3-413b-9ae2-ecaecaec002f',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"62b398e8-0cd3-413b-9ae2-ecaecaec002f\",\"timeout\":60,\"id\":\"KjJPmV8twqJf9CuU1l0nMj4VDVmX6a7D\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 14:05:48',
            ],
            [
                'id' => 25,
                'uuid' => '087905a2-a27d-413c-b81f-0511a30765c1',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"087905a2-a27d-413c-b81f-0511a30765c1\",\"timeout\":60,\"id\":\"s7WmrQ1Sx11w0fHOYb8o8x0m2pffAThe\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 16:05:26',
            ],
            [
                'id' => 26,
                'uuid' => '1c0f631c-6ea5-4f2a-8f78-9f6df10af542',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"1c0f631c-6ea5-4f2a-8f78-9f6df10af542\",\"timeout\":60,\"id\":\"Jhb4IiuHlpJYZME1mixjygWRK3DXBgTk\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 16:05:50',
            ],
            [
                'id' => 27,
                'uuid' => 'fcaade09-8b70-4912-b667-8f7a57701e6a',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"fcaade09-8b70-4912-b667-8f7a57701e6a\",\"timeout\":60,\"id\":\"wFdAsznTXUg5mGbOeZRw4BOhQ6rPCNHh\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:80;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 18:05:26',
            ],
            [
                'id' => 28,
                'uuid' => '4aa1befa-99ba-482a-b85f-24bfd44cc80b',
                'connection' => 'redis',
                'queue' => 'wallet-balance-query',
                'payload' => '{\"uuid\":\"4aa1befa-99ba-482a-b85f-24bfd44cc80b\",\"timeout\":60,\"id\":\"405utGIfFszJn45Ncde6J32EmDvaG1rO\",\"backoff\":\"5,10,30\",\"displayName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\",\"maxTries\":3,\"failOnTimeout\":false,\"maxExceptions\":null,\"retryUntil\":null,\"data\":{\"command\":\"O:30:\\\"App\\\\Jobs\\\\QueryWalletBalanceJob\\\":4:{s:16:\\\"receiveWordKeyId\\\";i:70;s:12:\\\"currencyType\\\";s:3:\\\"XRP\\\";s:7:\\\"address\\\";s:34:\\\"rGNFD4Fxvuxu6HQuuRbNPHr6CG9ZPxvMUT\\\";s:5:\\\"queue\\\";s:20:\\\"wallet-balance-query\\\";}\",\"commandName\":\"App\\\\Jobs\\\\QueryWalletBalanceJob\"},\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"attempts\":3}',
                'exception' => 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\QueryWalletBalanceJob has been attempted too many times or run too long. The job may have previously timed out. in /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php:746
Stack trace:
#0 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(505): Illuminate\\Queue\\Worker->maxAttemptsExceededException()
#1 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(415): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts()
#2 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(375): Illuminate\\Queue\\Worker->process()
#3 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(173): Illuminate\\Queue\\Worker->runJob()
#4 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(148): Illuminate\\Queue\\Worker->daemon()
#5 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(131): Illuminate\\Queue\\Console\\WorkCommand->runWorker()
#6 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()
#7 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()
#8 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()
#9 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()
#10 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Container/Container.php(661): Illuminate\\Container\\BoundMethod::call()
#11 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(183): Illuminate\\Container\\Container->call()
#12 /www/wwwroot/coin/coin/api/vendor/symfony/console/Command/Command.php(326): Illuminate\\Console\\Command->execute()
#13 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Command.php(153): Symfony\\Component\\Console\\Command\\Command->run()
#14 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(1083): Illuminate\\Console\\Command->run()
#15 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand()
#16 /www/wwwroot/coin/coin/api/vendor/symfony/console/Application.php(175): Symfony\\Component\\Console\\Application->doRun()
#17 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()
#18 /www/wwwroot/coin/coin/api/vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()
#19 /www/wwwroot/coin/coin/api/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()
#20 {main}',
                'failed_at' => '2026-01-06 18:05:47',
            ],
        ];

        foreach ($data as $row) {
            DB::table('failed_jobs')->insert($row);
        }
    }
}
