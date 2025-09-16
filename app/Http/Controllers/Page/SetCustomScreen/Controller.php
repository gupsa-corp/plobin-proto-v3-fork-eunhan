<?php

namespace App\Http\Controllers\Page\SetCustomScreen;

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

    public function __invoke($id, $projectId, $pageId, Request $request)
    {
        try {
            $page = ProjectPage::where('id', $pageId)
                ->whereHas('project', function($query) use ($projectId, $id) {
                    $query->where('id', $projectId)
                          ->whereHas('organization', function($q) use ($id) {
                              $q->where('id', $id);
                          });
                })->first();

            if (!$page) {
                return redirect()->back()->with('error', '페이지를 찾을 수 없습니다.');
            }

            // 프로젝트 레벨에서 샌드박스가 설정되어 있는지 확인
            if (!$this->sandboxService->hasProjectSandbox($page->project)) {
                return redirect()->back()->with('error', '커스텀 화면을 사용하려면 먼저 프로젝트 설정에서 샌드박스를 선택해야 합니다.');
            }

            $customScreenId = $request->input('custom_screen', '');
            $createSubPages = $request->input('create_sub_pages', false);

            // ID에서 폴더명 추출 (ID 형식: 도메인-화면폴더명)
            $customScreenFolder = '';
            if ($customScreenId) {
                // ID가 도메인-화면 형태인 경우 처리
                $parts = explode('-', $customScreenId);
                if (count($parts) > 2) {
                    // 첫 3개 부분은 도메인 (예: 101-domain-rfx)
                    // 나머지는 화면 폴더명 (예: 106-screen-analysis-dashboard)
                    $domainParts = array_slice($parts, 0, 3);
                    $screenParts = array_slice($parts, 3);
                    $customScreenFolder = implode('-', $screenParts);
                }
            }

            Log::info('커스텀 화면 설정 요청', [
                'customScreenId' => $customScreenId,
                'customScreenFolder' => $customScreenFolder,
                'createSubPages' => $createSubPages,
                'pageId' => $pageId
            ]);

            // 기존 화면이 106-screen-analysis-dashboard였는데 다른 화면으로 변경하는 경우
            // 자동 생성된 하위 페이지들 삭제
            $previousScreen = $page->sandbox_custom_screen_folder;
            if ($previousScreen === '106-screen-analysis-dashboard' && $customScreenFolder !== '106-screen-analysis-dashboard') {
                $analysisScreens = [
                    '103-screen-uploaded-files-list',
                    '104-screen-analysis-requests',
                    '105-screen-document-analysis'
                ];

                $deletedCount = ProjectPage::where('parent_id', $pageId)
                    ->whereIn('sandbox_custom_screen_folder', $analysisScreens)
                    ->delete();

                if ($deletedCount > 0) {
                    Log::info('분석 대시보드 하위 페이지 삭제', [
                        'parent_id' => $pageId,
                        'deleted_count' => $deletedCount
                    ]);
                }
            }

            if ($this->sandboxService->setCustomScreen($page, $customScreenFolder)) {
                // 분석 대시보드 선택 시 하위 페이지 자동 생성
                if ($customScreenFolder === '106-screen-analysis-dashboard' && $createSubPages === 'yes') {
                    $subPages = [
                        ['title' => '업로드 파일 목록', 'screen' => '103-screen-uploaded-files-list'],
                        ['title' => '분석 요청', 'screen' => '104-screen-analysis-requests'],
                        ['title' => '문서 분석', 'screen' => '105-screen-document-analysis']
                    ];

                    $maxOrder = ProjectPage::where('project_id', $projectId)
                        ->where('parent_id', $pageId)
                        ->max('sort_order') ?? 0;

                    foreach ($subPages as $index => $subPage) {
                        // 중복 체크
                        $exists = ProjectPage::where('project_id', $projectId)
                            ->where('parent_id', $pageId)
                            ->where('sandbox_custom_screen_folder', $subPage['screen'])
                            ->exists();

                        if (!$exists) {
                            // slug 생성 - 다른 페이지와 같은 형식 사용 (new-page-timestamp)
                            $slug = 'new-page-' . time();

                            // 중복된 slug가 있는지 확인 (timestamp가 같은 경우를 대비)
                            $counter = 1;
                            $baseSlug = $slug;
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
                            $newPage->sandbox_domain = $page->sandbox_domain; // 부모 페이지의 도메인 상속
                            $newPage->sandbox_folder = $page->sandbox_folder; // 부모 페이지의 샌드박스 폴더 상속
                            $newPage->sandbox_mode = $page->sandbox_mode; // 부모 페이지의 샌드박스 모드 상속
                            $newPage->custom_screen_enabled = true;
                            $newPage->custom_screen_applied_at = now();
                            $newPage->sort_order = $maxOrder + $index + 1;
                            $newPage->save();

                            Log::info('하위 페이지 생성', [
                                'page_id' => $newPage->id,
                                'title' => $newPage->title,
                                'screen' => $newPage->sandbox_custom_screen_folder
                            ]);
                        }
                    }

                    return redirect()->back()->with('success', '대시보드와 하위 페이지들이 생성되었습니다.');
                }

                return redirect()->back()->with('success', '커스텀 화면 설정이 저장되었습니다.');
            } else {
                return redirect()->back()->with('error', '설정 저장 중 오류가 발생했습니다.');
            }

        } catch (\Exception $e) {
            Log::error('커스텀 화면 설정 저장 오류', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', '설정 저장 중 오류가 발생했습니다.');
        }
    }
}
