<?php

namespace App\Services\Sandbox\CustomScreenRender;

use App\Models\ProjectPage;
use App\Services\SandboxContextService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(ProjectPage $page): ?array
    {
        $sandboxInfo = app(\App\Services\Sandbox\PageInfo\Service::class)($page);

        if (!$sandboxInfo['has_sandbox'] || !$sandboxInfo['has_custom_screen']) {
            return null;
        }

        try {
            $sandboxContextService = app(SandboxContextService::class);
            $screenId = $sandboxInfo['custom_screen_folder'];
            // 동적 샌드박스 스토리지 경로 사용 (실제 파일이 있는 곳)
            $storagePath = $sandboxContextService->getSandboxStoragePath();

            if (!File::exists($storagePath)) {
                return null;
            }

            // 도메인별로 검색
            $domainFolders = File::directories($storagePath);
            $targetContentFile = null;
            $targetDomain = null;
            $targetScreenName = null;

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);
                $screenFolders = File::directories($domainFolder);

                foreach ($screenFolders as $screenFolder) {
                    $screenName = basename($screenFolder);
                    $contentFile = $screenFolder . '/000-content.blade.php';

                    // 화면 ID가 일치하는지 확인 (정확한 매치 우선, 다양한 번호 패턴 지원)
                    if ($screenName === $screenId) {
                        $targetContentFile = $contentFile;
                        $targetDomain = $domainName;
                        $targetScreenName = $screenName;
                        break 2;
                    }
                }
            }

            if ($targetContentFile && File::exists($targetContentFile)) {
                // 화면명에서 정보 추출
                $parts = explode('-', $targetScreenName, 3);
                $screenTitle = $parts[2] ?? 'unnamed';

                // Blade 템플릿을 실제 데이터로 렌더링
                try {
                    // 임시 블레이드 파일 생성 및 렌더링
                    $tempViewPath = 'project-renderer-temp-' . time() . '-' . rand(1000, 9999);
                    $tempViewFile = resource_path('views/' . $tempViewPath . '.blade.php');

                    $templateContent = File::get($targetContentFile);

                    // 템플릿에서 problematic require/include 구문 제거
                    $templateContent = preg_replace('/^\s*<\?php\s*$/m', '', $templateContent);
                    $templateContent = preg_replace('/^\s*require_once.*?bootstrap\.php.*?;?\s*$/m', '', $templateContent);
                    $templateContent = preg_replace('/^\s*use\s+App\\\\Services\\\\TemplateCommonService;\s*$/m', '', $templateContent);
                    $templateContent = preg_replace('/^\s*\$screenInfo\s*=.*?TemplateCommonService.*?;?\s*$/m', '', $templateContent);
                    $templateContent = preg_replace('/^\s*\$uploadPaths\s*=.*?TemplateCommonService.*?;?\s*$/m', '', $templateContent);
                    $templateContent = preg_replace('/^\s*\?>\s*$/m', '', $templateContent);

                    // 빈 PHP 블록 정리
                    $templateContent = preg_replace('/<\?php\s*\?>/s', '', $templateContent);

                    File::put($tempViewFile, $templateContent);

                    try {
                        // 실제 프로젝트 데이터 사용
                        $renderedContent = view($tempViewPath, [
                            'title' => $page->title ?? str_replace('-', ' ', $screenTitle),
                            'description' => $page->content ?? '프로젝트 페이지',
                            'organization' => $page->project->organization,
                            'project' => $page->project,
                            'page' => $page,
                            'organizations' => collect([$page->project->organization]),
                            'projects' => collect([$page->project]),
                            'users' => collect([]),
                            'activities' => collect([])
                        ])->render();
                    } catch (\Exception $e) {
                        $renderedContent = '<div class="p-4 bg-red-50 border border-red-200 rounded">
                            <h3 class="text-red-800 font-bold">템플릿 렌더링 오류</h3>
                            <p class="text-red-700 mt-2">' . $e->getMessage() . '</p>
                        </div>';
                    } finally {
                        // 임시 파일 삭제
                        if (File::exists($tempViewFile)) {
                            File::delete($tempViewFile);
                        }
                    }
                } catch (\Exception $e) {
                    $renderedContent = '<div class="p-4 bg-red-50 border border-red-200 rounded">
                        <h3 class="text-red-800 font-bold">파일 처리 오류</h3>
                        <p class="text-red-700 mt-2">' . $e->getMessage() . '</p>
                    </div>';
                }

                return [
                    'id' => $targetDomain . '-' . $targetScreenName,
                    'title' => $page->title ?? str_replace('-', ' ', $screenTitle),
                    'description' => $page->content ?? '프로젝트 페이지',
                    'type' => 'template',
                    'content' => $renderedContent,
                    'domain' => $targetDomain,
                    'screen' => $targetScreenName
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::info('커스텀 화면 로드 실패', [
                'pageId' => $page->id,
                'sandbox_folder' => $sandboxInfo['sandbox_name'],
                'sandbox_custom_screen_folder' => $sandboxInfo['custom_screen_folder'],
                'custom_screen_enabled' => $sandboxInfo['custom_screen_enabled'],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}