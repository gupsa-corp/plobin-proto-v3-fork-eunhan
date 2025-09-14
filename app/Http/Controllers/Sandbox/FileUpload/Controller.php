<?php

namespace App\Http\Controllers\Sandbox\FileUpload;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use App\Services\SandboxContextService;

class Controller extends \App\Http\Controllers\Controller
{
    protected $sandboxContextService;
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    private const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total

    public function __construct(SandboxContextService $sandboxContextService)
    {
        $this->sandboxContextService = $sandboxContextService;
    }

    /**
     * 파일 업로드 처리
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240'
            ]);

            $file = $request->file('file');

            // 파일 크기 검증
            if ($file->getSize() > self::MAX_FILE_SIZE) {
                return response()->json([
                    'success' => false,
                    'message' => '파일 크기가 너무 큽니다. 최대 10MB까지 허용됩니다.'
                ], 422);
            }

            // 전체 파일 크기 검증
            if ($this->getTotalFileSize() + $file->getSize() > self::MAX_TOTAL_SIZE) {
                return response()->json([
                    'success' => false,
                    'message' => '총 파일 크기가 50MB를 초과합니다.'
                ], 422);
            }

            // 파일 정보 로깅
            Log::info('File upload started', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            // 파일 저장
            $result = $this->storeFile($file);

            Log::info('File upload completed', [
                'file_id' => $result['id'],
                'stored_path' => $result['file_path']
            ]);

            return response()->json([
                'success' => true,
                'message' => '파일이 성공적으로 업로드되었습니다.',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'no file'
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 업로드 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 파일 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->validate([
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
                'type' => 'nullable|string|in:image,document,video,audio,archive,other',
                'sort' => 'nullable|string|in:uploaded_at_desc,uploaded_at_asc,name_asc,name_desc,size_desc,size_asc'
            ]);

            $files = $this->getFiles($params);

            return response()->json([
                'success' => true,
                'data' => $files
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get files list', [
                'error' => $e->getMessage(),
                'params' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 목록을 가져오는 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 파일 상세 정보 조회
     */
    public function show(int $id): JsonResponse
    {
        try {
            $file = $this->getFile($id);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '파일을 찾을 수 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $file
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get file details', [
                'error' => $e->getMessage(),
                'file_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 정보를 가져오는 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 파일 다운로드
     */
    public function download(int $id): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $file = $this->getFile($id);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '파일을 찾을 수 없습니다.'
                ], 404);
            }

            // 파일이 존재하는지 확인
            $filePath = $this->sandboxContextService->getSandboxStoragePath() . '/downloads/' . $file['stored_name'];
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '파일이 서버에 존재하지 않습니다.'
                ], 404);
            }

            Log::info('File download started', [
                'file_id' => $id,
                'original_name' => $file['original_name'],
                'stored_name' => $file['stored_name']
            ]);

            return response()->download($filePath, $file['original_name'], [
                'Content-Type' => $file['mime_type'],
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('File download failed', [
                'error' => $e->getMessage(),
                'file_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 다운로드 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 파일 삭제
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $file = $this->getFile($id);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '파일을 찾을 수 없습니다.'
                ], 404);
            }

            // 파일 삭제
            $this->deleteFile($id);

            Log::info('File deleted', [
                'file_id' => $id,
                'original_name' => $file['original_name']
            ]);

            return response()->json([
                'success' => true,
                'message' => '파일이 성공적으로 삭제되었습니다.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'error' => $e->getMessage(),
                'file_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 삭제 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 파일 저장 (private method)
     */
    private function storeFile(UploadedFile $file): array
    {
        // 파일명 생성 (타임스탬프 + 원본 파일명)
        $timestamp = now()->format('Y-m-d_H-i-s');
        $originalName = $file->getClientOriginalName();
        $storedName = $timestamp . '_' . $originalName;

        // 파일 정보를 미리 저장 (move 후에는 접근할 수 없음)
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // 파일 경로 설정 (downloads 디렉토리에 직접 저장)
        $fullPath = $storedName;

        try {
            // 파일 저장 - downloads 디렉토리에 저장하여 파일 목록에 표시되도록 함
            $uploadPath = $this->sandboxContextService->getSandboxStoragePath() . '/downloads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $storedName);

            // 데이터베이스에 파일 정보 저장
            $fileId = DB::table('uploaded_files')->insertGetId([
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_path' => $fullPath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'uploaded_at' => now(),
                'user_id' => auth()->id()
            ]);

            return [
                'id' => $fileId,
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_path' => $fullPath,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'uploaded_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            // 저장 실패 시 업로드된 파일 삭제
            $filePath = $this->sandboxContextService->getSandboxStoragePath() . '/downloads/' . $fullPath;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw $e;
        }
    }

    /**
     * 파일 목록 조회 (private method)
     */
    private function getFiles(array $params = []): array
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
     * 단일 파일 조회 (private method)
     */
    private function getFile(int $id): ?array
    {
        $file = DB::table('uploaded_files')->where('id', $id)->first();

        return $file ? (array) $file : null;
    }

    /**
     * 파일 삭제 (private method)
     */
    private function deleteFile(int $id): bool
    {
        $file = $this->getFile($id);

        if (!$file) {
            return false;
        }

        try {
            // 물리적 파일 삭제
            $filePath = $this->sandboxContextService->getSandboxStoragePath() . '/downloads/' . $file['stored_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // 데이터베이스에서 삭제
            DB::table('uploaded_files')->where('id', $id)->delete();

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
     * MIME 타입 패턴 생성 (private method)
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
     * 샌드박스 템플릿 downloads 디렉토리 파일 목록 조회
     */
    public function getSandboxFiles(Request $request): JsonResponse
    {
        try {
            $params = $request->validate([
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'search' => 'nullable|string|max:255',
                'type' => 'nullable|string|in:image,document,video,audio,archive,other',
                'sort' => 'nullable|string|in:uploaded_at_desc,uploaded_at_asc,name_asc,name_desc,size_desc,size_asc'
            ]);

            $files = $this->getSandboxDownloadFiles($params);

            return response()->json([
                'success' => true,
                'data' => $files['files'] ?? [],
                'pagination' => [
                    'current_page' => $files['current_page'] ?? 1,
                    'per_page' => $files['per_page'] ?? 10,
                    'total' => $files['total'] ?? 0,
                    'total_pages' => $files['total_pages'] ?? 1
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get sandbox files list', [
                'error' => $e->getMessage(),
                'params' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => '샌드박스 파일 목록을 가져오는 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 샌드박스 템플릿 downloads 디렉토리 파일 목록 조회 (private method)
     */
    private function getSandboxDownloadFiles(array $params = []): array
    {
        try {
            $downloadsDir = $this->sandboxContextService->getSandboxStoragePath() . '/downloads';
            $files = [];

            if (is_dir($downloadsDir)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($downloadsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );

                $id = 1;
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $relativePath = str_replace($downloadsDir . '/', '', $file->getPathname());
                        $mimeType = $this->getMimeTypeFromFile($file->getPathname());

                        // 다운로드 URL 생성
                        $downloadUrl = url($this->sandboxContextService->getSandboxUrl() . '/downloads/' . $relativePath);

                        $files[] = [
                            'id' => $id++,
                            'original_name' => $file->getFilename(),
                            'stored_name' => $file->getFilename(),
                            'file_path' => $relativePath,
                            'file_size' => $file->getSize(),
                            'mime_type' => $mimeType,
                            'uploaded_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'user_id' => null,
                            'download_url' => $downloadUrl
                        ];
                    }
                }
            }

            // 최신순으로 정렬
            usort($files, function($a, $b) {
                return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
            });

            // 검색 필터
            if (!empty($params['search'])) {
                $files = array_filter($files, function($file) use ($params) {
                    return stripos($file['original_name'], $params['search']) !== false;
                });
            }

            // 타입 필터
            if (!empty($params['type'])) {
                $files = array_filter($files, function($file) use ($params) {
                    $category = $this->getFileCategory($file['mime_type']);
                    return $category === $params['type'];
                });
            }

            // 정렬 적용
            $sortBy = $params['sort'] ?? 'uploaded_at_desc';
            switch ($sortBy) {
                case 'uploaded_at_asc':
                    usort($files, function($a, $b) { return strtotime($a['uploaded_at']) - strtotime($b['uploaded_at']); });
                    break;
                case 'name_asc':
                    usort($files, function($a, $b) { return strcasecmp($a['original_name'], $b['original_name']); });
                    break;
                case 'name_desc':
                    usort($files, function($a, $b) { return strcasecmp($b['original_name'], $a['original_name']); });
                    break;
                case 'size_asc':
                    usort($files, function($a, $b) { return $a['file_size'] - $b['file_size']; });
                    break;
                case 'size_desc':
                    usort($files, function($a, $b) { return $b['file_size'] - $a['file_size']; });
                    break;
            }

            // 페이지네이션
            $perPage = $params['per_page'] ?? 10;
            $page = $params['page'] ?? 1;
            $total = count($files);
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $pagedFiles = array_slice($files, $offset, $perPage);

            return [
                'files' => array_values($pagedFiles), // reindex array
                'total' => $total,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'per_page' => $perPage
            ];

        } catch (\Exception $e) {
            Log::error('getSandboxDownloadFiles error: ' . $e->getMessage());
            return [
                'files' => [],
                'total' => 0,
                'total_pages' => 1,
                'current_page' => 1,
                'per_page' => 10
            ];
        }
    }

    /**
     * 파일 MIME 타입 추출 (private method)
     */
    private function getMimeTypeFromFile($filePath): string
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'zip' => 'application/zip',
            'rar' => 'application/rar',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav'
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * 파일 카테고리 추출 (private method)
     */
    private function getFileCategory(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) return 'image';
        if (strpos($mimeType, 'video/') === 0) return 'video';
        if (strpos($mimeType, 'audio/') === 0) return 'audio';
        if ($mimeType === 'application/pdf') return 'pdf';
        if (strpos($mimeType, 'document') !== false || strpos($mimeType, 'text') !== false) return 'document';
        if (strpos($mimeType, 'zip') !== false || strpos($mimeType, 'rar') !== false) return 'archive';

        return 'other';
    }

    /**
     * 전체 파일 크기 계산 (private method)
     */
    private function getTotalFileSize(): int
    {
        return DB::table('uploaded_files')->sum('file_size');
    }
}
