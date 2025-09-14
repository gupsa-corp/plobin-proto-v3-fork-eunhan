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
        // 기존 세션 키 동기화
        $this->synchronizeSessionKeys();

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
        \Log::info('SandboxContextMiddleware - Processing path', ['path' => $path]);

        // 정적 샌드박스 라우트들 (샌드박스 이름이 아닌 기능 이름)
        $staticRoutes = [
            'custom-screens', 'custom-screen-creator', 'dashboard', 'database-manager',
            'sql-executor', 'storage-manager', 'file-editor', 'api-creator', 'api-list',
            'blade-creator', 'function-browser', 'form-creator', 'scenario-manager',
            'git-version-control', 'using-projects'
        ];

        // /sandbox/{sandbox_name}/... 패턴 매칭
        if (preg_match('#^sandbox/([^/]+)#', $path, $matches)) {
            $detectedSandbox = $matches[1];

            // 정적 라우트인 경우 URL에서 샌드박스를 추출하지 않고, 기존 세션 유지
            if (in_array($detectedSandbox, $staticRoutes)) {
                \Log::info('SandboxContextMiddleware - Static route detected, keeping existing session sandbox', ['route' => $detectedSandbox]);
                return;
            }

            \Log::info('SandboxContextMiddleware - Detected dynamic sandbox from URL', ['detected' => $detectedSandbox]);

            try {
                // 샌드박스가 존재하면 컨텍스트에 설정
                if ($this->sandboxContextService->validateSandboxExists($detectedSandbox)) {
                    $this->sandboxContextService->setCurrentSandbox($detectedSandbox);
                    \Log::info('SandboxContextMiddleware - Sandbox set successfully', ['sandbox' => $detectedSandbox]);
                } else {
                    \Log::warning('SandboxContextMiddleware - Detected sandbox does not exist', ['detected' => $detectedSandbox]);
                }
            } catch (\Exception $e) {
                // 실패해도 요청 처리는 계속 진행
                \Log::warning('Failed to set sandbox context from URL', [
                    'detected_sandbox' => $detectedSandbox,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            \Log::info('SandboxContextMiddleware - No sandbox pattern found in path', ['path' => $path]);
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

    /**
     * 레거시 세션 키와 새 세션 키를 동기화합니다.
     */
    protected function synchronizeSessionKeys(): void
    {
        $currentSandbox = session('current_sandbox');
        $sandboxStorage = session('sandbox_storage');

        \Log::info('Session synchronization check', [
            'current_sandbox' => $currentSandbox,
            'sandbox_storage' => $sandboxStorage,
            'all_session' => session()->all()
        ]);

        // sandbox_storage에는 있는데 current_sandbox에 없는 경우 동기화
        if (!$currentSandbox && $sandboxStorage) {
            session(['current_sandbox' => $sandboxStorage]);
            \Log::info('Synchronized session keys', ['sandbox_storage' => $sandboxStorage, 'current_sandbox' => $currentSandbox]);
        }
        // current_sandbox에는 있는데 sandbox_storage에 없는 경우 동기화
        elseif ($currentSandbox && !$sandboxStorage) {
            session(['sandbox_storage' => $currentSandbox]);
            \Log::info('Synchronized session keys', ['current_sandbox' => $currentSandbox, 'sandbox_storage' => $sandboxStorage]);
        }
        // 둘 다 있는데 다른 경우 current_sandbox를 우선으로 동기화
        elseif ($currentSandbox && $sandboxStorage && $currentSandbox !== $sandboxStorage) {
            session(['sandbox_storage' => $currentSandbox]);
            \Log::info('Resolved session key conflict', ['using' => $currentSandbox, 'replaced' => $sandboxStorage]);
        }
        // 둘 다 없는 경우
        elseif (!$currentSandbox && !$sandboxStorage) {
            \Log::warning('No sandbox found in any session key');
        }
        // 둘 다 같은 값인 경우
        else {
            \Log::info('Session keys already synchronized', ['sandbox' => $currentSandbox]);
        }
    }
}