<?php

/**
 * 샌드박스용 AssetSummary 모델
 * 에셋 AI 요약 관리
 */
class AssetSummary
{
    private $db;
    private $id;
    private $asset_id;
    private $ai_summary;
    private $helpful_content;
    private $analysis_status;
    private $analysis_metadata;
    private $version_count;
    private $current_version;
    private $created_at;
    private $updated_at;

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

    public static function findByAssetId($assetId)
    {
        $instance = new self();
        return $instance->getByAssetId($assetId);
    }

    public static function find($id)
    {
        $instance = new self();
        return $instance->findById($id);
    }

    // 데이터 저장
    public function save($data)
    {
        $sql = "INSERT INTO asset_summaries (
            asset_id, ai_summary, helpful_content, analysis_status, 
            analysis_metadata, version_count, current_version, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['asset_id'],
            $data['ai_summary'],
            $data['helpful_content'],
            $data['analysis_status'] ?? 'completed',
            json_encode($data['analysis_metadata'] ?? []),
            $data['version_count'] ?? 1,
            $data['current_version'] ?? 1
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
        $sql = "SELECT * FROM asset_summaries WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->loadData($data);
            return $this;
        }

        return null;
    }

    // 에셋 ID로 조회
    public function getByAssetId($assetId)
    {
        $sql = "SELECT * FROM asset_summaries WHERE asset_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assetId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->loadData($data);
            return $this;
        }

        return null;
    }

    // 요약 업데이트
    public function updateSummary($data)
    {
        $sql = "UPDATE asset_summaries SET 
            ai_summary = ?, 
            helpful_content = ?, 
            analysis_status = ?, 
            analysis_metadata = ?, 
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['ai_summary'] ?? $this->ai_summary,
            $data['helpful_content'] ?? $this->helpful_content,
            $data['analysis_status'] ?? $this->analysis_status,
            json_encode($data['analysis_metadata'] ?? json_decode($this->analysis_metadata, true)),
            $this->id
        ]);

        if ($result) {
            $this->loadData($data);
        }

        return $result;
    }

    // 버전 수 증가
    public function incrementVersion()
    {
        $sql = "UPDATE asset_summaries SET 
            version_count = version_count + 1, 
            current_version = current_version + 1,
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$this->id]);

        if ($result) {
            $this->version_count++;
            $this->current_version++;
        }

        return $result;
    }

    // 현재 버전 변경
    public function setCurrentVersion($version)
    {
        $sql = "UPDATE asset_summaries SET 
            current_version = ?,
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$version, $this->id]);

        if ($result) {
            $this->current_version = $version;
        }

        return $result;
    }

    // 버전 히스토리 조회
    public function getVersionHistory()
    {
        $sql = "SELECT * FROM summary_versions WHERE summary_id = ? ORDER BY version_number DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 현재 버전의 내용 조회
    public function getCurrentVersionContent()
    {
        $sql = "SELECT * FROM summary_versions 
                WHERE summary_id = ? AND version_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id, $this->current_version]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 데이터 로드
    private function loadData($data)
    {
        $this->id = $data['id'] ?? $this->id;
        $this->asset_id = $data['asset_id'] ?? $this->asset_id;
        $this->ai_summary = $data['ai_summary'] ?? $this->ai_summary;
        $this->helpful_content = $data['helpful_content'] ?? $this->helpful_content;
        $this->analysis_status = $data['analysis_status'] ?? $this->analysis_status;
        $this->analysis_metadata = is_string($data['analysis_metadata'] ?? null) ? 
            $data['analysis_metadata'] : json_encode($data['analysis_metadata'] ?? []);
        $this->version_count = $data['version_count'] ?? $this->version_count;
        $this->current_version = $data['current_version'] ?? $this->current_version;
        $this->created_at = $data['created_at'] ?? $this->created_at;
        $this->updated_at = $data['updated_at'] ?? $this->updated_at;
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

    // Getter 메서드들
    public function getId() { return $this->id; }
    public function getAssetId() { return $this->asset_id; }
    public function getAiSummary() { return $this->ai_summary; }
    public function getHelpfulContent() { return $this->helpful_content; }
    public function getAnalysisStatus() { return $this->analysis_status; }
    public function getAnalysisMetadata() { 
        return is_string($this->analysis_metadata) ? 
            json_decode($this->analysis_metadata, true) : $this->analysis_metadata;
    }
    public function getVersionCount() { return $this->version_count; }
    public function getCurrentVersion() { return $this->current_version; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // 배열로 변환
    public function toArray()
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'ai_summary' => $this->ai_summary,
            'helpful_content' => $this->helpful_content,
            'analysis_status' => $this->analysis_status,
            'status_color' => $this->getStatusColor($this->analysis_status),
            'status_icon' => $this->getStatusIcon($this->analysis_status),
            'analysis_metadata' => $this->getAnalysisMetadata(),
            'version_count' => $this->version_count,
            'current_version' => $this->current_version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}