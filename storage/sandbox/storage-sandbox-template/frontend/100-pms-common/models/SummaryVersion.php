<?php

/**
 * 샌드박스용 SummaryVersion 모델
 * 요약 버전 히스토리 관리
 */
class SummaryVersion
{
    private $db;
    private $id;
    private $summary_id;
    private $version_number;
    private $ai_summary;
    private $helpful_content;
    private $edit_type;
    private $edit_notes;
    private $is_current;
    private $created_at;

    // 편집 타입 정의
    public const EDIT_TYPES = [
        'ai_generated' => 'AI 생성',
        'user_edit' => '사용자 편집',
        'auto_improved' => '자동 개선'
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

    public static function findBySummaryId($summaryId)
    {
        $instance = new self();
        return $instance->getBySummaryId($summaryId);
    }

    public static function find($id)
    {
        $instance = new self();
        return $instance->findById($id);
    }

    public static function findBySummaryAndVersion($summaryId, $version)
    {
        $instance = new self();
        return $instance->getBySummaryAndVersion($summaryId, $version);
    }

    // 데이터 저장
    public function save($data)
    {
        // 기존 current 버전을 false로 변경 (새 버전이 current가 될 때)
        if ($data['is_current'] ?? false) {
            $this->unsetCurrentVersion($data['summary_id']);
        }

        $sql = "INSERT INTO summary_versions (
            summary_id, version_number, ai_summary, helpful_content, 
            edit_type, edit_notes, is_current, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['summary_id'],
            $data['version_number'],
            $data['ai_summary'],
            $data['helpful_content'],
            $data['edit_type'] ?? 'user_edit',
            $data['edit_notes'] ?? null,
            $data['is_current'] ? 1 : 0
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
        $sql = "SELECT * FROM summary_versions WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->loadData($data);
            return $this;
        }

        return null;
    }

    // 요약 ID로 모든 버전 조회
    public function getBySummaryId($summaryId)
    {
        $sql = "SELECT * FROM summary_versions WHERE summary_id = ? ORDER BY version_number DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$summaryId]);
        
        $versions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $version = new self($this->db);
            $version->loadData($row);
            $versions[] = $version;
        }

        return $versions;
    }

    // 요약 ID와 버전 번호로 조회
    public function getBySummaryAndVersion($summaryId, $version)
    {
        $sql = "SELECT * FROM summary_versions WHERE summary_id = ? AND version_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$summaryId, $version]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->loadData($data);
            return $this;
        }

        return null;
    }

    // 현재 버전으로 설정
    public function setAsCurrent()
    {
        // 기존 current 버전을 false로 변경
        $this->unsetCurrentVersion($this->summary_id);

        // 현재 버전을 true로 설정
        $sql = "UPDATE summary_versions SET is_current = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$this->id]);

        if ($result) {
            $this->is_current = true;
        }

        return $result;
    }

    // 모든 버전의 current를 false로 변경
    private function unsetCurrentVersion($summaryId)
    {
        $sql = "UPDATE summary_versions SET is_current = 0 WHERE summary_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$summaryId]);
    }

    // 버전 업데이트
    public function update($data)
    {
        $sql = "UPDATE summary_versions SET 
            ai_summary = ?, 
            helpful_content = ?, 
            edit_type = ?, 
            edit_notes = ?
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['ai_summary'] ?? $this->ai_summary,
            $data['helpful_content'] ?? $this->helpful_content,
            $data['edit_type'] ?? $this->edit_type,
            $data['edit_notes'] ?? $this->edit_notes,
            $this->id
        ]);

        if ($result) {
            $this->loadData($data);
        }

        return $result;
    }

    // 현재 버전 가져오기
    public static function getCurrentVersion($summaryId)
    {
        $instance = new self();
        $sql = "SELECT * FROM summary_versions WHERE summary_id = ? AND is_current = 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute([$summaryId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $instance->loadData($data);
            return $instance;
        }

        return null;
    }

    // 다음 버전 번호 가져오기
    public static function getNextVersionNumber($summaryId)
    {
        $instance = new self();
        $sql = "SELECT MAX(version_number) as max_version FROM summary_versions WHERE summary_id = ?";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute([$summaryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result['max_version'] ?? 0) + 1;
    }

    // 데이터 로드
    private function loadData($data)
    {
        $this->id = $data['id'] ?? $this->id;
        $this->summary_id = $data['summary_id'] ?? $this->summary_id;
        $this->version_number = $data['version_number'] ?? $this->version_number;
        $this->ai_summary = $data['ai_summary'] ?? $this->ai_summary;
        $this->helpful_content = $data['helpful_content'] ?? $this->helpful_content;
        $this->edit_type = $data['edit_type'] ?? $this->edit_type;
        $this->edit_notes = $data['edit_notes'] ?? $this->edit_notes;
        $this->is_current = $data['is_current'] ?? $this->is_current;
        $this->created_at = $data['created_at'] ?? $this->created_at;
    }

    // 편집 타입별 색상 반환
    private function getEditTypeColor($editType)
    {
        $colors = [
            'ai_generated' => 'bg-blue-100 text-blue-800',
            'user_edit' => 'bg-green-100 text-green-800',
            'auto_improved' => 'bg-purple-100 text-purple-800'
        ];

        return $colors[$editType] ?? 'bg-gray-100 text-gray-800';
    }

    // 편집 타입별 아이콘 반환
    private function getEditTypeIcon($editType)
    {
        $icons = [
            'ai_generated' => '🤖',
            'user_edit' => '👤',
            'auto_improved' => '⚡'
        ];

        return $icons[$editType] ?? '📝';
    }

    // Getter 메서드들
    public function getId() { return $this->id; }
    public function getSummaryId() { return $this->summary_id; }
    public function getVersionNumber() { return $this->version_number; }
    public function getAiSummary() { return $this->ai_summary; }
    public function getHelpfulContent() { return $this->helpful_content; }
    public function getEditType() { return $this->edit_type; }
    public function getEditNotes() { return $this->edit_notes; }
    public function isCurrent() { return $this->is_current; }
    public function getCreatedAt() { return $this->created_at; }

    // 배열로 변환
    public function toArray()
    {
        return [
            'id' => $this->id,
            'summary_id' => $this->summary_id,
            'version_number' => $this->version_number,
            'ai_summary' => $this->ai_summary,
            'helpful_content' => $this->helpful_content,
            'edit_type' => $this->edit_type,
            'edit_type_name' => self::EDIT_TYPES[$this->edit_type] ?? '알 수 없음',
            'edit_type_color' => $this->getEditTypeColor($this->edit_type),
            'edit_type_icon' => $this->getEditTypeIcon($this->edit_type),
            'edit_notes' => $this->edit_notes,
            'is_current' => $this->is_current,
            'created_at' => $this->created_at
        ];
    }
}