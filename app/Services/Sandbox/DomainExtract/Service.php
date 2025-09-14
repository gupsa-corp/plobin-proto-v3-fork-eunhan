<?php

namespace App\Services\Sandbox\DomainExtract;

use App\Services\SandboxContextService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(?string $customScreenFolder): ?string
    {
        if (empty($customScreenFolder)) {
            return null;
        }

        try {
            $sandboxContextService = app(SandboxContextService::class);
            $templatePath = $sandboxContextService->getSandboxPath();

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