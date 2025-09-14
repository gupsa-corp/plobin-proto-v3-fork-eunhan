<?php

namespace App\Http\Controllers\Api\Sandbox\SaveCustomScreen;

use App\Models\ProjectPage;
use App\Services\SandboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Controller extends \App\Http\Controllers\Controller
{
    protected SandboxService $sandboxService;

    public function __construct(SandboxService $sandboxService)
    {
        $this->sandboxService = $sandboxService;
    }

    public function __invoke(Request $request)
    {
        try {
            $organizationId = $request->input('organization_id');
            $projectId = $request->input('project_id');
            $pageId = $request->input('page_id');
            $domainName = $request->input('domain');
            $screenName = $request->input('screen');

            // 페이지 조회
            $page = ProjectPage::where('id', $pageId)
                ->whereHas('project', function($query) use ($projectId, $organizationId) {
                    $query->where('id', $projectId)
                          ->whereHas('organization', function($q) use ($organizationId) {
                              $q->where('id', $organizationId);
                          });
                })->first();

            if (!$page) {
                return response()->json([
                    'success' => false,
                    'message' => '페이지를 찾을 수 없습니다.'
                ], 404);
            }

            // 프로젝트 레벨에서 샌드박스가 설정되어 있는지 확인
            if (!$this->sandboxService->hasProjectSandbox($page->project)) {
                return response()->json([
                    'success' => false,
                    'message' => '커스텀 화면을 사용하려면 먼저 프로젝트 설정에서 샌드박스를 선택해야 합니다.'
                ], 400);
            }

            // 커스텀 화면 설정 저장
            $customScreenFolder = $screenName;

            if ($this->sandboxService->setCustomScreen($page, $customScreenFolder)) {
                return response()->json([
                    'success' => true,
                    'message' => '커스텀 화면이 저장되었습니다.',
                    'data' => [
                        'domain' => $domainName,
                        'screen' => $screenName,
                        'custom_screen_folder' => $customScreenFolder
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '커스텀 화면 저장에 실패했습니다.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('커스텀 화면 저장 API 오류', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => '서버 오류가 발생했습니다.'
            ], 500);
        }
    }
}