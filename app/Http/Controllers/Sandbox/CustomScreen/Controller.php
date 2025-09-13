<?php

namespace App\Http\Controllers\Sandbox\CustomScreen;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;

class Controller extends \App\Http\Controllers\Controller
{
    public function show($id)
    {
        // 템플릿 경로에서 해당 스크린 파일 찾기
        $templatePath = storage_path('sandbox/storage-sandbox-template/frontend');
        $screenPath = null;
        
        if (File::exists($templatePath)) {
            $folders = File::directories($templatePath);
            
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                $contentFile = $folder . '/000-content.blade.php';
                
                if (File::exists($contentFile)) {
                    // 폴더명에서 화면 ID 추출
                    $parts = explode('-', $folderName, 3);
                    $screenId = $parts[0] ?? '000';
                    
                    if ($screenId === $id) {
                        $screenPath = $contentFile;
                        break;
                    }
                }
            }
        }
        
        if (!$screenPath || !File::exists($screenPath)) {
            return response('템플릿 파일을 찾을 수 없습니다.', 404);
        }
        
        // 템플릿 파일 내용 읽기
        $templateContent = File::get($screenPath);
        
        // 샘플 데이터 설정
        $sampleData = [
            'title' => '샘플 화면',
            'users' => collect([
                ['id' => 1, 'name' => '홍길동', 'email' => 'hong@example.com', 'status' => 'active'],
                ['id' => 2, 'name' => '김철수', 'email' => 'kim@example.com', 'status' => 'inactive'],
                ['id' => 3, 'name' => '이영희', 'email' => 'lee@example.com', 'status' => 'active']
            ]),
            'projects' => collect([
                ['id' => 1, 'name' => '프로젝트 A', 'status' => 'active', 'progress' => 75],
                ['id' => 2, 'name' => '프로젝트 B', 'status' => 'pending', 'progress' => 30],
                ['id' => 3, 'name' => '프로젝트 C', 'status' => 'completed', 'progress' => 100]
            ]),
            'organizations' => collect([
                ['id' => 1, 'name' => '샘플 조직 1', 'members' => 15],
                ['id' => 2, 'name' => '샘플 조직 2', 'members' => 8],
            ])
        ];
        
        try {
            // 임시 블레이드 파일 생성
            $tempViewPath = 'sandbox-raw-' . time() . '-' . rand(1000, 9999);
            $tempViewFile = resource_path('views/' . $tempViewPath . '.blade.php');
            
            File::put($tempViewFile, $templateContent);
            
            try {
                // 블레이드 템플릿 렌더링
                $renderedContent = view($tempViewPath, $sampleData)->render();
                
                // HTML 문서로 감싸서 반환 (CSRF 토큰 포함)
                $csrfToken = csrf_token();
                $html = '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="' . $csrfToken . '">
    <title>템플릿 미리보기</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: "Malgun Gothic", "맑은 고딕", sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-7xl mx-auto">
        ' . $renderedContent . '
    </div>
</body>
</html>';
                
                return response($html)->header('Content-Type', 'text/html; charset=utf-8');
                
            } catch (\Exception $e) {
                return response('렌더링 오류: ' . $e->getMessage(), 500);
            } finally {
                // 임시 파일 삭제
                if (File::exists($tempViewFile)) {
                    File::delete($tempViewFile);
                }
            }
            
        } catch (\Exception $e) {
            return response('템플릿 처리 오류: ' . $e->getMessage(), 500);
        }
    }
    
    public function showByPath($storageName, $screenFolderName)
    {
        // 스토리지 경로에서 해당 화면 폴더 찾기
        $templatePath = storage_path("sandbox/{$storageName}/frontend");
        $screenPath = $templatePath . '/' . $screenFolderName . '/000-content.blade.php';
        
        if (!File::exists($screenPath)) {
            return response('템플릿 파일을 찾을 수 없습니다.', 404);
        }
        
        // 템플릿 파일 내용 읽기
        $templateContent = File::get($screenPath);
        
        // 샘플 데이터 설정
        $sampleData = [
            'title' => '샘플 화면',
            'users' => collect([
                ['id' => 1, 'name' => '홍길동', 'email' => 'hong@example.com', 'status' => 'active'],
                ['id' => 2, 'name' => '김철수', 'email' => 'kim@example.com', 'status' => 'inactive'],
                ['id' => 3, 'name' => '이영희', 'email' => 'lee@example.com', 'status' => 'active']
            ]),
            'projects' => collect([
                ['id' => 1, 'name' => '프로젝트 A', 'status' => 'active', 'progress' => 75],
                ['id' => 2, 'name' => '프로젝트 B', 'status' => 'pending', 'progress' => 30],
                ['id' => 3, 'name' => '프로젝트 C', 'status' => 'completed', 'progress' => 100]
            ]),
            'organizations' => collect([
                ['id' => 1, 'name' => '샘플 조직 1', 'members' => 15],
                ['id' => 2, 'name' => '샘플 조직 2', 'members' => 8],
            ])
        ];
        
        try {
            // 임시 블레이드 파일 생성
            $tempViewPath = 'sandbox-raw-path-' . time() . '-' . rand(1000, 9999);
            $tempViewFile = resource_path('views/' . $tempViewPath . '.blade.php');
            
            File::put($tempViewFile, $templateContent);
            
            try {
                // 블레이드 템플릿 렌더링
                $renderedContent = view($tempViewPath, $sampleData)->render();
                
                // HTML 문서로 감싸서 반환 (CSRF 토큰 포함)
                $csrfToken = csrf_token();
                $html = '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="' . $csrfToken . '">
    <title>템플릿 미리보기 - ' . $screenFolderName . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: "Malgun Gothic", "맑은 고딕", sans-serif; }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-7xl mx-auto">
        ' . $renderedContent . '
    </div>
</body>
</html>';
                
                return response($html)->header('Content-Type', 'text/html; charset=utf-8');
                
            } catch (\Exception $e) {
                return response('렌더링 오류: ' . $e->getMessage(), 500);
            } finally {
                // 임시 파일 삭제
                if (File::exists($tempViewFile)) {
                    File::delete($tempViewFile);
                }
            }
            
        } catch (\Exception $e) {
            return response('템플릿 처리 오류: ' . $e->getMessage(), 500);
        }
    }
}