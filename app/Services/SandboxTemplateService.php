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

        return storage_path("sandbox/{$sandboxName}/frontend");
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

            $folders = File::directories($templatePath);

            foreach ($folders as $folder) {
                $folderName = basename($folder);
                $contentFile = $folder . '/000-content.blade.php';

                if (File::exists($contentFile)) {
                    // 폴더명에서 화면 정보 추출
                    $parts = explode('-', $folderName, 3);
                    $screenId = $parts[0] ?? '000';
                    $screenType = $parts[1] ?? 'screen';
                    $screenName = $parts[2] ?? 'unnamed';

                    // 파일 내용 읽기
                    $fileContent = File::get($contentFile);

                    $customScreens[] = [
                        'id' => $folderName, // 전체 폴더명을 사용해서 고유한 ID 생성
                        'title' => str_replace('-', ' ', $screenName),
                        'description' => '템플릿 화면 - ' . str_replace('-', ' ', $screenName),
                        'type' => $screenType,
                        'folder_name' => $folderName,
                        'file_path' => 'frontend/' . $folderName . '/000-content.blade.php',
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
}