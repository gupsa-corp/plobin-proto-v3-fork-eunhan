<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadManager extends Component
{
    use WithFileUploads;

    public $files = [];
    public $uploadedFiles = [];
    public $currentStep = 'upload'; // upload, progress, complete
    public $uploadProgress = [];
    public $overallProgress = 0;
    public $maxFileSize = 50; // MB
    public $maxFiles = 20;
    public $maxTotalSize = 500; // MB

    protected $rules = [
        'files.*' => 'file|max:51200', // 50MB in KB
    ];

    public function mount()
    {
        $this->reset();
    }

    public function updatedFiles()
    {
        $this->validate();
        
        // 파일 개수 제한 검사
        if (count($this->files) > $this->maxFiles) {
            session()->flash('error', "최대 {$this->maxFiles}개의 파일만 업로드할 수 있습니다.");
            return;
        }

        // 총 크기 제한 검사
        $totalSize = 0;
        foreach ($this->files as $file) {
            $totalSize += $file->getSize();
        }

        if ($totalSize > ($this->maxTotalSize * 1024 * 1024)) {
            session()->flash('error', "총 업로드 크기가 {$this->maxTotalSize}MB를 초과합니다.");
            return;
        }

        $this->currentStep = 'selected';
    }

    public function removeFile($index)
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
        
        if (empty($this->files)) {
            $this->currentStep = 'upload';
        }
    }

    public function clearFiles()
    {
        $this->files = [];
        $this->currentStep = 'upload';
    }

    public function startUpload()
    {
        if (empty($this->files)) {
            return;
        }

        $this->currentStep = 'progress';
        $this->uploadFiles();
    }

    protected function uploadFiles()
    {
        foreach ($this->files as $index => $file) {
            try {
                // 실제 파일 업로드 로직
                $path = $file->store('uploads', 'public');
                
                $this->uploadedFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];

                $this->uploadProgress[$index] = 100;
                $this->calculateOverallProgress();

            } catch (\Exception $e) {
                session()->flash('error', "파일 업로드 중 오류가 발생했습니다: " . $e->getMessage());
                return;
            }
        }

        $this->currentStep = 'complete';
    }

    protected function calculateOverallProgress()
    {
        $totalProgress = array_sum($this->uploadProgress);
        $this->overallProgress = count($this->files) > 0 ? 
            round($totalProgress / count($this->files)) : 0;
    }

    public function newUpload()
    {
        $this->reset();
        $this->currentStep = 'upload';
    }

    public function getTotalSize()
    {
        $totalSize = 0;
        foreach ($this->files as $file) {
            $totalSize += $file->getSize();
        }
        return $this->formatFileSize($totalSize);
    }

    public function getFileIcon($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $icons = [
            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️', 'webp' => '🖼️',
            'pdf' => '📄', 'doc' => '📝', 'docx' => '📝', 'txt' => '📄',
            'xls' => '📊', 'xlsx' => '📊', 'csv' => '📊',
            'zip' => '📦', 'rar' => '📦', '7z' => '📦',
            'mp4' => '🎥', 'avi' => '🎥', 'mov' => '🎥',
            'mp3' => '🎵', 'wav' => '🎵', 'flac' => '🎵'
        ];

        return $icons[$extension] ?? '📄';
    }

    public function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function render()
    {
        return view('livewire.file-upload-manager');
    }
}