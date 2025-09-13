<?php

require_once 'FileStorageHandler.php';

/**
 * 파일 저장소 API 엔드포인트
 * RESTful API를 통해 파일 업로드, 목록 조회, 다운로드, 삭제 기능을 제공
 */
class FileStorageAPI
{
    private $handler;

    public function __construct()
    {
        $this->handler = new FileStorageHandler();
        
        // CORS 헤더 설정
        $this->setCorsHeaders();
        
        // Content-Type 설정
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * CORS 헤더 설정
     */
    private function setCorsHeaders()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        
        // OPTIONS 요청 처리
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * API 요청 라우팅
     */
    public function handleRequest()
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = array_filter(explode('/', $path));
            
            // API 경로에서 엔드포인트 추출
            $endpoint = end($pathParts);
            
            switch ($method) {
                case 'POST':
                    $this->handlePostRequest($endpoint);
                    break;
                case 'GET':
                    $this->handleGetRequest($endpoint);
                    break;
                case 'DELETE':
                    $this->handleDeleteRequest($endpoint);
                    break;
                default:
                    $this->sendError('지원하지 않는 HTTP 메소드입니다.', 405);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * POST 요청 처리
     */
    private function handlePostRequest($endpoint)
    {
        switch ($endpoint) {
            case 'upload':
                $this->uploadFiles();
                break;
            case 'delete-multiple':
                $this->deleteMultipleFiles();
                break;
            default:
                $this->sendError('알 수 없는 엔드포인트입니다.', 404);
        }
    }

    /**
     * GET 요청 처리
     */
    private function handleGetRequest($endpoint)
    {
        switch ($endpoint) {
            case 'files':
                $this->getFiles();
                break;
            case 'download':
                $this->downloadFile();
                break;
            case 'storage-info':
                $this->getStorageInfo();
                break;
            case 'file-info':
                $this->getFileInfo();
                break;
            default:
                $this->sendError('알 수 없는 엔드포인트입니다.', 404);
        }
    }

    /**
     * DELETE 요청 처리
     */
    private function handleDeleteRequest($endpoint)
    {
        switch ($endpoint) {
            case 'file':
                $this->deleteFile();
                break;
            default:
                $this->sendError('알 수 없는 엔드포인트입니다.', 404);
        }
    }

    /**
     * 파일 업로드 처리
     */
    private function uploadFiles()
    {
        if (empty($_FILES['files'])) {
            $this->sendError('업로드할 파일이 없습니다.', 400);
            return;
        }

        try {
            $result = $this->handler->handleMultipleUpload($_FILES['files']);
            
            $response = [
                'success' => true,
                'message' => '파일 업로드가 완료되었습니다.',
                'data' => $result
            ];
            
            // 업로드 실패가 있는 경우 부분 성공으로 처리
            if (!empty($result['failed'])) {
                $response['message'] = '일부 파일 업로드에 실패했습니다.';
                $response['partial_success'] = true;
            }
            
            $this->sendResponse($response);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * 파일 목록 조회
     */
    private function getFiles()
    {
        try {
            $filters = [
                'type' => $_GET['type'] ?? 'all',
                'search' => $_GET['search'] ?? '',
                'sort' => $_GET['sort'] ?? 'date-desc'
            ];
            
            // 빈 필터 제거
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== 'all';
            });
            
            $files = $this->handler->getFileList($filters);
            
            // 페이지네이션 처리
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $totalFiles = count($files);
            $pagedFiles = array_slice($files, $offset, $limit);
            
            $this->sendResponse([
                'success' => true,
                'data' => [
                    'files' => $pagedFiles,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => $totalFiles,
                        'total_pages' => ceil($totalFiles / $limit)
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * 파일 다운로드
     */
    private function downloadFile()
    {
        $fileId = $_GET['file_id'] ?? null;
        
        if (!$fileId) {
            $this->sendError('파일 ID가 필요합니다.', 400);
            return;
        }

        try {
            $downloadInfo = $this->handler->downloadFile($fileId, true);
            
            // 파일 스트리밍 다운로드
            if (file_exists($downloadInfo['file_path'])) {
                // JSON 응답 대신 파일 스트리밍
                header('Content-Type: ' . $downloadInfo['mime_type']);
                header('Content-Disposition: attachment; filename="' . $downloadInfo['filename'] . '"');
                header('Content-Length: ' . $downloadInfo['size']);
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                
                // 파일 출력
                readfile($downloadInfo['file_path']);
                exit();
            } else {
                $this->sendError('파일을 찾을 수 없습니다.', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * 단일 파일 삭제
     */
    private function deleteFile()
    {
        $fileId = $_GET['file_id'] ?? null;
        
        if (!$fileId) {
            $this->sendError('파일 ID가 필요합니다.', 400);
            return;
        }

        try {
            $deletedFile = $this->handler->deleteFile($fileId);
            
            $this->sendResponse([
                'success' => true,
                'message' => '파일이 성공적으로 삭제되었습니다.',
                'data' => $deletedFile
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * 다중 파일 삭제
     */
    private function deleteMultipleFiles()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $fileIds = $input['file_ids'] ?? [];
        
        if (empty($fileIds)) {
            $this->sendError('삭제할 파일 ID 목록이 필요합니다.', 400);
            return;
        }

        try {
            $result = $this->handler->deleteMultipleFiles($fileIds);
            
            $response = [
                'success' => true,
                'message' => '선택된 파일들이 삭제되었습니다.',
                'data' => $result
            ];
            
            if (!empty($result['failed'])) {
                $response['message'] = '일부 파일 삭제에 실패했습니다.';
                $response['partial_success'] = true;
            }
            
            $this->sendResponse($response);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * 스토리지 정보 조회
     */
    private function getStorageInfo()
    {
        try {
            $storageInfo = $this->handler->getStorageInfo();
            
            $this->sendResponse([
                'success' => true,
                'data' => $storageInfo
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * 특정 파일 정보 조회
     */
    private function getFileInfo()
    {
        $fileId = $_GET['file_id'] ?? null;
        
        if (!$fileId) {
            $this->sendError('파일 ID가 필요합니다.', 400);
            return;
        }

        try {
            $files = $this->handler->getFileList();
            $file = null;
            
            foreach ($files as $f) {
                if ($f['id'] === $fileId) {
                    $file = $f;
                    break;
                }
            }
            
            if (!$file) {
                $this->sendError('파일을 찾을 수 없습니다.', 404);
                return;
            }
            
            $this->sendResponse([
                'success' => true,
                'data' => $file
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * 성공 응답 전송
     */
    private function sendResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * 에러 응답 전송
     */
    private function sendError($message, $statusCode = 400)
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// API 실행
if (basename($_SERVER['SCRIPT_NAME']) === 'FileStorageAPI.php') {
    $api = new FileStorageAPI();
    $api->handleRequest();
}