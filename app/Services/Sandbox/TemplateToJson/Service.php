<?php

namespace App\Services\Sandbox\TemplateToJson;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(): array
    {
        try {
            $templatePath = storage_path('sandbox/container/storage-sandbox-template');

            if (!File::exists($templatePath)) {
                return ['domains' => []];
            }

            $domainFolders = File::directories($templatePath);
            $domains = [];

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);

                // 도메인 폴더만 처리 (숫자-domain-이름 패턴)
                if (preg_match('/^\d+-domain-.+/', $domainName)) {
                    $domain = $this->processDomain($domainFolder, $domainName);
                    if ($domain) {
                        $domains[] = $domain;
                    }
                }
            }

            // 도메인 ID 기준으로 정렬
            usort($domains, function($a, $b) {
                return strcmp($a['id'], $b['id']);
            });

            return [
                'domains' => $domains,
                'generated_at' => now()->toISOString(),
                'total_domains' => count($domains),
                'total_screens' => array_sum(array_column($domains, 'screen_count'))
            ];

        } catch (\Exception $e) {
            Log::error('템플릿 JSON 변환 오류', ['error' => $e->getMessage()]);
            return ['domains' => [], 'error' => $e->getMessage()];
        }
    }

    private function processDomain(string $domainPath, string $domainName): ?array
    {
        try {
            // 도메인명에서 정보 추출
            $parts = explode('-', $domainName, 3);
            $domainId = $parts[0] ?? '000';
            $domainTitle = $parts[2] ?? 'unnamed';

            $screenFolders = File::directories($domainPath);
            $screens = [];

            foreach ($screenFolders as $screenFolder) {
                $screenName = basename($screenFolder);

                // 스크린 폴더만 처리 (숫자-screen-이름 패턴)
                if (preg_match('/^\d+-screen-.+/', $screenName)) {
                    $screen = $this->processScreen($screenFolder, $screenName, $domainName);
                    if ($screen) {
                        $screens[] = $screen;
                    }
                }
            }

            // 화면 ID 기준으로 정렬
            usort($screens, function($a, $b) {
                return strcmp($a['id'], $b['id']);
            });

            return [
                'id' => $domainName,
                'domain_id' => $domainId,
                'name' => $domainTitle,
                'title' => str_replace('-', ' ', ucfirst($domainTitle)),
                'display_name' => $domainId . ' Domain ' . ucfirst(str_replace('-', ' ', $domainTitle)),
                'screen_count' => count($screens),
                'screens' => $screens,
                'created_at' => date('Y-m-d H:i:s', File::lastModified($domainPath)),
                'path' => $domainPath
            ];

        } catch (\Exception $e) {
            Log::error('도메인 처리 오류', ['error' => $e->getMessage(), 'domain' => $domainName]);
            return null;
        }
    }

    private function processScreen(string $screenPath, string $screenName, string $domainName): ?array
    {
        try {
            // 스크린명에서 정보 추출
            $parts = explode('-', $screenName, 3);
            $screenId = $parts[0] ?? '000';
            $screenTitle = $parts[2] ?? 'unnamed';

            // 파일 목록 수집
            $files = [];
            if (File::isDirectory($screenPath)) {
                $allFiles = File::files($screenPath);
                foreach ($allFiles as $file) {
                    $fileName = $file->getFilename();
                    $files[] = [
                        'name' => $fileName,
                        'size' => $file->getSize(),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                        'extension' => $file->getExtension(),
                        'is_blade' => str_ends_with($fileName, '.blade.php')
                    ];
                }
            }

            // 주요 파일 존재 여부 확인
            $contentFile = $screenPath . '/000-content.blade.php';
            $indexFile = $screenPath . '/index.blade.php';
            $hasContent = File::exists($contentFile);
            $hasIndex = File::exists($indexFile);

            return [
                'id' => $screenName,
                'screen_id' => $screenId,
                'name' => $screenTitle,
                'title' => str_replace('-', ' ', ucfirst($screenTitle)),
                'display_name' => $screenId . '-screen-' . $screenTitle,
                'domain' => $domainName,
                'type' => 'screen',
                'has_content_file' => $hasContent,
                'has_index_file' => $hasIndex,
                'file_count' => count($files),
                'files' => $files,
                'created_at' => date('Y-m-d H:i:s', File::lastModified($screenPath)),
                'path' => $screenPath
            ];

        } catch (\Exception $e) {
            Log::error('스크린 처리 오류', ['error' => $e->getMessage(), 'screen' => $screenName]);
            return null;
        }
    }
}