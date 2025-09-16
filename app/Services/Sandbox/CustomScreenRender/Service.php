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

            // screenId가 도메인과 화면을 모두 포함하는 경우 분리
            $targetDomain = null;
            $targetScreenName = null;

            if (preg_match('/(\d+-domain-[^-]+)-(\d+-screen-.+)/', $screenId, $matches)) {
                // 예: 101-domain-rfx-101-screen-multi-file-upload
                $targetDomain = $matches[1];
                $targetScreenName = $matches[2];
            } elseif (preg_match('/(\d+-domain-[^\/]+)\/(\d+-screen-.+)/', $screenId, $matches)) {
                // 예: 101-domain-rfx/101-screen-multi-file-upload
                $targetDomain = $matches[1];
                $targetScreenName = $matches[2];
            } else {
                // 기존 형식 (화면 ID만 있는 경우)
                $targetScreenName = $screenId;
            }

            // 샌드박스 경로 확인 (container 경로와 storage 경로 둘 다 시도)
            $sandboxName = $sandboxInfo['sandbox_name'];
            $containerPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandboxName}");
            $storagePath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/{$sandboxName}");

            // container 경로를 우선 확인, 없으면 storage 경로 확인
            if (File::exists($containerPath)) {
                $storagePath = $containerPath;
            } elseif (!File::exists($storagePath)) {
                return null;
            }

            $targetContentFile = null;

            // 도메인과 화면이 모두 지정된 경우 직접 경로 확인
            if ($targetDomain && $targetScreenName) {
                $contentFile = $storagePath . '/' . $targetDomain . '/' . $targetScreenName . '/000-content.blade.php';
                if (File::exists($contentFile)) {
                    $targetContentFile = $contentFile;
                }
            } else {
                // 도메인별로 검색 (기존 로직)
                $domainFolders = File::directories($storagePath);

                foreach ($domainFolders as $domainFolder) {
                    $domainName = basename($domainFolder);
                    $screenFolders = File::directories($domainFolder);

                    foreach ($screenFolders as $screenFolder) {
                        $screenName = basename($screenFolder);
                        $contentFile = $screenFolder . '/000-content.blade.php';

                        // 화면 ID가 일치하는지 확인 (정확한 매치 우선, 다양한 번호 패턴 지원)
                        if ($screenName === $targetScreenName) {
                            $targetContentFile = $contentFile;
                            $targetDomain = $domainName;
                            $targetScreenName = $screenName;
                            break 2;
                        }
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

                    // PHP 태그와 require/use 문 제거 (전체 PHP 블록 제거)
                    $templateContent = preg_replace('/<\?php.*?require_once.*?\?>/ms', '', $templateContent);
                    $templateContent = preg_replace('/<\?php.*?use\s+App\\\\Services\\\\TemplateCommonService;.*?\?>/ms', '', $templateContent);

                    // TemplateCommonService 관련 PHP 코드 블록 전체 제거
                    $templateContent = preg_replace('/<\?php[^>]*TemplateCommonService[^>]*\?>/ms', '', $templateContent);

                    // 남은 PHP 코드 블록 제거 (변수 초기화 등)
                    $templateContent = preg_replace('/<\?php\s+\$screenInfo\s*=.*?\?>/ms', '', $templateContent);
                    $templateContent = preg_replace('/<\?php\s+\$uploadPaths\s*=.*?\?>/ms', '', $templateContent);

                    // 빈 PHP 블록 및 빈 줄 정리
                    $templateContent = preg_replace('/<\?php\s*\?>/s', '', $templateContent);
                    $templateContent = preg_replace('/^\s*$/m', '', $templateContent);
                    $templateContent = preg_replace('/\n{3,}/', "\n\n", $templateContent);

                    // PHP echo 태그를 Blade 구문으로 변환
                    $templateContent = preg_replace('/<\?=\s*(.+?)\s*\?>/s', '{{ $1 }}', $templateContent);
                    $templateContent = preg_replace('/<\?php\s+echo\s+(.+?);\s*\?>/s', '{{ $1 }}', $templateContent);

                    File::put($tempViewFile, $templateContent);

                    try {
                        // screenInfo와 uploadPaths 변수 준비
                        $screenInfo = [
                            'sandbox' => $sandboxInfo['sandbox_name'],
                            'domain' => $targetDomain,
                            'screen' => $targetScreenName,
                            'title' => ucwords(str_replace('-', ' ', $targetScreenName)),
                            'path' => request()->path()
                        ];

                        $uploadPaths = [
                            'upload' => '/sandbox/upload',
                            'temp' => '/sandbox/temp',
                            'download' => '/sandbox/download',
                            'storage' => storage_path('app/sandbox')
                        ];


                        // 실제 프로젝트 데이터 사용
                        $viewData = [
                            'title' => $page->title ?? str_replace('-', ' ', $screenTitle),
                            'description' => $page->content ?? '프로젝트 페이지',
                            'organization' => $page->project->organization,
                            'project' => $page->project,
                            'page' => $page,
                            'organizations' => collect([$page->project->organization]),
                            'projects' => collect([$page->project]),
                            'users' => collect([]),
                            'activities' => collect([]),
                            'screenInfo' => $screenInfo,
                            'uploadPaths' => $uploadPaths,
                            // helper 함수들을 뷰 데이터로 전달
                            'getScreenUrl' => function($domain, $screen) use ($sandboxInfo) {
                                return "/sandbox/{$sandboxInfo['sandbox_name']}/{$domain}/{$screen}";
                            },
                            'getFileIcon' => function($mimeType) {
                                $icons = [
                                    'application/pdf' => '📄',
                                    'application/msword' => '📝',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '📝',
                                    'application/vnd.ms-excel' => '📊',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '📊',
                                    'image/jpeg' => '🖼️',
                                    'image/png' => '🖼️',
                                    'image/gif' => '🖼️',
                                    'application/zip' => '📦',
                                    'text/plain' => '📄'
                                ];
                                return $icons[$mimeType] ?? '📄';
                            },
                            'formatFileSize' => function($bytes) {
                                if ($bytes < 1024) return $bytes . ' B';
                                elseif ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
                                elseif ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
                                else return round($bytes / 1073741824, 2) . ' GB';
                            },
                            'getFileCategory' => function($mimeType) {
                                if (strpos($mimeType, 'image/') === 0) return 'image';
                                if (strpos($mimeType, 'video/') === 0) return 'video';
                                if (strpos($mimeType, 'audio/') === 0) return 'audio';
                                if (in_array($mimeType, ['application/pdf', 'application/msword', 'text/plain'])) return 'document';
                                if (in_array($mimeType, ['application/zip', 'application/x-rar-compressed'])) return 'archive';
                                return 'other';
                            },
                            'getFileTypeLabel' => function($mimeType) {
                                $labels = [
                                    'application/pdf' => 'PDF',
                                    'application/msword' => 'DOC',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
                                    'application/vnd.ms-excel' => 'XLS',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
                                    'image/jpeg' => 'JPG',
                                    'image/png' => 'PNG',
                                    'image/gif' => 'GIF',
                                    'application/zip' => 'ZIP',
                                    'text/plain' => 'TXT'
                                ];
                                return $labels[$mimeType] ?? 'FILE';
                            },
                            'getFileTypeBadge' => function($mimeType) {
                                $getFileCategory = function($mimeType) {
                                    if (strpos($mimeType, 'image/') === 0) return 'image';
                                    if (strpos($mimeType, 'video/') === 0) return 'video';
                                    if (strpos($mimeType, 'audio/') === 0) return 'audio';
                                    if (in_array($mimeType, ['application/pdf', 'application/msword', 'text/plain'])) return 'document';
                                    if (in_array($mimeType, ['application/zip', 'application/x-rar-compressed'])) return 'archive';
                                    return 'other';
                                };
                                $category = $getFileCategory($mimeType);
                                $badges = [
                                    'image' => 'bg-green-100 text-green-800',
                                    'document' => 'bg-blue-100 text-blue-800',
                                    'video' => 'bg-purple-100 text-purple-800',
                                    'audio' => 'bg-yellow-100 text-yellow-800',
                                    'archive' => 'bg-gray-100 text-gray-800',
                                    'other' => 'bg-gray-100 text-gray-600'
                                ];
                                return $badges[$category] ?? 'bg-gray-100 text-gray-600';
                            },
                            'formatDate' => function($date) {
                                return date('Y-m-d H:i', strtotime($date));
                            }
                        ];

                        $renderedContent = view($tempViewPath, $viewData)->render();
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