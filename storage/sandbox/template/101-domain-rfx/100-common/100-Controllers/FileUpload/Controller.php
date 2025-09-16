<?php

namespace App\Controllers\FileUpload;

/**
 * RFX 도메인 파일 업로드 컨트롤러
 */
class Controller
{
    private $fileManager;

    public function __construct()
    {
        // TODO: FileManager 서비스 주입
        // $this->fileManager = new FileManagerService();
    }

    /**
     * 파일 업로드 처리
     */
    public function upload()
    {
        try {
            $file = $_FILES['file'] ?? null;
            
            if (!$file) {
                return $this->jsonResponse(['error' => '업로드할 파일이 없습니다.'], 400);
            }

            // 파일 정보 로깅
            error_log('File upload started: ' . $file['name']);

            // TODO: 파일 저장 로직 구현
            // $result = $this->fileManager->storeFile($file);

            return $this->jsonResponse([
                'success' => true,
                'message' => '파일이 성공적으로 업로드되었습니다.',
                'data' => [
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type']
                ]
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 업로드된 파일 목록 조회
     */
    public function getFiles()
    {
        try {
            // TODO: 파일 목록 조회 로직 구현
            return $this->jsonResponse([
                'files' => []
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 파일 삭제
     */
    public function delete($id)
    {
        try {
            // TODO: 파일 삭제 로직 구현
            return $this->jsonResponse([
                'success' => true,
                'message' => '파일이 삭제되었습니다.'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * JSON 응답 헬퍼
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }
}