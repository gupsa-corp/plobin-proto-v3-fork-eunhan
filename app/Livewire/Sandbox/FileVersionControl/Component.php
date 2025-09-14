<?php

namespace App\Livewire\Sandbox\FileVersionControl;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class Component extends LivewireComponent
{
    public string $currentFile = '';
    public array $fileVersions = [];
    public bool $showVersionDialog = false;
    public string $commitMessage = '';
    public string $currentStoragePath = '';
    
    protected $listeners = ['setCurrentFile', 'saveVersion'];

    public function mount()
    {
        $this->currentStoragePath = $this->getCurrentStoragePath();
    }

    private function getCurrentStoragePath()
    {
        $currentStorage = Session::get('sandbox_storage', '1');
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $currentStorage);
    }

    private function getVersionsPath()
    {
        return $this->currentStoragePath . '/.versions';
    }

    public function setCurrentFile($filePath)
    {
        $this->currentFile = str_replace($this->currentStoragePath . '/', '', $filePath);
        $this->loadFileVersions();
    }

    private function loadFileVersions()
    {
        if (!$this->currentFile) {
            $this->fileVersions = [];
            return;
        }

        $versionsPath = $this->getVersionsPath();
        $fileVersionsPath = $versionsPath . '/' . str_replace('/', '_', $this->currentFile);

        if (!File::exists($fileVersionsPath)) {
            $this->fileVersions = [];
            return;
        }

        $versions = [];
        $versionFiles = File::glob($fileVersionsPath . '/*.version');

        foreach ($versionFiles as $versionFile) {
            $metadata = json_decode(File::get($versionFile), true);
            if ($metadata) {
                $versions[] = $metadata;
            }
        }

        // 시간순으로 정렬 (최신순)
        usort($versions, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $this->fileVersions = $versions;
    }

    public function saveVersion($content = null)
    {
        if (!$this->currentFile || empty($this->commitMessage)) {
            return;
        }

        // 현재 파일 내용 가져오기
        $filePath = $this->currentStoragePath . '/' . $this->currentFile;
        if (!File::exists($filePath)) {
            return;
        }

        $content = $content ?? File::get($filePath);
        
        // 버전 디렉토리 생성
        $versionsPath = $this->getVersionsPath();
        if (!File::exists($versionsPath)) {
            File::makeDirectory($versionsPath, 0755, true);
        }

        $fileVersionsPath = $versionsPath . '/' . str_replace('/', '_', $this->currentFile);
        if (!File::exists($fileVersionsPath)) {
            File::makeDirectory($fileVersionsPath, 0755, true);
        }

        // 버전 정보 생성
        $timestamp = now();
        $versionId = $timestamp->format('YmdHis') . '_' . substr(md5($content), 0, 8);
        
        $metadata = [
            'id' => $versionId,
            'file' => $this->currentFile,
            'message' => $this->commitMessage,
            'created_at' => $timestamp->toISOString(),
            'size' => strlen($content),
            'hash' => md5($content)
        ];

        // 메타데이터 저장
        File::put($fileVersionsPath . '/' . $versionId . '.version', json_encode($metadata, JSON_PRETTY_PRINT));
        
        // 파일 내용 저장
        File::put($fileVersionsPath . '/' . $versionId . '.content', $content);

        // 목록 새로고침
        $this->loadFileVersions();
        
        // 입력 초기화
        $this->commitMessage = '';
        $this->showVersionDialog = false;

        session()->flash('success', '새 버전이 저장되었습니다.');
    }

    public function restoreVersion($versionId)
    {
        if (!$this->currentFile || !$versionId) {
            return;
        }

        $versionsPath = $this->getVersionsPath();
        $fileVersionsPath = $versionsPath . '/' . str_replace('/', '_', $this->currentFile);
        $contentFile = $fileVersionsPath . '/' . $versionId . '.content';

        if (!File::exists($contentFile)) {
            session()->flash('error', '버전 파일을 찾을 수 없습니다.');
            return;
        }

        // 현재 파일을 백업으로 저장
        $this->saveVersion(null);

        // 선택한 버전으로 복원
        $versionContent = File::get($contentFile);
        $currentFilePath = $this->currentStoragePath . '/' . $this->currentFile;
        File::put($currentFilePath, $versionContent);

        $this->dispatch('file-restored', content: $versionContent);
        session()->flash('success', '파일이 선택한 버전으로 복원되었습니다.');
    }

    public function deleteVersion($versionId)
    {
        if (!$this->currentFile || !$versionId) {
            return;
        }

        $versionsPath = $this->getVersionsPath();
        $fileVersionsPath = $versionsPath . '/' . str_replace('/', '_', $this->currentFile);
        
        $metadataFile = $fileVersionsPath . '/' . $versionId . '.version';
        $contentFile = $fileVersionsPath . '/' . $versionId . '.content';

        if (File::exists($metadataFile)) {
            File::delete($metadataFile);
        }
        
        if (File::exists($contentFile)) {
            File::delete($contentFile);
        }

        $this->loadFileVersions();
        session()->flash('success', '버전이 삭제되었습니다.');
    }

    public function showVersion($versionId)
    {
        if (!$this->currentFile || !$versionId) {
            return '';
        }

        $versionsPath = $this->getVersionsPath();
        $fileVersionsPath = $versionsPath . '/' . str_replace('/', '_', $this->currentFile);
        $contentFile = $fileVersionsPath . '/' . $versionId . '.content';

        if (File::exists($contentFile)) {
            return File::get($contentFile);
        }

        return '';
    }

    public function openVersionDialog()
    {
        $this->showVersionDialog = true;
        $this->commitMessage = '';
    }

    public function closeVersionDialog()
    {
        $this->showVersionDialog = false;
        $this->commitMessage = '';
    }

    public function render()
    {
        return view('700-page-sandbox.704-page-file-editor.220-file-version-control-component');
    }
}