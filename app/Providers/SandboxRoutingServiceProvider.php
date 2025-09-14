<?php

namespace App\Providers;

use App\Http\Controllers\Sandbox\DynamicRouteController;
use App\Services\SandboxRoutingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class SandboxRoutingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // SandboxRoutingService를 싱글톤으로 등록
        $this->app->singleton(SandboxRoutingService::class, function ($app) {
            return new SandboxRoutingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerSandboxViews();
        $this->registerSandboxRoutes();
    }
    
    /**
     * 샌드박스 뷰 경로를 등록합니다.
     */
    private function registerSandboxViews(): void
    {
        // 샌드박스 컨테이너 뷰 경로 등록
        $this->app['view']->addNamespace('sandbox.container', [
            base_path('sandbox/container'),
        ]);
    }
    
    /**
     * 샌드박스 동적 라우트를 등록합니다.
     */
    private function registerSandboxRoutes(): void
    {
        try {
            Route::group([
                'prefix' => 'sandbox',
                'as' => 'sandbox.',
                'middleware' => ['web']
            ], function () {
                $this->registerDynamicRoutes();
                $this->registerApiRoutes();
                $this->registerManagementRoutes();
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to register sandbox routes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * 동적 샌드박스 라우트를 등록합니다.
     */
    private function registerDynamicRoutes(): void
    {
        // 샌드박스 메인 페이지 (샌드박스 목록)
        Route::get('/', function () {
            $sandboxService = app(SandboxRoutingService::class);
            $allRoutes = $sandboxService->getAllSandboxRoutes();
            
            // 샌드박스별로 그룹화
            $groupedRoutes = [];
            foreach ($allRoutes as $route) {
                $groupedRoutes[$route['sandbox']][] = $route;
            }
            
            return view('sandbox.index', [
                'grouped_routes' => $groupedRoutes,
                'total_routes' => count($allRoutes)
            ]);
        })->name('index');
        
        // 템플릿 화면 직접 렌더링 라우트 (우선순위를 위해 먼저 등록)
        Route::get('storage-sandbox-template/{domain}/{screen}', function($domain, $screen) {
            // 템플릿 파일 경로 생성
            $templateFile = base_path("sandbox/container/storage-sandbox-template/{$domain}/{$screen}/000-content.blade.php");
            
            if (!file_exists($templateFile)) {
                return abort(404, '템플릿 파일을 찾을 수 없습니다.');
            }
            
            // 템플릿 내용을 직접 렌더링하여 반환
            try {
                // 템플릿 뷰어 사용하여 드롭다운 헤더 포함하여 렌더링
                return view('700-page-sandbox.706-page-custom-screens.100-template-viewer', [
                    'sandboxName' => 'storage-sandbox-template',
                    'customScreen' => [
                        'id' => $domain . '-' . $screen,
                        'title' => ucwords(str_replace('-', ' ', explode('-screen-', $screen)[1] ?? $screen)),
                        'description' => ucwords(str_replace('-', ' ', explode('-domain-', $domain)[1] ?? $domain)) . ' 도메인 템플릿',
                        'domain' => $domain,
                        'screen' => $screen,
                        'is_template' => true
                    ],
                    'templateContent' => file_get_contents($templateFile)
                ]);
                
            } catch (\Exception $e) {
                return abort(500, '템플릿 렌더링 중 오류가 발생했습니다: ' . $e->getMessage());
            }
        })->name('template-screen')
          ->where('domain', '\d+-domain-[a-zA-Z0-9\-_]+')
          ->where('screen', '\d+-screen-[a-zA-Z0-9\-_]+');
          
        // 특정 샌드박스의 도메인 목록 (custom-screens, storage-sandbox-template 제외)
        Route::get('{sandbox}', [DynamicRouteController::class, 'showDomainList'])
              ->name('domains')
              ->where('sandbox', '^(?!custom-screens$|storage-sandbox-template$)[a-zA-Z0-9\-_]+$');
        
        // 특정 도메인의 화면 목록 (storage-sandbox-template 제외)
        Route::get('{sandbox}/{domain}', [DynamicRouteController::class, 'showScreenList'])
              ->name('screens')
              ->where('sandbox', '^(?!storage-sandbox-template$)[a-zA-Z0-9\-_]+$')
              ->where('domain', '\d+-domain-[a-zA-Z0-9\-_]+');
        
        // 동적 화면 라우트 (GET/POST 모두 지원) - storage-sandbox-template 제외
        Route::match(['get', 'post'], 
                     '{sandbox}/{domain}/{screen}', 
                     [DynamicRouteController::class, 'handleDynamicRoute'])
              ->name('screen')
              ->where('sandbox', '^(?!storage-sandbox-template$)[a-zA-Z0-9\-_]+$')
              ->where('domain', '\d+-domain-[a-zA-Z0-9\-_]+')
              ->where('screen', '\d+-screen-[a-zA-Z0-9\-_]+');
    }
    
    /**
     * API 라우트를 등록합니다.
     */
    private function registerApiRoutes(): void
    {
        Route::group([
            'prefix' => 'api',
            'as' => 'api.',
        ], function () {
            // 라우트 정보 API
            Route::get('routes', [DynamicRouteController::class, 'getRouteInfo'])
                  ->name('routes');
            
            // 캐시 새로고침 API
            Route::post('refresh-cache', [DynamicRouteController::class, 'refreshCache'])
                  ->name('refresh-cache');
            
            // 샌드박스별 도메인 정보 API
            Route::get('{sandbox}/domains', function ($sandbox) {
                $sandboxService = app(SandboxRoutingService::class);
                $domains = $sandboxService->getDomainList($sandbox);
                
                return response()->json([
                    'success' => true,
                    'sandbox' => $sandbox,
                    'domains' => $domains
                ]);
            })->name('domains.api')->where('sandbox', '[a-zA-Z0-9\-_]+');
            
            // 도메인별 화면 정보 API
            Route::get('{sandbox}/{domain}/screens', function ($sandbox, $domain) {
                $sandboxService = app(SandboxRoutingService::class);
                $screens = $sandboxService->getScreenList($domain, $sandbox);
                
                return response()->json([
                    'success' => true,
                    'sandbox' => $sandbox,
                    'domain' => $domain,
                    'screens' => $screens
                ]);
            })->name('screens.api')
              ->where('sandbox', '[a-zA-Z0-9\-_]+')
              ->where('domain', '\d+-domain-[a-zA-Z0-9\-_]+');
        });
    }
    
    /**
     * 관리 라우트를 등록합니다.
     */
    private function registerManagementRoutes(): void
    {
        // 관리자만 접근 가능한 라우트들
        Route::group([
            'prefix' => 'manage',
            'as' => 'manage.',
            // 'middleware' => ['auth', 'can:manage-sandbox'] // 필요시 권한 미들웨어 추가
        ], function () {
            // 전체 라우트 목록 관리 페이지
            Route::get('routes', function () {
                $sandboxService = app(SandboxRoutingService::class);
                $allRoutes = $sandboxService->getAllSandboxRoutes();
                
                return view('sandbox.manage.routes', [
                    'routes' => $allRoutes,
                    'total_count' => count($allRoutes)
                ]);
            })->name('routes');
            
            // 캐시 관리 페이지
            Route::get('cache', function () {
                return view('sandbox.manage.cache');
            })->name('cache');
            
            // 캐시 새로고침 (POST)
            Route::post('cache/refresh', [DynamicRouteController::class, 'refreshCache'])
                  ->name('cache.refresh');
        });
    }
}