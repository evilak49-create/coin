<?php

namespace App\Providers;

use App\Broadcasting\LaravelSBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 注册 laravel-s 广播驱动
        $this->app->make(BroadcastManager::class)->extend('laravel-s', function ($app, $config) {
            return new LaravelSBroadcaster(
                $app->make('redis'),
                $config['connection'] ?? null,
                $app['config']->get('database.redis.options.prefix', '')
            );
        });
    }
}
