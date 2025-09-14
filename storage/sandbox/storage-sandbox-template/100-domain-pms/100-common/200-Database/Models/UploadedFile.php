<?php

/**
 * 샌드박스용 UploadedFile 모델
 * 업로드된 파일 정보 관리
 */
class UploadedFile
{
    private $db;
    private $id;
    private $file_name;
    private $original_name;
    private $file_path;
    private $file_size;
    private $mime_type;
    private $is_analysis_requested;
    private $is_analysis_completed;
    private $analysis_status;
    private $analysis_requested_at;
    private $analysis_completed_at;
    private $created_at;
    private $updated_at;

    // 분석 상태 정의
    public const ANALYSIS_STATUS = [
        'pending' => '대기중',
        'processing' => '분석중',
        'completed' => '완료',
        'failed' => '실패'
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

    public static function findAll()
    {
        $instance = new self();
        return $instance->getAll();
    }

    public static function find($id)
    {
        $instance = new self();
        return $instance->findById($id);
    }

    public static function findAnalyzed()
    {
        $instance = new self();
        return $instance->getAnalyzedFiles();
    }

    // 데이터 저장
    public function save($data)
    {
        if (isset($this->id)) {
            // 업데이트
            return $this->update($data);
        }

        // 새로 생성
        $sql = "INSERT INTO uploaded_files (
            file_name, original_name, file_path, file_size, mime_type, 
            is_analysis_requested, is_analysis_completed, analysis_status,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['file_name'],
            $data['original_name'],
            $data['file_path'],
            $data['file_size'] ?? 0,
            $data['mime_type'] ?? 'application/octet-stream',
            $data['is_analysis_requested'] ?? 0,
            $data['is_analysis_completed'] ?? 0,
            $data['analysis_status'] ?? 'pending'
        ]);

        if ($result) {
            $this->id = $this->db->lastInsertId();
            $this->loadData($data);
            return $this;
        }

        return false;
    }

    // 업데이트
    public function update($data)
    {
        $sql = "UPDATE uploaded_files SET 
            file_name = ?, original_name = ?, file_path = ?, file_size = ?, 
            mime_type = ?, is_analysis_requested = ?, is_analysis_completed = ?, 
            analysis_status = ?, analysis_requested_at = ?, analysis_completed_at = ?,
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['file_name'] ?? $this->file_name,
            $data['original_name'] ?? $this->original_name,
            $data['file_path'] ?? $this->file_path,
            $data['file_size'] ?? $this->file_size,
            $data['mime_type'] ?? $this->mime_type,
            $data['is_analysis_requested'] ?? $this->is_analysis_requested,
            $data['is_analysis_completed'] ?? $this->is_analysis_completed,
            $data['analysis_status'] ?? $this->analysis_status,
            $data['analysis_requested_at'] ?? $this->analysis_requested_at,
            $data['analysis_completed_at'] ?? $this->analysis_completed_at,
            $this->id
        ]);

        if ($result) {
            $this->loadData($data);
        }

        return $result;
    }

    // ID로 조회
    public function findById($id)
    {
        $sql = "SELECT * FROM uploaded_files WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $this->loadData($data);
            return $this;
        }

        return null;
    }

    // 모든 파일 조회
    public function getAll()
    {
        $sql = "SELECT * FROM uploaded_files ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $files = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $file = new self($this->db);
            $file->loadData($row);
            $files[] = $file;
        }

        return $files;
    }

    // 분석 완료된 파일들 조회
    public function getAnalyzedFiles()
    {
        $sql = "SELECT * FROM uploaded_files WHERE is_analysis_completed = 1 ORDER BY analysis_completed_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $files = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $file = new self($this->db);
            $file->loadData($row);
            $files[] = $file;
        }

        return $files;
    }

    // 분석 요청
    public function requestAnalysis()
    {
        $sql = "UPDATE uploaded_files SET 
            is_analysis_requested = 1, 
            analysis_status = 'processing',
            analysis_requested_at = datetime('now'),
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$this->id]);

        if ($result) {
            $this->is_analysis_requested = 1;
            $this->analysis_status = 'processing';
            $this->analysis_requested_at = date('Y-m-d H:i:s');
        }

        return $result;
    }

    // 분석 완료 처리
    public function completeAnalysis($status = 'completed')
    {
        $sql = "UPDATE uploaded_files SET 
            is_analysis_completed = 1, 
            analysis_status = ?,
            analysis_completed_at = datetime('now'),
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$status, $this->id]);

        if ($result) {
            $this->is_analysis_completed = 1;
            $this->analysis_status = $status;
            $this->analysis_completed_at = date('Y-m-d H:i:s');
        }

        return $result;
    }

    // 분석 실패 처리
    public function failAnalysis($errorMessage = null)
    {
        $sql = "UPDATE uploaded_files SET 
            analysis_status = 'failed',
            updated_at = datetime('now')
        WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$this->id]);

        if ($result) {
            $this->analysis_status = 'failed';
        }

        return $result;
    }

    // 관련된 문서 에셋 조회
    public function getDocumentAssets()
    {
        require_once __DIR__ . '/DocumentAsset.php';
        return DocumentAsset::findByFileId($this->id);
    }

    // 데이터 로드
    private function loadData($data)
    {
        $this->id = $data['id'] ?? $this->id;
        $this->file_name = $data['file_name'] ?? $this->file_name;
        $this->original_name = $data['original_name'] ?? $this->original_name;
        $this->file_path = $data['file_path'] ?? $this->file_path;
        $this->file_size = $data['file_size'] ?? $this->file_size;
        $this->mime_type = $data['mime_type'] ?? $this->mime_type;
        $this->is_analysis_requested = $data['is_analysis_requested'] ?? $this->is_analysis_requested;
        $this->is_analysis_completed = $data['is_analysis_completed'] ?? $this->is_analysis_completed;
        $this->analysis_status = $data['analysis_status'] ?? $this->analysis_status;
        $this->analysis_requested_at = $data['analysis_requested_at'] ?? $this->analysis_requested_at;
        $this->analysis_completed_at = $data['analysis_completed_at'] ?? $this->analysis_completed_at;
        $this->created_at = $data['created_at'] ?? $this->created_at;
        $this->updated_at = $data['updated_at'] ?? $this->updated_at;
    }

    // 파일 크기를 읽기 쉬운 형태로 변환
    private function formatFileSize($size)
    {
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }

    // 분석 상태별 색상 반환
    private function getStatusColor($status)
    {
        $colors = [
            'pending' => 'bg-gray-100 text-gray-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800'
        ];

        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }

    // 분석 상태별 아이콘 반환
    private function getStatusIcon($status)
    {
        $icons = [
            'pending' => '⏳',
            'processing' => '🔄',
            'completed' => '✅',
            'failed' => '❌'
        ];

        return $icons[$status] ?? '❓';
    }

    // Getter 메서드들
    public function getId() { return $this->id; }
    public function getFileName() { return $this->file_name; }
    public function getOriginalName() { return $this->original_name; }
    public function getFilePath() { return $this->file_path; }
    public function getFileSize() { return $this->file_size; }
    public function getMimeType() { return $this->mime_type; }
    public function isAnalysisRequested() { return $this->is_analysis_requested; }
    public function isAnalysisCompleted() { return $this->is_analysis_completed; }
    public function getAnalysisStatus() { return $this->analysis_status; }
    public function getAnalysisRequestedAt() { return $this->analysis_requested_at; }
    public function getAnalysisCompletedAt() { return $this->analysis_completed_at; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // 배열로 변환
    public function toArray()
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'original_name' => $this->original_name,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'file_size_formatted' => $this->formatFileSize($this->file_size),
            'mime_type' => $this->mime_type,
            'is_analysis_requested' => $this->is_analysis_requested,
            'is_analysis_completed' => $this->is_analysis_completed,
            'analysis_status' => $this->analysis_status,
            'analysis_status_name' => self::ANALYSIS_STATUS[$this->analysis_status] ?? '알 수 없음',
            'analysis_status_color' => $this->getStatusColor($this->analysis_status),
            'analysis_status_icon' => $this->getStatusIcon($this->analysis_status),
            'analysis_requested_at' => $this->analysis_requested_at,
            'analysis_completed_at' => $this->analysis_completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}