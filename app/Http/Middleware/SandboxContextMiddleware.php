<?php

namespace App\Http\Middleware;

use App\Services\SandboxContextService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SandboxContextMiddleware
{
    protected $sandboxContextService;

    public function __construct(SandboxContextService $sandboxContextService)
    {
        $this->sandboxContextService = $sandboxContextService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // URL에서 샌드박스 자동 감지 및 설정
        $this->detectAndSetSandboxFromUrl($request);

        // 현재 샌드박스 컨텍스트를 뷰에 공유
        $this->shareContextWithViews();

        return $next($request);
    }

    /**
     * URL에서 샌드박스를 감지하고 컨텍스트에 설정합니다.
     */
    protected function detectAndSetSandboxFromUrl(Request $request): void
    {
        $path = $request->path();
        
        // /sandbox/{sandbox_name}/... 패턴 매칭
        if (preg_match('#^sandbox/([^/]+)#', $path, $matches)) {
            $detectedSandbox = $matches[1];
            
            try {
                // 샌드박스가 존재하면 컨텍스트에 설정
                if ($this->sandboxContextService->validateSandboxExists($detectedSandbox)) {
                    $this->sandboxContextService->setCurrentSandbox($detectedSandbox);
                }
            } catch (\Exception $e) {
                // 실패해도 요청 처리는 계속 진행
                \Log::warning('Failed to set sandbox context from URL', [
                    'detected_sandbox' => $detectedSandbox,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // API 라우트의 경우도 체크
        if (preg_match('#^api/sandbox/([^/]+)#', $path, $matches)) {
            $detectedSandbox = $matches[1];
            
            try {
                if ($this->sandboxContextService->validateSandboxExists($detectedSandbox)) {
                    $this->sandboxContextService->setCurrentSandbox($detectedSandbox);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to set sandbox context from API URL', [
                    'detected_sandbox' => $detectedSandbox,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * 현재 샌드박스 컨텍스트를 모든 뷰에 공유합니다.
     */
    protected function shareContextWithViews(): void
    {
        try {
            $context = $this->sandboxContextService->getCurrentContext();
            
            // 템플릿에서 사용할 변수들 공유
            View::share([
                'current_sandbox' => $context['sandbox_name'],
                'sandbox_context' => $context,
                'sandbox_url' => $context['sandbox_url'],
                'sandbox_display_name' => $context['display_name'],
                'available_sandboxes' => $this->sandboxContextService->getAvailableSandboxes()
            ]);
            
        } catch (\Exception $e) {
            // 실패해도 뷰 렌더링은 계속 진행
            \Log::error('Failed to share sandbox context with views', [
                'error' => $e->getMessage()
            ]);
            
            // 샌드박스가 선택되지 않았음을 명시
            View::share([
                'current_sandbox' => null,
                'sandbox_context' => ['error' => '샌드박스가 선택되지 않음'],
                'sandbox_url' => null,
                'sandbox_display_name' => '샌드박스 선택 필요',
                'available_sandboxes' => $this->sandboxContextService->getAvailableSandboxes()
            ]);
        }
    }
}