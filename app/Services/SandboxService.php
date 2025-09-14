<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectPage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SandboxService
{
    /**
     * 프로젝트 페이지의 샌드박스 정보를 가져옵니다.
     */
    public function getPageSandboxInfo(ProjectPage $page): array
    {
        $project = $page->project;
        
        return [
            'has_sandbox' => !empty($project->sandbox_folder),
            'sandbox_name' => $project->sandbox_folder,
            'sandbox_level' => 'project',
            'has_custom_screen' => $page->custom_screen_enabled && !empty($page->sandbox_custom_screen_folder),
            'custom_screen_folder' => $page->sandbox_custom_screen_folder,
            'custom_screen_enabled' => $page->custom_screen_enabled,
            'template_path' => $page->template_path,
        ];
    }

    /**
     * 사용 가능한 커스텀 스크린 목록을 가져옵니다.
     */
    public function getAvailableCustomScreens(?string $sandboxName = null): array
    {
        if (empty($sandboxName)) {
            return [];
        }

        try {
            // 새로운 컨테이너 기반 템플릿 경로 사용
            $templatePath = base_path('sandbox/container/storage-sandbox-template');
            
            if (!File::exists($templatePath)) {
                return [];
            }

            // 새로운 도메인 기반 구조 처리
            $domainFolders = File::directories($templatePath);
            $customScreens = [];

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);
                // 도메인 폴더 안의 화면 폴더들을 확인
                $screenFolders = File::directories($domainFolder);
                
                foreach ($screenFolders as $screenFolder) {
                    $screenName = basename($screenFolder);
                    $contentFile = $screenFolder . '/000-content.blade.php';

                    if (File::exists($contentFile)) {
                        // 화면명에서 정보 추출
                        $parts = explode('-', $screenName, 3);
                        $screenId = $parts[0] ?? '000';
                        $screenType = $parts[1] ?? 'screen';
                        $screenTitle = $parts[2] ?? 'unnamed';

                        $customScreens[] = [
                            'id' => $domainName . '-' . $screenName, // 도메인-화면 형태로 ID 생성
                            'title' => str_replace('-', ' ', $screenTitle),
                            'description' => str_replace('-', ' ', $domainName) . ' 도메인의 ' . str_replace('-', ' ', $screenTitle),
                            'type' => $screenType,
                            'folder_name' => $screenName,
                            'domain' => $domainName,
                            'domain_display' => str_replace('-', ' ', ucfirst($domainName)),
                            'file_path' => $domainName . '/' . $screenName . '/000-content.blade.php',
                            'created_at' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                            'file_exists' => true,
                            'full_path' => $contentFile,
                            'file_size' => File::size($contentFile),
                            'file_modified' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                            'is_template' => true,
                        ];
                    }
                }
            }

            // 생성 날짜 기준 정렬
            usort($customScreens, function($a, $b) {
                return strcmp($a['folder_name'], $b['folder_name']);
            });

            return $customScreens;

        } catch (\Exception $e) {
            Log::error('커스텀 화면 목록 로드 오류', ['error' => $e->getMessage(), 'sandbox_name' => $sandboxName]);
            return [];
        }
    }

    /**
     * 커스텀 스크린 콘텐츠를 렌더링합니다.
     */
    public function renderCustomScreen(ProjectPage $page): ?array
    {
        $sandboxInfo = $this->getPageSandboxInfo($page);
        
        if (!$sandboxInfo['has_sandbox'] || !$sandboxInfo['has_custom_screen']) {
            return null;
        }

        try {
            $screenId = $sandboxInfo['custom_screen_folder'];
            // 새로운 컨테이너 기반 템플릿 경로 사용  
            $storagePath = base_path('sandbox/container/storage-sandbox-template');

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

    /**
     * 프로젝트에 샌드박스가 설정되어 있는지 확인합니다.
     */
    public function hasProjectSandbox(Project $project): bool
    {
        return !empty($project->sandbox_folder);
    }

    /**
     * 페이지의 커스텀 스크린을 설정합니다.
     */
    public function setCustomScreen(ProjectPage $page, ?string $customScreenFolder): bool
    {
        try {
            if (!empty($customScreenFolder)) {
                // 도메인 정보 추출
                $domain = $this->extractDomainFromScreenFolder($customScreenFolder);
                
                $page->update([
                    'sandbox_custom_screen_folder' => $customScreenFolder,
                    'sandbox_domain' => $domain,
                    'custom_screen_enabled' => true,
                    'custom_screen_applied_at' => now(),
                    'template_path' => null,
                ]);
            } else {
                // 커스텀 화면 비활성화
                $page->update([
                    'sandbox_custom_screen_folder' => null,
                    'sandbox_domain' => null,
                    'custom_screen_enabled' => false,
                    'custom_screen_applied_at' => null,
                    'template_path' => null,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('커스텀 화면 설정 저장 오류', ['error' => $e->getMessage(), 'page_id' => $page->id]);
            return false;
        }
    }

    /**
     * 커스텀 화면 폴더명에서 도메인 정보를 추출합니다.
     */
    private function extractDomainFromScreenFolder(?string $customScreenFolder): ?string
    {
        if (empty($customScreenFolder)) {
            return null;
        }

        try {
            $templatePath = base_path('sandbox/container/storage-sandbox-template');
            
            if (!File::exists($templatePath)) {
                return null;
            }

            // 도메인별로 검색해서 해당 화면이 있는 도메인을 찾음
            $domainFolders = File::directories($templatePath);
            
            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);
                $screenFolders = File::directories($domainFolder);
                
                foreach ($screenFolders as $screenFolder) {
                    $screenName = basename($screenFolder);
                    if ($screenName === $customScreenFolder) {
                        return $domainName;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('도메인 추출 오류', ['error' => $e->getMessage(), 'screen_folder' => $customScreenFolder]);
            return null;
        }
    }
}