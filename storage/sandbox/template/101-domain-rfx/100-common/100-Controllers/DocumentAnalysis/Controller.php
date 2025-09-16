<?php

namespace App\Controllers\DocumentAnalysis;

require_once __DIR__ . '/../../models/DocumentAsset.php';
require_once __DIR__ . '/../../models/AssetSummary.php';
require_once __DIR__ . '/../../models/SummaryVersion.php';
require_once __DIR__ . '/../../models/UploadedFile.php';

/**
 * RFX 도메인 문서 분석 컨트롤러
 * AI 기반 문서 에셋 분석 및 요약 관리
 */
class Controller
{
    private $db;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../../database/release.sqlite';
        $this->db = new PDO('sqlite:' . $dbPath);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 문서 분석 요청
     */
    public function analyzeDocument($fileId)
    {
        try {
            $file = \UploadedFile::find($fileId);
            if (!$file) {
                return $this->jsonResponse(['error' => '파일을 찾을 수 없습니다.'], 404);
            }

            // 이미 분석 요청된 경우
            if ($file->isAnalysisRequested()) {
                return $this->jsonResponse([
                    'message' => '이미 분석이 요청된 파일입니다.',
                    'status' => $file->getAnalysisStatus()
                ]);
            }

            // 분석 요청 상태로 변경
            $file->requestAnalysis();

            // 실제 AI 분석 시뮬레이션 (백그라운드 프로세스)
            $this->simulateAIAnalysis($fileId);

            return $this->jsonResponse([
                'message' => '문서 분석이 요청되었습니다.',
                'file_id' => $fileId,
                'status' => 'pending'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 분석 결과 조회
     */
    public function getResult($id)
    {
        // TODO: Implement analysis result retrieval
        try {
            $asset = \DocumentAsset::find($id);
            if (!$asset) {
                return $this->jsonResponse(['error' => '분석 결과를 찾을 수 없습니다.'], 404);
            }

            return $this->jsonResponse($asset->toArray());
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 분석 히스토리 조회
     */
    public function getHistory()
    {
        // TODO: Implement analysis history retrieval
        try {
            $history = \DocumentAsset::getAnalysisHistory();
            return $this->jsonResponse($history);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AI 분석 시뮬레이션
     */
    private function simulateAIAnalysis($fileId)
    {
        // 실제 구현에서는 백그라운드 큐 작업으로 처리
        // 여기서는 시뮬레이션
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