<?php

namespace App\Services\Sandbox\DomainList;

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

            // 도메인 폴더들을 스캔
            $domainFolders = File::directories($templatePath);
            $domains = [];

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);

                // 도메인 폴더 형식 검증 (*-domain-* 패턴)
                if (preg_match('/^\d+-domain-.+/', $domainName)) {
                    // 도메인명에서 정보 추출
                    $parts = explode('-', $domainName, 3);
                    $domainId = $parts[0] ?? '000';
                    $domainTitle = $parts[2] ?? 'unnamed';

                    // 해당 도메인의 화면 개수 계산
                    $screenFolders = File::directories($domainFolder);
                    $screenCount = 0;

                    foreach ($screenFolders as $screenFolder) {
                        $screenName = basename($screenFolder);
                        if (preg_match('/^\d+-screen-.+/', $screenName)) {
                            $contentFile = $screenFolder . '/000-content.blade.php';
                            if (File::exists($contentFile)) {
                                $screenCount++;
                            }
                        }
                    }

                    $domains[] = [
                        'id' => $domainName,
                        'name' => $domainName,
                        'title' => str_replace('-', ' ', ucfirst($domainTitle)),
                        'display_name' => $domainId . ' Domain ' . ucfirst(str_replace('-', ' ', $domainTitle)),
                        'screen_count' => $screenCount,
                        'created_at' => date('Y-m-d H:i:s', File::lastModified($domainFolder)),
                    ];
                }
            }

            // 도메인 ID 기준으로 정렬
            usort($domains, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $domains;

        } catch (\Exception $e) {
            Log::error('도메인 목록 로드 오류', ['error' => $e->getMessage(), 'sandbox_name' => $sandboxName]);
            return [];
        }
    }
}