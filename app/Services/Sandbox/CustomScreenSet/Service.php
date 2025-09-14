<?php

namespace App\Services\Sandbox\CustomScreenSet;

use App\Models\ProjectPage;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(ProjectPage $page, ?string $customScreenFolder): bool
    {
        try {
            if (!empty($customScreenFolder)) {
                // 도메인 정보 추출
                $domain = app(\App\Services\Sandbox\DomainExtract\Service::class)($customScreenFolder);

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
}