<?php

namespace App\Http\Controllers\Sandbox\ListScreens;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Services\SandboxContextService;

class Controller extends \App\Http\Controllers\Controller
{
    protected $sandboxContextService;

    public function __construct(SandboxContextService $sandboxContextService)
    {
        $this->sandboxContextService = $sandboxContextService;
    }
    /**
     * 선택된 샌드박스의 화면(스크린) 목록을 반환
     */
    public function listScreens(Request $request)
    {
        $sandboxName = $request->get('sandbox_folder');

        if (!$sandboxName) {
            return response()->json(['error' => '샌드박스 이름이 필요합니다.'], 400);
        }

        // 템플릿 폴더인 경우 특별 처리
        try {
            $currentSandbox = $this->sandboxContextService->getCurrentSandbox();
            if ($sandboxName === $currentSandbox) {
                return $this->listCurrentSandboxScreens();
            }
        } catch (\Exception $e) {
            // 샌드박스가 설정되지 않은 경우 기본 처리
        }

        $sandboxPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $sandboxName);

        // 샌드박스 디렉토리가 존재하는지 확인
        if (!File::exists($sandboxPath)) {
            return response()->json(['error' => '샌드박스가 존재하지 않습니다.'], 404);
        }

        try {
            $screens = $this->findScreenFiles($sandboxPath);

            return response()->json([
                'sandbox_folder' => $sandboxName,
                'screens' => $screens,
                'total_count' => count($screens)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '스크린 목록을 가져오는 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * 특정 샌드박스의 화면(스크린) 목록을 반환 (URL 파라미터로)
     */
    public function listScreensBySandbox(Request $request, $sandboxName)
    {
        // storage/sandbox 경로 사용
        $sandboxFrontendPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/' . $sandboxName);

        // 샌드박스 디렉토리가 존재하는지 확인
        if (!File::exists($sandboxFrontendPath)) {
            return response()->json(['error' => '샌드박스 폴더가 존재하지 않습니다.'], 404);
        }

        try {
            $screens = $this->findScreenFolders($sandboxFrontendPath);

            return response()->json([
                'sandbox_folder' => $sandboxName,
                'screens' => $screens,
                'total_count' => count($screens)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '스크린 목록을 가져오는 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * frontend 폴더에서 스크린 폴더들을 찾아서 반환
     */
    private function findScreenFolders($frontendPath)
    {
        $screens = [];

        // frontend 폴더의 모든 디렉토리 스캔
        $directories = File::directories($frontendPath);

        foreach ($directories as $directory) {
            $folderName = basename($directory);

            // 001-screen-xxx 형식의 폴더인지 확인
            if (preg_match('/^\d{3}-screen-.+$/', $folderName)) {
                $screens[] = [
                    'folder_name' => $folderName
                ];
            }
        }

        // 폴더명으로 정렬
        usort($screens, function($a, $b) {
            return strcmp($a['folder_name'], $b['folder_name']);
        });

        return $screens;
    }

    /**
     * 샌드박스에서 화면 파일들을 찾아서 반환 (기존 방식 유지)
     */
    private function findScreenFiles($sandboxPath)
    {
        $screens = [];
        $extensions = ['html', 'php']; // 화면으로 간주할 파일 확장자

        // 재귀적으로 모든 화면 파일 찾기
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sandboxPath),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                $filename = $file->getBasename();

                // 화면 파일인지 확인
                if (in_array($extension, $extensions)) {
                    $relativePath = str_replace($sandboxPath . '/', '', $file->getPathname());
                    $directory = dirname($relativePath);

                    // 파일 내용에서 제목 추출 시도
                    $title = $this->extractTitle($file->getPathname());
                    if (!$title) {
                        $title = $this->generateTitleFromFilename($filename, $directory);
                    }

                    $screens[] = [
                        'id' => md5($relativePath), // 파일 경로 기반 고유 ID
                        'filename' => $filename,
                        'title' => $title,
                        'path' => $relativePath,
                        'directory' => $directory === '.' ? '/' : $directory,
                        'type' => $extension,
                        'size' => $this->formatBytes($file->getSize()),
                        'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'description' => $this->generateDescription($filename, $directory)
                    ];
                }
            }
        }

        // 경로 및 파일명으로 정렬
        usort($screens, function($a, $b) {
            $pathCompare = strcmp($a['directory'], $b['directory']);
            if ($pathCompare === 0) {
                return strcmp($a['filename'], $b['filename']);
            }
            return $pathCompare;
        });

        return $screens;
    }

    /**
     * HTML 파일에서 제목 추출
     */
    private function extractTitle($filePath)
    {
        try {
            $content = File::get($filePath);

            // HTML title 태그에서 제목 추출
            if (preg_match('/<title[^>]*>(.*?)<\/title>/i', $content, $matches)) {
                return trim(strip_tags($matches[1]));
            }

            // HTML h1 태그에서 제목 추출
            if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $content, $matches)) {
                return trim(strip_tags($matches[1]));
            }

            // PHP 주석에서 제목 추출
            if (preg_match('/\/\*\*?\s*\*?\s*(.+?)\s*\*?\s*\*?\//s', $content, $matches)) {
                return trim($matches[1]);
            }

        } catch (\Exception $e) {
            // 파일을 읽을 수 없는 경우 무시
        }

        return null;
    }

    /**
     * 파일명과 디렉토리에서 제목 생성 (설정 페이지와 동일한 로직 사용)
     */
    private function generateTitleFromFilename($filename, $directory = null)
    {
        // 디렉토리 기반 제목 생성 (우선순위)
        if ($directory && $directory !== '.' && $directory !== '/') {
            // frontend 폴더 하위의 패턴: 001-screen-dashboard 형식
            $folderName = basename($directory);
            if (preg_match('/^\d+-screen-(.+)$/', $folderName, $matches)) {
                $screenName = str_replace('-', ' ', $matches[1]);
                return $screenName;
            }

            // backend 폴더의 경우
            if (strpos($directory, 'backend') !== false) {
                $folderName = basename($directory);
                return str_replace('-', ' ', $folderName);
            }
        }

        $name = pathinfo($filename, PATHINFO_FILENAME);

        // 특별한 파일명들 처리
        $specialNames = [
            'index' => '메인 페이지',
            'main' => '메인 화면',
            'home' => '홈 화면',
            'dashboard' => '대시보드',
            'form-publisher-save' => '폼 발행 저장',
            'form-publisher-preview' => '폼 발행 미리보기',
            'form-publisher-list' => '폼 발행 목록',
            'form-publisher-editor' => '폼 발행 편집기',
            'gantt' => '간트 차트'
        ];

        if (isset($specialNames[$name])) {
            return $specialNames[$name];
        }

        // 하이픈과 언더스코어를 공백으로 변경하고 첫 글자 대문자화
        $title = str_replace(['-', '_'], ' ', $name);
        return ucwords($title);
    }

    /**
     * 파일 설명 생성
     */
    private function generateDescription($filename, $directory)
    {
        $descriptions = [
            'index' => '기본 시작 페이지입니다',
            'form-publisher' => '폼 발행 관련 화면입니다',
            'gantt' => '간트 차트 화면입니다',
            'dashboard' => '대시보드 화면입니다'
        ];

        // 파일명 패턴으로 설명 생성
        foreach ($descriptions as $pattern => $desc) {
            if (strpos($filename, $pattern) !== false) {
                return $desc;
            }
        }

        // 디렉토리 기반 설명
        if ($directory !== '/' && $directory !== '.') {
            return "/{$directory} 디렉토리의 화면입니다";
        }

        return "사용자 화면 파일입니다";
    }


    /**
     * 파일 크기 포맷팅
     */
    private function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 1) . ' ' . $units[$unitIndex];
    }

    /**
     * 현재 샌드박스의 스크린 목록을 반환
     */
    private function listCurrentSandboxScreens()
    {
        $templatePath = $this->sandboxContextService->getSandboxPath();

        if (!File::exists($templatePath)) {
            return response()->json(['error' => '샌드박스 폴더가 존재하지 않습니다.'], 404);
        }

        try {
            $screens = $this->findTemplateScreens($templatePath);

            return response()->json([
                'sandbox_folder' => $this->sandboxContextService->getCurrentSandbox(),
                'screens' => $screens,
                'total_count' => count($screens)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '샌드박스 스크린 목록을 가져오는 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * 템플릿 폴더의 스크린 목록을 반환 (레거시)
     */
    private function listTemplateScreens()
    {
        $templatePath = $this->sandboxContextService->getSandboxPath();

        if (!File::exists($templatePath)) {
            return response()->json(['error' => '템플릿 폴더가 존재하지 않습니다.'], 404);
        }

        try {
            $screens = $this->findTemplateScreens($templatePath);

            return response()->json([
                'sandbox_folder' => $this->sandboxContextService->getCurrentSandbox(),
                'screens' => $screens,
                'total_count' => count($screens)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '템플릿 스크린 목록을 가져오는 중 오류가 발생했습니다.'], 500);
        }
    }

    /**
     * 템플릿 폴더에서 스크린들을 찾아서 반환
     */
    private function findTemplateScreens($templatePath)
    {
        $screens = [];

        // 도메인 폴더들 스캔
        $domainDirs = File::directories($templatePath);

        foreach ($domainDirs as $domainDir) {
            $domainName = basename($domainDir);

            // 숫자-domain-xxx 형식의 폴더만 처리
            if (!preg_match('/^\d+-domain-(.+)$/', $domainName)) {
                continue;
            }

            // 각 도메인 폴더 내의 스크린 폴더들 찾기
            $screenDirs = File::directories($domainDir);

            foreach ($screenDirs as $screenDir) {
                $screenName = basename($screenDir);

                // 숫자-screen-xxx 형식의 폴더만 처리
                if (!preg_match('/^\d+-screen-(.+)$/', $screenName, $matches)) {
                    continue;
                }

                // 000-content.blade.php 파일 찾기
                $contentFile = $screenDir . '/000-content.blade.php';

                if (File::exists($contentFile)) {
                    // 고유 ID 생성 (도메인-스크린 형식)
                    $uniqueId = $domainName . '-' . $screenName;
                    
                    // 제목 생성
                    $screenTitle = str_replace('-', ' ', $matches[1]);
                    $domainTitle = str_replace('-', ' ', basename($domainName, '-domain'));

                    $screens[] = [
                        'id' => $uniqueId,
                        'filename' => '000-content.blade.php',
                        'title' => ucwords($screenTitle),
                        'path' => $domainName . '/' . $screenName . '/000-content.blade.php',
                        'directory' => $domainName . '/' . $screenName,
                        'type' => 'blade',
                        'size' => $this->formatBytes(File::size($contentFile)),
                        'modified_at' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                        'description' => ucwords($domainTitle) . ' 도메인의 ' . ucwords($screenTitle) . ' 템플릿 화면입니다',
                        'domain' => $domainName,
                        'screen' => $screenName,
                        'is_template' => true
                    ];
                }
            }
        }

        // ID 기준으로 정렬
        usort($screens, function($a, $b) {
            return strcmp($a['id'], $b['id']);
        });

        return $screens;
    }
}
