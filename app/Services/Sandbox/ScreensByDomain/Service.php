<?php

namespace App\Services\Sandbox\ScreensByDomain;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(?string $sandboxName = null, string $domainName = null): array
    {
        if (!$sandboxName || !$domainName) {
            return [];
        }

        try {
            $sandboxPath = storage_path('sandbox/storage-sandbox-template');
            $domainPath = $sandboxPath . '/' . $domainName;

            if (!File::isDirectory($domainPath)) {
                return [];
            }

            $screens = [];
            $screenFolders = File::directories($domainPath);

            foreach ($screenFolders as $screenFolder) {
                $screenName = basename($screenFolder);

                // 스크린 폴더 이름 패턴 확인 (숫자-screen-이름)
                if (preg_match('/^\d+-screen-.+/', $screenName)) {
                    $contentFile = $screenFolder . '/000-content.blade.php';

                    if (File::exists($contentFile)) {
                        // 스크린명에서 정보 추출
                        $parts = explode('-', $screenName, 3);
                        $screenId = $parts[0] ?? '000';
                        $screenTitle = $parts[2] ?? 'unnamed';

                        $screens[] = [
                            'id' => $screenName,
                            'folder_name' => $screenName,
                            'title' => str_replace('-', ' ', ucfirst($screenTitle)),
                            'display_name' => $screenId . '-screen-' . $screenTitle,
                            'domain' => $domainName,
                            'created_at' => date('Y-m-d H:i:s', File::lastModified($screenFolder)),
                        ];
                    }
                }
            }

            // 화면 ID 기준으로 정렬
            usort($screens, function($a, $b) {
                return strcmp($a['folder_name'], $b['folder_name']);
            });

            return $screens;

        } catch (\Exception $e) {
            Log::error('도메인별 화면 목록 로드 오류', [
                'error' => $e->getMessage(),
                'sandbox_name' => $sandboxName,
                'domain_name' => $domainName
            ]);
            return [];
        }
    }
}