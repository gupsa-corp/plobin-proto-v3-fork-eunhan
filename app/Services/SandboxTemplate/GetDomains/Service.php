<?php

namespace App\Services\SandboxTemplate\GetDomains;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(string $sandboxName): array
    {
        $domains = [];

        if (empty($sandboxName)) {
            return $domains;
        }

        try {
            $getTemplatePathService = app(\App\Services\SandboxTemplate\GetTemplatePath\Service::class);
            $templatePath = $getTemplatePathService($sandboxName);

            if (!File::exists($templatePath)) {
                return $domains;
            }

            // 도메인 폴더 검색 (예: 100-domain-pms, 101-domain-rfx)
            $domainFolders = File::directories($templatePath);

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);

                // 도메인 폴더가 아닌 경우 건너뛰기
                if (!preg_match('/^(\d+)-domain-(.+)$/', $domainName, $matches)) {
                    continue;
                }

                $domainId = $matches[1];
                $domainSlug = $matches[2];

                $domains[] = [
                    'folder' => $domainName,
                    'title' => $this->formatDomainTitle($domainSlug),
                    'id' => $domainId,
                    'slug' => $domainSlug
                ];
            }

            // ID 순으로 정렬
            usort($domains, function($a, $b) {
                return (int)$a['id'] - (int)$b['id'];
            });

        } catch (\Exception $e) {
            Log::error('도메인 목록 로드 오류', [
                'error' => $e->getMessage(),
                'sandbox_folder' => $sandboxName
            ]);
        }

        return $domains;
    }

    private function formatDomainTitle(string $slug): string
    {
        $titleMap = [
            'pms' => 'Domain PMS',
            'rfx' => 'Domain RFX',
            'crm' => 'Domain CRM',
            'hrm' => 'Domain HRM'
        ];

        return $titleMap[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
    }
}