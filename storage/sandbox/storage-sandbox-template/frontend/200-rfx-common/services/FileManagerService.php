<?php

namespace App\Services\Sandbox;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FileManagerService
{
    private const UPLOAD_DISK = 'sandbox_downloads'; // 업로드 시 바로 downloads 폴더에 저장
    private const DOWNLOAD_DISK = 'sandbox_downloads';
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    private const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total

    /**
     * 파일 저장
     */
    public function storeFile(UploadedFile $file): array
    {
        // 파일 크기 검증
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('파일 크기가 너무 큽니다. 최대 10MB까지 허용됩니다.');
        }

        // 전체 파일 크기 검증
        if ($this->getTotalFileSize() + $file->getSize() > self::MAX_TOTAL_SIZE) {
            throw new \Exception('총 파일 크기가 50MB를 초과합니다.');
        }

        // 파일명 생성 (타임스탬프 + 원본 파일명)
        $timestamp = now()->format('Y-m-d_H-i-s');
        $originalName = $file->getClientOriginalName();
        $storedName = $timestamp . '_' . $originalName;

        // downloads 폴더에 직접 저장 (날짜별 폴더 구조 없이)
        $fullPath = $storedName;

        try {
            // 파일 저장
            $path = $file->storeAs('', $storedName, self::UPLOAD_DISK);

            if (!$path) {
                throw new \Exception('파일 저장에 실패했습니다.');
            }

            // 데이터베이스에 파일 정보 저장
            $fileId = DB::table('uploaded_files')->insertGetId([
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_path' => $fullPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now(),
                'user_id' => auth()->id()
            ]);

            Log::info('File stored successfully', [
                'file_id' => $fileId,
                'original_name' => $originalName,
                'stored_path' => $fullPath
            ]);

            return [
                'id' => $fileId,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_path' => $fullPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            // 저장 실패 시 업로드된 파일 삭제
            if (Storage::disk(self::UPLOAD_DISK)->exists($fullPath)) {
                Storage::disk(self::UPLOAD_DISK)->delete($fullPath);
            }
            throw $e;
        }
    }

    /**
     * 파일 목록 조회
     */
    public function getFiles(array $params = []): array
    {
        $query = DB::table('uploaded_files');

        // 검색 조건
        if (!empty($params['search'])) {
            $query->where('original_name', 'like', '%' . $params['search'] . '%');
        }

        // 파일 형식 필터
        if (!empty($params['type'])) {
            $query->where('mime_type', 'like', $this->getMimeTypePattern($params['type']) . '%');
        }

        // 정렬
        $sortBy = $params['sort'] ?? 'uploaded_at_desc';
        switch ($sortBy) {
            case 'uploaded_at_asc':
                $query->orderBy('uploaded_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('original_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('original_name', 'desc');
                break;
            case 'size_asc':
                $query->orderBy('file_size', 'asc');
                break;
            case 'size_desc':
                $query->orderBy('file_size', 'desc');
                break;
            default:
                $query->orderBy('uploaded_at', 'desc');
        }

        // 페이지네이션
        $perPage = $params['per_page'] ?? 10;
        $page = $params['page'] ?? 1;
        $offset = ($page - 1) * $perPage;

        $total = $query->count();
        $files = $query->skip($offset)->take($perPage)->get();

        return [
            'files' => $files,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * 단일 파일 조회
     */
    public function getFile(int $id): ?array
    {
        $file = DB::table('uploaded_files')->where('id', $id)->first();

        return $file ? (array) $file : null;
    }

    /**
     * 파일 삭제
     */
    public function deleteFile(int $id): bool
    {
        $file = $this->getFile($id);

        if (!$file) {
            return false;
        }

        try {
            // 물리적 파일 삭제
            if (Storage::disk(self::DOWNLOAD_DISK)->exists($file['stored_name'])) {
                Storage::disk(self::DOWNLOAD_DISK)->delete($file['stored_name']);
            }

            // 데이터베이스에서 삭제
            DB::table('uploaded_files')->where('id', $id)->delete();

            Log::info('File deleted successfully', [
                'file_id' => $id,
                'original_name' => $file['original_name']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 파일 정보 업데이트
     */
    public function updateFile(int $id, array $data): ?array
    {
        $updated = DB::table('uploaded_files')
            ->where('id', $id)
            ->update($data);

        if ($updated) {
            return $this->getFile($id);
        }

        return null;
    }

    /**
     * 파일 통계 정보
     */
    public function getFileStats(): array
    {
        $stats = DB::table('uploaded_files')
            ->selectRaw('
                COUNT(*) as total_files,
                SUM(file_size) as total_size,
                AVG(file_size) as avg_file_size,
                MAX(uploaded_at) as last_upload
            ')
            ->first();

        $fileTypes = DB::table('uploaded_files')
            ->selectRaw('
                CASE
                    WHEN mime_type LIKE "image/%" THEN "image"
                    WHEN mime_type LIKE "video/%" THEN "video"
                    WHEN mime_type LIKE "audio/%" THEN "audio"
                    WHEN mime_type = "application/pdf" THEN "pdf"
                    WHEN mime_type LIKE "%document%" OR mime_type LIKE "text/%" THEN "document"
                    WHEN mime_type LIKE "%zip%" OR mime_type LIKE "%rar%" THEN "archive"
                    ELSE "other"
                END as type,
                COUNT(*) as count
            ')
            ->groupBy('type')
            ->get();

        return [
            'total_files' => $stats->total_files ?? 0,
            'total_size' => $stats->total_size ?? 0,
            'avg_file_size' => $stats->avg_file_size ?? 0,
            'last_upload' => $stats->last_upload,
            'file_types' => $fileTypes->pluck('count', 'type')->toArray(),
            'storage_used_percentage' => $this->getStorageUsagePercentage()
        ];
    }

    /**
     * MIME 타입 패턴 생성
     */
    private function getMimeTypePattern(string $type): string
    {
        $patterns = [
            'image' => 'image/',
            'video' => 'video/',
            'audio' => 'audio/',
            'document' => 'text/',
            'archive' => 'application/',
            'other' => ''
        ];

        return $patterns[$type] ?? '';
    }

    /**
     * 전체 파일 크기 계산
     */
    private function getTotalFileSize(): int
    {
        return DB::table('uploaded_files')->sum('file_size');
    }

    /**
     * 스토리지 사용량 백분율 계산
     */
    private function getStorageUsagePercentage(): float
    {
        $totalSize = $this->getTotalFileSize();
        return round(($totalSize / self::MAX_TOTAL_SIZE) * 100, 2);
    }

    /**
     * 파일 다운로드용 복사본 생성
     */
    public function createDownloadCopy(int $id): ?string
    {
        $file = $this->getFile($id);

        if (!$file) {
            return null;
        }

        $sourcePath = $file['file_path'];
        $downloadPath = 'downloads/' . $file['stored_name'];

        try {
            // 업로드 폴더에서 다운로드 폴더로 복사
            $content = Storage::disk(self::UPLOAD_DISK)->get($sourcePath);
            Storage::disk(self::DOWNLOAD_DISK)->put($downloadPath, $content);

            return $downloadPath;

        } catch (\Exception $e) {
            Log::error('Failed to create download copy', [
                'file_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 임시 파일 정리
     */
    public function cleanupTempFiles(): int
    {
        $deletedCount = 0;

        try {
            // 다운로드 폴더의 오래된 파일들 정리 (24시간 이상 된 파일)
            $files = Storage::disk(self::DOWNLOAD_DISK)->files('downloads');

            foreach ($files as $file) {
                $filePath = Storage::disk(self::DOWNLOAD_DISK)->path($file);
                $fileModified = filemtime($filePath);

                if ($fileModified && (time() - $fileModified) > 86400) { // 24시간
                    Storage::disk(self::DOWNLOAD_DISK)->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Temp files cleanup completed', ['deleted_count' => $deletedCount]);

        } catch (\Exception $e) {
            Log::error('Temp files cleanup failed', ['error' => $e->getMessage()]);
        }

        return $deletedCount;
    }
}
