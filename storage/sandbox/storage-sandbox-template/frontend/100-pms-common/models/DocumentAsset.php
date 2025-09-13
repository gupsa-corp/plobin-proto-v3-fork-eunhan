<?php

/**
 * 샌드박스용 DocumentAsset 모델
 * 팔란티어 온톨로지 기반 문서 에셋 관리
 */
class DocumentAsset
{
    private $db;
    private $id;
    private $file_id;
    private $asset_type;
    private $section_title;
    private $order_index;
    private $content;
    private $metadata;
    private $status;
    private $created_at;
    private $updated_at;

    // 팔란티어 온톨로지 기반 에셋 타입 정의
    public const ASSET_TYPES = [
        'introduction' => '서론/개요',
        'methodology' => '방법론',
        'findings' => '주요 발견사항',
        'analysis' => '분석 결과',
        'conclusion' => '결론',
        'recommendation' => '권고사항',
        'technical_spec' => '기술 사양',
        'data_analysis' => '데이터 분석',
        'case_study' => '사례 연구',
        'appendix' => '부록',
        'reference' => '참고문헌',
        'summary' => '요약',
        'other' => '기타'
    ];

    public function __construct($db = null)
    {
        $this->db = $db ?: $this->getDatabase();
    }

    private function getDatabase()
    {
        $dbPath = __DIR__ . '/../database/release.sqlite';
        return new PDO('sqlite:' . $dbPath);
    }

    // 정적 팩토리 메서드
    public static function create($data)
    {
        $instance = new self();
        return $instance->save($data);
    }

    public static function findByFileId($fileId)
    {
        $instance = new self();
        return $instance->getByFileId($fileId);
    }

    public static function find($id)
    {
        $instance = new self();
        return $instance->findById($id);
    }

    // 데이터 저장
    public function save($data)
    {
        $sql = "INSERT INTO document_assets (
            file_id, asset_type, section_title, order_index, 
            content, metadata, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['file_id'],
            $data['asset_type'],
            $data['section_title'],
            $data['order_index'],
            $data['content'],
            json_encode($data['metadata'] ?? []),
            $data['status'] ?? 'pending'
        ]);

        if ($result) {
            $this->id = $this->db->lastInsertId();
            $this->loadData($data);
            return $this;
        }

        return false;
    }

    // ID로 조회
    public function findById($id)
    {
        $sql = "SELECT * FROM document_assets WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->loadData($data);
            return $this;
        }

        return null;
    }

    // 파일 ID로 에셋 리스트 조회
    public function getByFileId($fileId)
    {
        $sql = "SELECT * FROM document_assets WHERE file_id = ? ORDER BY order_index ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fileId]);
        
        $assets = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $asset = new self($this->db);
            $asset->loadData($row);
            $assets[] = $asset;
        }

        return $assets;
    }

    // 요약과 함께 조회
    public function getWithSummary($fileId)
    {
        $sql = "
            SELECT 
                da.*,
                asu.id as summary_id,
                asu.ai_summary,
                asu.helpful_content,
                asu.analysis_status,
                asu.analysis_metadata
            FROM document_assets da
            LEFT JOIN asset_summaries asu ON da.id = asu.asset_id
            WHERE da.file_id = ?
            ORDER BY da.order_index ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fileId]);
        
        $assets = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $asset = [
                'id' => $row['id'],
                'asset_type' => $row['asset_type'],
                'asset_type_name' => self::ASSET_TYPES[$row['asset_type']] ?? '알 수 없음',
                'asset_type_color' => $this->getAssetTypeColor($row['asset_type']),
                'asset_type_icon' => $this->getAssetTypeIcon($row['asset_type']),
                'section_title' => $row['section_title'],
                'content' => $row['content'],
                'content_preview' => $this->getContentPreview($row['content']),
                'order_index' => $row['order_index'],
                'summary' => $row['summary_id'] ? [
                    'id' => $row['summary_id'],
                    'ai_summary' => $row['ai_summary'],
                    'helpful_content' => $row['helpful_content'],
                    'analysis_status' => $row['analysis_status'],
                    'status_color' => $this->getStatusColor($row['analysis_status']),
                    'status_icon' => $this->getStatusIcon($row['analysis_status'])
                ] : null
            ];
            $assets[] = $asset;
        }

        return $assets;
    }

    // 데이터 로드
    private function loadData($data)
    {
        $this->id = $data['id'] ?? null;
        $this->file_id = $data['file_id'] ?? null;
        $this->asset_type = $data['asset_type'] ?? null;
        $this->section_title = $data['section_title'] ?? null;
        $this->order_index = $data['order_index'] ?? 0;
        $this->content = $data['content'] ?? null;
        $this->metadata = is_string($data['metadata'] ?? null) ? 
            json_decode($data['metadata'], true) : ($data['metadata'] ?? []);
        $this->status = $data['status'] ?? 'pending';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    // 에셋 타입별 색상 반환
    private function getAssetTypeColor($assetType)
    {
        $colors = [
            'introduction' => 'bg-blue-100 text-blue-800',
            'methodology' => 'bg-green-100 text-green-800',
            'findings' => 'bg-purple-100 text-purple-800',
            'analysis' => 'bg-yellow-100 text-yellow-800',
            'conclusion' => 'bg-red-100 text-red-800',
            'recommendation' => 'bg-indigo-100 text-indigo-800',
            'technical_spec' => 'bg-gray-100 text-gray-800',
            'data_analysis' => 'bg-pink-100 text-pink-800',
            'case_study' => 'bg-teal-100 text-teal-800',
            'appendix' => 'bg-orange-100 text-orange-800',
            'reference' => 'bg-cyan-100 text-cyan-800',
            'summary' => 'bg-emerald-100 text-emerald-800',
            'other' => 'bg-slate-100 text-slate-800'
        ];

        return $colors[$assetType] ?? 'bg-gray-100 text-gray-800';
    }

    // 에셋 타입별 아이콘 반환
    private function getAssetTypeIcon($assetType)
    {
        $icons = [
            'introduction' => '📖',
            'methodology' => '⚙️',
            'findings' => '🔍',
            'analysis' => '📊',
            'conclusion' => '🎯',
            'recommendation' => '💡',
            'technical_spec' => '🛠️',
            'data_analysis' => '📈',
            'case_study' => '📋',
            'appendix' => '📎',
            'reference' => '📚',
            'summary' => '📝',
            'other' => '📄'
        ];

        return $icons[$assetType] ?? '📄';
    }

    // 상태별 색상 반환
    private function getStatusColor($status)
    {
        $colors = [
            'processing' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800'
        ];

        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }

    // 상태별 아이콘 반환
    private function getStatusIcon($status)
    {
        $icons = [
            'processing' => '⏳',
            'completed' => '✅',
            'failed' => '❌'
        ];

        return $icons[$status] ?? '❓';
    }

    // 컨텐츠 미리보기
    private function getContentPreview($content)
    {
        return mb_substr(strip_tags($content), 0, 200) . (mb_strlen($content) > 200 ? '...' : '');
    }

    // Getter 메서드들
    public function getId() { return $this->id; }
    public function getFileId() { return $this->file_id; }
    public function getAssetType() { return $this->asset_type; }
    public function getSectionTitle() { return $this->section_title; }
    public function getOrderIndex() { return $this->order_index; }
    public function getContent() { return $this->content; }
    public function getMetadata() { return $this->metadata; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // 배열로 변환
    public function toArray()
    {
        return [
            'id' => $this->id,
            'file_id' => $this->file_id,
            'asset_type' => $this->asset_type,
            'asset_type_name' => self::ASSET_TYPES[$this->asset_type] ?? '알 수 없음',
            'asset_type_color' => $this->getAssetTypeColor($this->asset_type),
            'asset_type_icon' => $this->getAssetTypeIcon($this->asset_type),
            'section_title' => $this->section_title,
            'content' => $this->content,
            'content_preview' => $this->getContentPreview($this->content),
            'order_index' => $this->order_index,
            'metadata' => $this->metadata,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}