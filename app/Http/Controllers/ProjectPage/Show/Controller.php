<?php

namespace App\Http\Controllers\ProjectPage\Show;

use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Services\AccessControlService;
use App\Services\SandboxService;
use App\Services\SandboxContextService;
use Illuminate\Support\Facades\Auth;

class Controller extends \App\Http\Controllers\Controller
{
    protected SandboxService $sandboxService;
    protected SandboxContextService $sandboxContextService;
    protected AccessControlService $accessControlService;

    public function __construct(
        SandboxService $sandboxService,
        SandboxContextService $sandboxContextService,
        AccessControlService $accessControlService
    ) {
        $this->sandboxService = $sandboxService;
        $this->sandboxContextService = $sandboxContextService;
        $this->accessControlService = $accessControlService;
    }

    public function __invoke($id, $projectId, $pageId)
    {
        // 기본 데이터 조회
        $organization = Organization::find($id);
        $project = Project::find($projectId);
        $page = ProjectPage::find($pageId);

        // 페이지 접근 권한 확인
        if ($page && Auth::check()) {
            if (!$this->accessControlService->canUserAccessPage(Auth::user(), $page)) {
                abort(403, '이 페이지에 접근할 권한이 없습니다.');
            }
        }

        // 샌드박스 정보 조회
        $sandboxInfo = null;
        $customScreen = null;
        $customScreens = [];

        if ($page) {
            $sandboxInfo = $this->sandboxService->getPageSandboxInfo($page);

            // 샌드박스 컨텍스트 설정
            if ($sandboxInfo['has_sandbox']) {
                $this->sandboxContextService->setCurrentSandbox($sandboxInfo['sandbox_name']);
            }

            // 커스텀 스크린 렌더링
            if ($sandboxInfo['has_custom_screen']) {
                $customScreen = $this->sandboxService->renderCustomScreen($page);
            }

            // 사용 가능한 커스텀 스크린 목록 및 도메인 목록 조회
            if ($sandboxInfo['has_sandbox']) {
                $customScreens = $this->sandboxService->getAvailableCustomScreens($sandboxInfo['sandbox_name']);
                $availableDomains = $this->sandboxService->getAvailableDomains($sandboxInfo['sandbox_name']);
            }
        }

        // 도메인 목록 초기화 (샌드박스가 없는 경우)
        if (!isset($availableDomains)) {
            $availableDomains = [];
        }

        return view('300-page-service.308-page-project-dashboard.000-index', [
            'currentPageId' => $pageId,
            'activeTab' => 'overview',
            'organization' => $organization,
            'project' => $project,
            'page' => $page,
            'sandboxInfo' => $sandboxInfo,
            'customScreen' => $customScreen,
            'customScreens' => $customScreens,
            'availableDomains' => $availableDomains,
            // 기존 변수들 (하위 호환성)
            'sandboxName' => $sandboxInfo['sandbox_name'] ?? null,
            'hasSandbox' => $sandboxInfo['has_sandbox'] ?? false,
            'customScreenFolder' => $sandboxInfo['custom_screen_folder'] ?? null,
        ]);
    }
}