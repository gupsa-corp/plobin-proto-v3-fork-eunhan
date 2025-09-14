<?php

namespace App\Http\Controllers\Sandbox;

use App\Http\Controllers\Controller;
use App\Services\SandboxRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class DynamicRouteController extends Controller
{
    private SandboxRoutingService $sandboxRoutingService;
    
    public function __construct(SandboxRoutingService $sandboxRoutingService)
    {
        $this->sandboxRoutingService = $sandboxRoutingService;
    }
    
    /**
     * 동적 샌드박스 라우트를 처리합니다.
     */
    public function handleDynamicRoute(Request $request, string $sandboxName, string $domainName, string $screenName)
    {
        try {
            // 뷰 경로 생성
            $viewPath = $this->sandboxRoutingService->generateViewPath($domainName, $screenName, $sandboxName);
            
            // 뷰 파일이 존재하는지 확인
            if (!View::exists($viewPath)) {
                Log::warning("Sandbox view not found: {$viewPath}", [
                    'sandbox' => $sandboxName,
                    'domain' => $domainName,
                    'screen' => $screenName,
                    'request_path' => $request->path()
                ]);
                
                return $this->renderNotFoundPage($sandboxName, $domainName, $screenName);
            }
            
            // 뷰 데이터 준비
            $viewData = $this->prepareViewData($request, $sandboxName, $domainName, $screenName);
            
            // 뷰 렌더링
            return view($viewPath, $viewData);
            
        } catch (\Exception $e) {
            Log::error("Error handling dynamic sandbox route", [
                'sandbox' => $sandboxName,
                'domain' => $domainName,
                'screen' => $screenName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->renderErrorPage($e, $sandboxName, $domainName, $screenName);
        }
    }
    
    /**
     * 샌드박스 도메인 목록을 표시합니다.
     */
    public function showDomainList(Request $request, string $sandboxName)
    {
        try {
            $domains = $this->sandboxRoutingService->getDomainList($sandboxName);
            
            return view('sandbox.domain-list', [
                'sandbox_name' => $sandboxName,
                'domains' => $domains,
                'page_title' => "샌드박스 '{$sandboxName}' 도메인 목록"
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error showing domain list", [
                'sandbox' => $sandboxName,
                'error' => $e->getMessage()
            ]);
            
            return abort(500, '도메인 목록을 불러오는 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 특정 도메인의 화면 목록을 표시합니다.
     */
    public function showScreenList(Request $request, string $sandboxName, string $domainName)
    {
        try {
            $screens = $this->sandboxRoutingService->getScreenList($domainName, $sandboxName);
            
            if (empty($screens)) {
                return abort(404, "도메인 '{$domainName}'를 찾을 수 없습니다.");
            }
            
            return view('sandbox.screen-list', [
                'sandbox_name' => $sandboxName,
                'domain_name' => $domainName,
                'screens' => $screens,
                'page_title' => "도메인 '{$domainName}' 화면 목록"
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error showing screen list", [
                'sandbox' => $sandboxName,
                'domain' => $domainName,
                'error' => $e->getMessage()
            ]);
            
            return abort(500, '화면 목록을 불러오는 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 샌드박스 라우팅 캐시를 새로고침합니다.
     */
    public function refreshCache(Request $request)
    {
        try {
            $this->sandboxRoutingService->refreshCache();
            
            return response()->json([
                'success' => true,
                'message' => '샌드박스 라우팅 캐시가 새로고침되었습니다.',
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error refreshing sandbox cache", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '캐시 새로고침 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 모든 샌드박스 라우트 정보를 API로 제공합니다.
     */
    public function getRouteInfo(Request $request)
    {
        try {
            $routes = $this->sandboxRoutingService->getAllSandboxRoutes();
            
            return response()->json([
                'success' => true,
                'routes' => $routes,
                'count' => count($routes),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error getting route info", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '라우트 정보를 불러오는 중 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 뷰 데이터를 준비합니다.
     */
    private function prepareViewData(Request $request, string $sandboxName, string $domainName, string $screenName): array
    {
        return [
            'sandbox_name' => $sandboxName,
            'domain_name' => $domainName,
            'screen_name' => $screenName,
            'request_data' => $request->all(),
            'current_route' => $request->path(),
            'is_post_request' => $request->isMethod('post'),
            'page_title' => $this->generatePageTitle($domainName, $screenName),
            'breadcrumbs' => $this->generateBreadcrumbs($sandboxName, $domainName, $screenName),
        ];
    }
    
    /**
     * 페이지 제목을 생성합니다.
     */
    private function generatePageTitle(string $domainName, string $screenName): string
    {
        // 도메인명과 화면명에서 읽기 좋은 제목 생성
        $domainTitle = str_replace(['-domain-', '-'], [' ', ' '], $domainName);
        $screenTitle = str_replace(['-screen-', '-'], [' ', ' '], $screenName);
        
        return ucwords($domainTitle) . ' - ' . ucwords($screenTitle);
    }
    
    /**
     * 브레드크럼을 생성합니다.
     */
    private function generateBreadcrumbs(string $sandboxName, string $domainName, string $screenName): array
    {
        return [
            ['title' => '샌드박스', 'url' => '/sandbox'],
            ['title' => $sandboxName, 'url' => "/sandbox/{$sandboxName}"],
            ['title' => $domainName, 'url' => "/sandbox/{$sandboxName}/{$domainName}"],
            ['title' => $screenName, 'url' => null], // 현재 페이지
        ];
    }
    
    /**
     * 404 페이지를 렌더링합니다.
     */
    private function renderNotFoundPage(string $sandboxName, string $domainName, string $screenName)
    {
        return response()->view('sandbox.errors.not-found', [
            'sandbox_name' => $sandboxName,
            'domain_name' => $domainName,
            'screen_name' => $screenName,
            'message' => "화면 '{$screenName}'을 찾을 수 없습니다.",
            'suggestions' => $this->getSuggestions($sandboxName, $domainName)
        ], 404);
    }
    
    /**
     * 에러 페이지를 렌더링합니다.
     */
    private function renderErrorPage(\Exception $e, string $sandboxName, string $domainName, string $screenName)
    {
        return response()->view('sandbox.errors.server-error', [
            'sandbox_name' => $sandboxName,
            'domain_name' => $domainName,
            'screen_name' => $screenName,
            'error_message' => config('app.debug') ? $e->getMessage() : '서버 오류가 발생했습니다.',
            'error_code' => 500
        ], 500);
    }
    
    /**
     * 대안 제안을 생성합니다.
     */
    private function getSuggestions(string $sandboxName, string $domainName): array
    {
        try {
            $screens = $this->sandboxRoutingService->getScreenList($domainName, $sandboxName);
            return array_slice($screens, 0, 5); // 최대 5개 제안
        } catch (\Exception $e) {
            return [];
        }
    }
}