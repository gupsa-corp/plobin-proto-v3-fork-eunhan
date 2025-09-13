<?php

require_once __DIR__ . '/../models/DocumentAsset.php';
require_once __DIR__ . '/../models/AssetSummary.php';
require_once __DIR__ . '/../models/SummaryVersion.php';
require_once __DIR__ . '/../models/UploadedFile.php';

/**
 * 샌드박스용 문서 분석 컨트롤러
 * AI 기반 문서 에셋 분석 및 요약 관리
 */
class DocumentAnalysisController
{
    private $db;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../database/release.sqlite';
        $this->db = new PDO('sqlite:' . $dbPath);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * 문서 분석 요청
     */
    public function analyzeDocument($fileId)
    {
        try {
            $file = UploadedFile::find($fileId);
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
                'status' => 'processing'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 분석 상태 확인
     */
    public function getAnalysisStatus($fileId)
    {
        try {
            $file = UploadedFile::find($fileId);
            if (!$file) {
                return $this->jsonResponse(['error' => '파일을 찾을 수 없습니다.'], 404);
            }

            return $this->jsonResponse([
                'file_id' => $fileId,
                'status' => $file->getAnalysisStatus(),
                'is_completed' => $file->isAnalysisCompleted(),
                'requested_at' => $file->getAnalysisRequestedAt(),
                'completed_at' => $file->getAnalysisCompletedAt()
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 문서 에셋 조회
     */
    public function getDocumentAssets($fileId)
    {
        try {
            $file = UploadedFile::find($fileId);
            if (!$file) {
                return $this->jsonResponse(['error' => '파일을 찾을 수 없습니다.'], 404);
            }

            if (!$file->isAnalysisCompleted()) {
                return $this->jsonResponse(['error' => '분석이 완료되지 않았습니다.'], 400);
            }

            $documentAsset = new DocumentAsset();
            $assets = $documentAsset->getWithSummary($fileId);

            return $this->jsonResponse([
                'file' => $file->toArray(),
                'assets' => $assets
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 에셋 요약 업데이트
     */
    public function updateAssetSummary($assetId, $data)
    {
        try {
            $summary = AssetSummary::findByAssetId($assetId);
            if (!$summary) {
                return $this->jsonResponse(['error' => '요약을 찾을 수 없습니다.'], 404);
            }

            // 새 버전 생성
            $nextVersion = SummaryVersion::getNextVersionNumber($summary->getId());
            
            // 버전 히스토리 저장
            SummaryVersion::create([
                'summary_id' => $summary->getId(),
                'version_number' => $nextVersion,
                'ai_summary' => $data['ai_summary'],
                'helpful_content' => $data['helpful_content'],
                'edit_type' => $data['edit_type'] ?? 'user_edit',
                'edit_notes' => $data['edit_notes'] ?? null,
                'is_current' => true
            ]);

            // 요약 테이블 업데이트
            $summary->updateSummary($data);
            $summary->setCurrentVersion($nextVersion);

            return $this->jsonResponse([
                'message' => '요약이 업데이트되었습니다.',
                'asset_id' => $assetId,
                'version' => $nextVersion
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 버전 히스토리 조회
     */
    public function getVersionHistory($assetId)
    {
        try {
            $summary = AssetSummary::findByAssetId($assetId);
            if (!$summary) {
                return $this->jsonResponse(['error' => '요약을 찾을 수 없습니다.'], 404);
            }

            $versions = SummaryVersion::findBySummaryId($summary->getId());
            $versionArray = array_map(function($version) {
                return $version->toArray();
            }, $versions);

            return $this->jsonResponse([
                'asset_id' => $assetId,
                'versions' => $versionArray
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 특정 버전으로 전환
     */
    public function switchToVersion($summaryId, $versionNumber)
    {
        try {
            $version = SummaryVersion::findBySummaryAndVersion($summaryId, $versionNumber);
            if (!$version) {
                return $this->jsonResponse(['error' => '버전을 찾을 수 없습니다.'], 404);
            }

            // 현재 버전으로 설정
            $version->setAsCurrent();

            // 요약 테이블도 업데이트
            $summary = AssetSummary::find($summaryId);
            if ($summary) {
                $summary->updateSummary([
                    'ai_summary' => $version->getAiSummary(),
                    'helpful_content' => $version->getHelpfulContent()
                ]);
                $summary->setCurrentVersion($versionNumber);
            }

            return $this->jsonResponse([
                'message' => '버전이 전환되었습니다.',
                'summary_id' => $summaryId,
                'version' => $versionNumber
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 분석 완료된 파일 목록 조회
     */
    public function getAnalyzedFiles()
    {
        try {
            $files = UploadedFile::findAnalyzed();
            $fileArray = array_map(function($file) {
                return $file->toArray();
            }, $files);

            return $this->jsonResponse([
                'files' => $fileArray
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AI 분석 시뮬레이션 (실제로는 백그라운드 큐 작업)
     */
    private function simulateAIAnalysis($fileId)
    {
        // 실제 구현에서는 큐 작업으로 처리
        // 여기서는 시뮬레이션으로 즉시 완료 처리

        try {
            $file = UploadedFile::find($fileId);
            if (!$file) {
                return;
            }

            // 샘플 에셋 데이터 생성 (실제로는 AI가 분석)
            $sampleAssets = $this->generateSampleAssets($fileId, $file->getOriginalName());

            foreach ($sampleAssets as $assetData) {
                // 문서 에셋 생성
                $asset = DocumentAsset::create($assetData);

                if ($asset) {
                    // 에셋 요약 생성
                    $summaryData = [
                        'asset_id' => $asset->getId(),
                        'ai_summary' => $assetData['ai_summary'],
                        'helpful_content' => $assetData['helpful_content'],
                        'analysis_status' => 'completed',
                        'analysis_metadata' => [
                            'ai_model' => 'claude-3-sonnet',
                            'confidence_score' => rand(85, 98) / 100,
                            'processing_time' => rand(2, 8) . 's'
                        ]
                    ];

                    $summary = AssetSummary::create($summaryData);

                    if ($summary) {
                        // 초기 버전 생성
                        SummaryVersion::create([
                            'summary_id' => $summary->getId(),
                            'version_number' => 1,
                            'ai_summary' => $assetData['ai_summary'],
                            'helpful_content' => $assetData['helpful_content'],
                            'edit_type' => 'ai_generated',
                            'edit_notes' => 'AI에 의해 초기 생성됨',
                            'is_current' => true
                        ]);
                    }
                }
            }

            // 분석 완료 처리
            $file->completeAnalysis();

        } catch (Exception $e) {
            // 분석 실패 처리
            if (isset($file)) {
                $file->failAnalysis();
            }
            error_log('AI Analysis Error: ' . $e->getMessage());
        }
    }

    /**
     * 샘플 에셋 데이터 생성
     */
    private function generateSampleAssets($fileId, $fileName)
    {
        $assets = [];
        
        // 파일명에 따른 다른 에셋 구성
        if (strpos($fileName, 'AI') !== false || strpos($fileName, '기술') !== false) {
            $assets = [
                [
                    'file_id' => $fileId,
                    'asset_type' => 'introduction',
                    'section_title' => 'AI 기술 개요',
                    'order_index' => 1,
                    'content' => '인공지능 기술은 현재 모든 산업 분야에서 혁신적인 변화를 이끌고 있습니다. 특히 머신러닝, 딥러닝, 자연어 처리 기술의 발전으로 인해 기업들은 새로운 비즈니스 모델을 구축하고 있습니다.',
                    'ai_summary' => 'AI 기술이 산업 전반에 미치는 혁신적 영향과 주요 기술 트렌드를 소개합니다.',
                    'helpful_content' => '우리 회사도 AI 기술 도입을 통해 업무 효율성을 크게 향상시킬 수 있습니다. 특히 고객 서비스 자동화와 데이터 분석 분야에서 즉시 적용 가능합니다.',
                    'metadata' => ['keywords' => ['AI', '인공지능', '머신러닝', '혁신']]
                ],
                [
                    'file_id' => $fileId,
                    'asset_type' => 'analysis',
                    'section_title' => '시장 분석 결과',
                    'order_index' => 2,
                    'content' => '2024년 AI 시장 규모는 전년 대비 35% 성장한 5,940억 달러를 기록했습니다. 주요 성장 동력은 생성형 AI, 자율주행, 의료 AI 분야입니다.',
                    'ai_summary' => 'AI 시장의 급속한 성장과 주요 성장 분야를 분석한 결과입니다.',
                    'helpful_content' => '우리가 집중해야 할 분야는 생성형 AI입니다. 경쟁사 대비 2년의 기술 격차가 있어 빠른 투자와 인재 확보가 필요합니다.',
                    'metadata' => ['keywords' => ['시장분석', '성장률', '생성형AI']]
                ]
            ];
        } else {
            $assets = [
                [
                    'file_id' => $fileId,
                    'asset_type' => 'introduction',
                    'section_title' => '문서 개요',
                    'order_index' => 1,
                    'content' => '본 문서는 프로젝트의 전반적인 계획과 실행 방안을 다룹니다.',
                    'ai_summary' => '프로젝트의 목적과 범위를 정의하는 개요 섹션입니다.',
                    'helpful_content' => '프로젝트 이해관계자들과 공유하여 전체적인 방향성을 정렬할 수 있습니다.',
                    'metadata' => ['keywords' => ['프로젝트', '계획', '개요']]
                ]
            ];
        }

        return $assets;
    }

    /**
     * JSON 응답 헬퍼
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 라우팅 처리
     */
    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));

        // API 라우팅
        if ($pathParts[0] === 'api' && $pathParts[1] === 'document') {
            switch ($method) {
                case 'POST':
                    if ($pathParts[2] === 'analyze') {
                        $input = json_decode(file_get_contents('php://input'), true);
                        echo $this->analyzeDocument($input['file_id']);
                    }
                    break;

                case 'GET':
                    if ($pathParts[2] === 'status' && isset($pathParts[3])) {
                        echo $this->getAnalysisStatus($pathParts[3]);
                    } elseif ($pathParts[2] === 'assets' && isset($pathParts[3])) {
                        echo $this->getDocumentAssets($pathParts[3]);
                    } elseif ($pathParts[2] === 'files') {
                        echo $this->getAnalyzedFiles();
                    } elseif ($pathParts[2] === 'versions' && isset($pathParts[3])) {
                        echo $this->getVersionHistory($pathParts[3]);
                    }
                    break;

                case 'PUT':
                    if ($pathParts[2] === 'summary' && isset($pathParts[3])) {
                        $input = json_decode(file_get_contents('php://input'), true);
                        echo $this->updateAssetSummary($pathParts[3], $input);
                    } elseif ($pathParts[2] === 'version' && isset($pathParts[3]) && isset($pathParts[4])) {
                        echo $this->switchToVersion($pathParts[3], $pathParts[4]);
                    }
                    break;
            }
        }
    }
}

// 직접 접근시 라우팅 처리
if (basename($_SERVER['SCRIPT_NAME']) === 'DocumentAnalysisController.php') {
    $controller = new DocumentAnalysisController();
    $controller->handleRequest();
}