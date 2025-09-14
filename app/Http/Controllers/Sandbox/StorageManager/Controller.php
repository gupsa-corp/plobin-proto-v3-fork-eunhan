<?php

namespace App\Http\Controllers\Sandbox\StorageManager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class Controller extends \App\Http\Controllers\Api\Core\Controller
{
    private function getStoragePath()
    {
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox'));
    }

    private function getTemplateStoragePath($templateName = 'default')
    {
        return storage_path(env('SANDBOX_TEMPLATE_PATH', 'sandbox/template') . '/' . $templateName);
    }

    private function getTemplateBasePath()
    {
        return storage_path(env('SANDBOX_TEMPLATE_PATH', 'sandbox/template'));
    }

    private function getTemplateList()
    {
        $templates = [];
        $templateBasePath = $this->getTemplateBasePath();

        if (!File::exists($templateBasePath)) {
            return $templates;
        }

        $directories = File::directories($templateBasePath);

        foreach ($directories as $directory) {
            $templateName = basename($directory);
            $templates[] = [
                'name' => $templateName,
                'display_name' => ucfirst($templateName),
                'path' => $directory,
                'file_count' => $this->getFileCount($directory),
                'size' => $this->getDirectorySize($directory),
            ];
        }

        // 이름순 정렬
        usort($templates, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $templates;
    }

    public function index()
    {
        $storages = $this->getStorageList();
        $templates = $this->getTemplateList();
        $currentStorage = Session::get('sandbox_storage', '1');

        // 디버깅 정보 추가
        $debugInfo = [
            'storage_path' => $this->getStoragePath(),
            'cwd' => getcwd(),
            'storage_exists' => File::exists($this->getStoragePath()),
            'directories' => File::exists($this->getStoragePath()) ? File::directories($this->getStoragePath()) : [],
        ];

        return view('700-page-sandbox.707-page-storage-manager.000-index', compact('storages', 'templates', 'currentStorage', 'debugInfo'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'storage_name' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:50',
            'template_name' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:50',
        ], [
            'storage_name.required' => '스토리지 이름을 입력해주세요.',
            'storage_name.regex' => '영문자, 숫자, 하이픈(-), 언더스코어(_)만 사용 가능합니다.',
            'storage_name.max' => '스토리지 이름은 50자를 초과할 수 없습니다.',
            'template_name.required' => '템플릿을 선택해주세요.',
            'template_name.regex' => '잘못된 템플릿 이름입니다.',
        ]);

        $storageName = $request->storage_name;
        $templateName = $request->template_name;
        $targetPath = $this->getStoragePath() . '/' . $storageName;

        // 이미 존재하는지 확인
        if (File::exists($targetPath)) {
            return back()->withErrors(['storage_name' => '이미 존재하는 스토리지 이름입니다.']);
        }

        // 선택한 템플릿이 있는지 확인
        $templatePath = $this->getTemplateStoragePath($templateName);
        if (!File::exists($templatePath)) {
            return back()->with('error', "선택한 템플릿 '{$templateName}'이 존재하지 않습니다. 경로: " . $templatePath);
        }

        try {
            // 템플릿 복사
            File::copyDirectory($templatePath, $targetPath);

            return back()->with('success', "템플릿 '{$templateName}'을 기반으로 스토리지 '{$storageName}'이 성공적으로 생성되었습니다.");
        } catch (\Exception $e) {
            return back()->with('error', '스토리지 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function select(Request $request)
    {
        $request->validate([
            'storage_name' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:50',
        ], [
            'storage_name.required' => '스토리지 이름이 필요합니다.',
            'storage_name.regex' => '잘못된 스토리지 이름 형식입니다.',
            'storage_name.max' => '스토리지 이름이 너무 깁니다.',
        ]);

        $storageName = $request->storage_name;
        $storagePath = $this->getStoragePath() . '/' . $storageName;

        // 스토리지 존재 확인
        if (!File::exists($storagePath)) {
            return back()->with('error', '선택하려는 스토리지가 존재하지 않습니다.');
        }

        // 데이터베이스 파일 존재 확인
        $dbPath = $storagePath . '/Backend/Databases/Release.sqlite';
        if (!File::exists($dbPath)) {
            return back()->with('error', '선택하려는 스토리지에 데이터베이스 파일이 없습니다.');
        }

        // 데이터베이스 파일 접근 권한 확인
        if (!is_readable($dbPath)) {
            return back()->with('error', '데이터베이스 파일에 읽기 권한이 없습니다.');
        }

        // 세션에 저장
        Session::put('sandbox_storage', $storageName);

        return back()->with('success', "스토리지 '{$storageName}'이 선택되었습니다.");
    }

    public function delete(Request $request)
    {
        $request->validate([
            'storage_name' => 'required|string',
        ]);

        $storageName = $request->storage_name;

        // template은 삭제할 수 없음
        if ($storageName === 'template') {
            return back()->with('error', '템플릿 스토리지는 삭제할 수 없습니다.');
        }

        $storagePath = $this->getStoragePath() . '/' . $storageName;

        if (!File::exists($storagePath)) {
            return back()->with('error', '삭제하려는 스토리지가 존재하지 않습니다.');
        }

        // 현재 선택된 스토리지인 경우 기본값으로 변경
        if (Session::get('sandbox_storage') === $storageName) {
            Session::put('sandbox_storage', '1');
        }

        try {
            File::deleteDirectory($storagePath);
            return back()->with('success', "스토리지 '{$storageName}'이 성공적으로 삭제되었습니다.");
        } catch (\Exception $e) {
            return back()->with('error', '스토리지 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    private function getStorageList()
    {
        $storages = [];
        $storagePath = $this->getStoragePath();

        if (!File::exists($storagePath)) {
            return $storages;
        }

        $directories = File::directories($storagePath);

        foreach ($directories as $directory) {
            $basename = basename($directory);

            // sandbox 폴더 안의 모든 디렉토리를 스토리지로 인식
            // template 관련 폴더는 제외
            if ($basename !== 'sandbox-template') {
                $storages[] = [
                    'name' => $basename,
                    'full_path' => $directory,
                    'created_at' => $this->getDirectoryCreatedAt($directory),
                    'size' => $this->getDirectorySize($directory),
                    'file_count' => $this->getFileCount($directory),
                ];
            }
        }

        // 이름순 정렬
        usort($storages, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $storages;
    }

    private function getDirectoryCreatedAt($path)
    {
        try {
            return Carbon::createFromTimestamp(filemtime($path))->format('Y-m-d H:i');
        } catch (\Exception $e) {
            return '알 수 없음';
        }
    }

    private function getDirectorySize($path)
    {
        try {
            $size = $this->getDirectorySizeRecursive($path);
            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return '알 수 없음';
        }
    }

    private function getDirectorySizeRecursive($path)
    {
        $size = 0;

        if (is_dir($path)) {
            $files = File::allFiles($path);
            foreach ($files as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function getFileCount($path)
    {
        try {
            return count(File::allFiles($path));
        } catch (\Exception $e) {
            return 0;
        }
    }

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
}
