<?php

namespace App\Controllers\FileManager;

/**
 * RFX 도메인 파일 관리 컨트롤러 
 * (기존 document_analysis.php 내용 통합)
 */
class Controller
{
    /**
     * API 라우팅 처리
     */
    public function handleRequest()
    {
        // CORS 헤더 설정
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

        // OPTIONS 요청 처리 (CORS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $requestUri = $_SERVER['REQUEST_URI'];
            
            // URL에서 쿼리 파라미터 제거
            $path = parse_url($requestUri, PHP_URL_PATH);
            $pathSegments = explode('/', trim($path, '/'));
            
            // API 경로 찾기 (sandbox 경로 고려)
            $apiIndex = array_search('api', $pathSegments);
            if ($apiIndex === false) {
                throw new Exception('Invalid API path');
            }
            
            // API 세그먼트 추출
            $apiSegments = array_slice($pathSegments, $apiIndex);
            
            // 라우팅
            switch ($method) {
                case 'POST':
                    if ($apiSegments[1] === 'document' && $apiSegments[2] === 'analyze') {
                        $this->handleAnalyzeRequest();
                    } else {
                        throw new Exception('Invalid POST endpoint');
                    }
                    break;
                    
                case 'GET':
                    if ($apiSegments[1] === 'document') {
                        $this->handleGetRequest($apiSegments);
                    } else {
                        throw new Exception('Invalid GET endpoint');
                    }
                    break;
                    
                default:
                    throw new Exception('Method not allowed');
            }

        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * 문서 분석 요청 처리
     */
    private function handleAnalyzeRequest()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['file_id'])) {
            throw new Exception('file_id is required');
        }
        
        // DocumentAnalysis 컨트롤러로 위임
        require_once __DIR__ . '/../DocumentAnalysis/Controller.php';
        $controller = new \App\Controllers\DocumentAnalysis\Controller();
        echo $controller->analyzeDocument($input['file_id']);
    }

    /**
     * GET 요청 처리
     */
    private function handleGetRequest($apiSegments)
    {
        if (count($apiSegments) >= 3) {
            $id = $apiSegments[2];
            // DocumentAnalysis 컨트롤러로 위임
            require_once __DIR__ . '/../DocumentAnalysis/Controller.php';
            $controller = new \App\Controllers\DocumentAnalysis\Controller();
            echo $controller->getResult($id);
        } else {
            // DocumentAnalysis 컨트롤러로 위임
            require_once __DIR__ . '/../DocumentAnalysis/Controller.php';
            $controller = new \App\Controllers\DocumentAnalysis\Controller();
            echo $controller->getHistory();
        }
    }

    /**
     * JSON 응답 헬퍼
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        return json_encode($data);
    }
}