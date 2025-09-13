<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ViewService;
use App\Services\ComponentService;
use App\Services\StorageCommonService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 서비스들을 싱글톤으로 등록
        $this->app->singleton(ViewService::class, function ($app) {
            return new ViewService();
        });

        $this->app->singleton(ComponentService::class, function ($app) {
            return new ComponentService();
        });

        // SandboxHelper Facade를 위한 서비스 등록
        $this->app->singleton('sandbox.helper', function ($app) {
            return new StorageCommonService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
