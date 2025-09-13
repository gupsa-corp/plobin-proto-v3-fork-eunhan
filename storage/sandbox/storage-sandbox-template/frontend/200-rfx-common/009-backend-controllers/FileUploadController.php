<?php

namespace App\Http\Controllers\Api\Sandbox;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Sandbox\FileUploadRequest;
use App\Services\Sandbox\FileManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    private FileManagerService $fileManager;

    public function __construct(FileManagerService $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * 파일 업로드 처리
     */
    public function upload(FileUploadRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');

            // 파일 정보 로깅
            Log::info('File upload started', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            // 파일 저장
            $result = $this->fileManager->storeFile($file);

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

            $files = $this->fileManager->getFiles($params);

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
            $file = $this->fileManager->getFile($id);

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
            $file = $this->fileManager->getFile($id);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '파일을 찾을 수 없습니다.'
                ], 404);
            }

            // 파일이 존재하는지 확인
            if (!Storage::disk('sandbox_downloads')->exists($file['stored_name'])) {
                return response()->json([
                    'success' => false,
                    'message' => '파일이 서버에 존재하지 않습니다.'
                ], 404);
            }

            $filePath = Storage::disk('sandbox_downloads')->path($file['stored_name']);

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
            $file = $this->fileManager->getFile($id);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '파일을 찾을 수 없습니다.'
                ], 404);
            }

            // 파일 삭제
            $this->fileManager->deleteFile($id);

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
     * 파일 메타데이터 업데이트
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'original_name' => 'nullable|string|max:255'
            ]);

            $file = $this->fileManager->updateFile($id, $validated);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => '파일을 찾을 수 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '파일 정보가 업데이트되었습니다.',
                'data' => $file
            ], 200);

        } catch (\Exception $e) {
            Log::error('File update failed', [
                'error' => $e->getMessage(),
                'file_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 정보 업데이트 중 오류가 발생했습니다.'
            ], 500);
        }
    }

    /**
     * 파일 통계 정보
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->fileManager->getFileStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get file stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => '파일 통계를 가져오는 중 오류가 발생했습니다.'
            ], 500);
        }
    }
}
