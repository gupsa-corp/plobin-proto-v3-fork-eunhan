<?php

namespace App\Services\Sandbox\ScreenContentLoad;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(?string $sandboxName, string $domainName, string $screenName): array
    {
        try {
            $sandboxPath = storage_path("sandbox/{$sandboxName}");
            $domainPath = "{$sandboxPath}/{$domainName}";
            $screenPath = "{$domainPath}/{$screenName}";

            // 도메인 경로 체크
            if (!File::exists($domainPath)) {
                $availableDomains = File::exists($sandboxPath) ? array_map('basename', File::directories($sandboxPath)) : [];
                throw new \Exception("도메인 '{$domainName}'을 찾을 수 없습니다. 사용 가능한 도메인: " . implode(', ', $availableDomains));
            }

            // 화면 경로 체크
            if (!File::exists($screenPath)) {
                $availableScreens = array_map('basename', File::directories($domainPath));
                throw new \Exception("화면 '{$screenName}'을 찾을 수 없습니다. 사용 가능한 화면: " . implode(', ', $availableScreens));
            }

            // 000-content.blade.php 파일 로드
            $contentFile = "{$screenPath}/000-content.blade.php";
            $content = '';

            if (File::exists($contentFile)) {
                $content = File::get($contentFile);
            } else {
                throw new \Exception("콘텐츠 파일이 존재하지 않습니다: {$contentFile}");
            }

            // 화면 정보 수집
            $screenInfo = [
                'folder_name' => $screenName,
                'domain' => $domainName,
                'title' => app(\App\Services\Sandbox\ScreenTitleGenerate\Service::class)($screenName),
                'description' => "도메인 {$domainName}의 {$screenName} 화면",
                'type' => 'custom',
                'created_at' => File::exists($screenPath) ? date('Y-m-d H:i:s', File::lastModified($screenPath)) : null,
                'content' => $content,
                'has_content' => !empty($content)
            ];

            return $screenInfo;

        } catch (\Exception $e) {
            Log::error('화면 콘텐츠 로드 오류', [
                'error' => $e->getMessage(),
                'sandbox_name' => $sandboxName,
                'domain_name' => $domainName,
                'screen_name' => $screenName,
                'sandbox_path' => $sandboxPath ?? 'undefined',
                'screen_path' => $screenPath ?? 'undefined'
            ]);
            throw $e;
        }
    }
}