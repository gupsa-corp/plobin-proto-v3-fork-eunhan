<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class SandboxCustomScreen extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'folder_name',
        'file_path',
        'sandbox_folder'
    ];

    /**
     * 폴더가 존재하는지 확인
     */
    public function fileExists()
    {
        $folderPath = $this->getFolderPath();
        return File::isDirectory($folderPath);
    }

    /**
     * 폴더 경로를 반환
     */
    public function getFolderPath()
    {
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->sandbox_folder}");
    }

    /**
     * 전체 파일 경로를 반환
     */
    public function getFullFilePath()
    {
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->sandbox_folder}/{$this->file_path}");
    }

    /**
     * 파일 크기를 반환 (바이트)
     */
    public function getFileSize()
    {
        $fullPath = $this->getFullFilePath();
        return File::exists($fullPath) ? File::size($fullPath) : 0;
    }

    /**
     * 파일 수정일시를 반환
     */
    public function getFileModified()
    {
        $fullPath = $this->getFullFilePath();
        return File::exists($fullPath) ? File::lastModified($fullPath) : null;
    }
}
