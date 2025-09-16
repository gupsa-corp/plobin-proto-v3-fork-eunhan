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
            $createSubPages = $request->input('create_sub_pages', false);
            $subPages = $request->input('sub_pages', []);

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
                // 하위 페이지 자동 생성 처리
                if ($createSubPages && !empty($subPages)) {
                    $createdPages = [];
                    $maxOrder = ProjectPage::where('project_id', $projectId)
                        ->where('parent_id', $pageId)
                        ->max('sort_order') ?? 0;

                    foreach ($subPages as $index => $subPage) {
                        // slug 생성 (title을 기반으로)
                        $baseSlug = \Illuminate\Support\Str::slug($subPage['title']);
                        $slug = $baseSlug;
                        $counter = 1;

                        // 중복된 slug가 있는지 확인하고 유니크한 slug 생성
                        while (ProjectPage::where('project_id', $projectId)->where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $counter;
                            $counter++;
                        }

                        $newPage = new ProjectPage();
                        $newPage->project_id = $projectId;
                        $newPage->parent_id = $pageId;
                        $newPage->title = $subPage['title'];
                        $newPage->slug = $slug;
                        $newPage->user_id = auth()->id() ?? 1; // 현재 사용자 ID 또는 기본값
                        $newPage->sandbox_custom_screen_folder = $subPage['screen'];
                        $newPage->custom_screen_enabled = true;
                        $newPage->custom_screen_applied_at = now();
                        $newPage->sort_order = $maxOrder + $index + 1;
                        $newPage->save();

                        $createdPages[] = [
                            'id' => $newPage->id,
                            'title' => $newPage->title,
                            'screen' => $newPage->sandbox_custom_screen_folder
                        ];

                        Log::info('하위 페이지 생성', [
                            'page_id' => $newPage->id,
                            'title' => $newPage->title,
                            'screen' => $newPage->sandbox_custom_screen_folder
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => '대시보드와 하위 페이지들이 생성되었습니다.',
                        'data' => [
                            'domain' => $domainName,
                            'screen' => $screenName,
                            'custom_screen_folder' => $customScreenFolder,
                            'created_pages' => $createdPages
                        ]
                    ]);
                }

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