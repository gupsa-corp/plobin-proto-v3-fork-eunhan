<?php

namespace App\Services\Sandbox\CustomScreenList;

use App\Services\SandboxContextService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(?string $sandboxName = null): array
    {
        if (empty($sandboxName)) {
            return [];
        }

        try {
            $sandboxContextService = app(SandboxContextService::class);

            // 동적 샌드박스 경로 사용
            $templatePath = $sandboxContextService->getSandboxPath();

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
}