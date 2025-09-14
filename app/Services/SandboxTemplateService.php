<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SandboxTemplateService
{
    /**
     * 샌드박스별 템플릿 경로 반환
     */
    public function getTemplatePath(string $sandboxName): string
    {
        if (empty($sandboxName)) {
            return '';
        }

        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandboxName}");
    }

    /**
     * 샌드박스 존재 여부 확인
     */
    public function validateSandboxExists(string $sandboxName): bool
    {
        if (empty($sandboxName)) {
            return false;
        }

        $templatePath = $this->getTemplatePath($sandboxName);
        return File::exists($templatePath);
    }

    /**
     * 커스텀 화면 목록 반환
     */
    public function getCustomScreens(string $sandboxName): array
    {
        $customScreens = [];
        
        if (empty($sandboxName)) {
            return $customScreens;
        }

        try {
            $templatePath = $this->getTemplatePath($sandboxName);

            if (!File::exists($templatePath)) {
                return $customScreens;
            }

            // 도메인 폴더 검색 (예: 100-domain-pms, 101-domain-rfx)
            $domainFolders = File::directories($templatePath);

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);
                
                // 도메인 폴더가 아닌 경우 건너뛰기
                if (!preg_match('/^\d+-domain-/', $domainName)) {
                    continue;
                }
                
                // 도메인 내 화면 폴더 검색 (예: 103-screen-table-view)
                $screenFolders = File::directories($domainFolder);
                
                foreach ($screenFolders as $screenFolder) {
                    $screenFolderName = basename($screenFolder);
                    $contentFile = $screenFolder . '/000-content.blade.php';

                    if (File::exists($contentFile)) {
                        // 폴더명에서 화면 정보 추출
                        $parts = explode('-', $screenFolderName, 3);
                        $screenId = $parts[0] ?? '000';
                        $screenType = $parts[1] ?? 'screen';
                        $screenName = $parts[2] ?? 'unnamed';

                        // 파일 내용 읽기
                        $fileContent = File::get($contentFile);

                        $customScreens[] = [
                            'id' => $screenFolderName, // 화면 폴더명만 ID로 사용
                            'title' => str_replace('-', ' ', $screenName),
                            'description' => '템플릿 화면 - ' . str_replace('-', ' ', $screenName),
                            'type' => $screenType,
                            'folder_name' => $screenFolderName,
                            'domain_folder' => $domainName,
                            'full_path_name' => $domainName . '/' . $screenFolderName,
                            'file_path' => $domainName . '/' . $screenFolderName . '/000-content.blade.php',
                            'created_at' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                            'file_exists' => true,
                            'full_path' => $contentFile,
                            'file_size' => File::size($contentFile),
                            'file_modified' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                            'is_template' => true,
                            'blade_template' => $fileContent,
                        ];
                    }
                }
            }

            // 생성 날짜 기준 내림차순 정렬
            usort($customScreens, function($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });

        } catch (\Exception $e) {
            Log::error('커스텀 화면 데이터 로드 오류', [
                'error' => $e->getMessage(), 
                'sandbox_folder' => $sandboxName
            ]);
            $customScreens = [];
        }

        return $customScreens;
    }

    /**
     * 샌드박스의 도메인 목록 반환
     */
    public function getDomains(string $sandboxName): array
    {
        $domains = [];
        
        if (empty($sandboxName)) {
            return $domains;
        }

        try {
            $templatePath = $this->getTemplatePath($sandboxName);

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

    /**
     * 도메인 슬러그를 사용자 친화적인 제목으로 변환
     */
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