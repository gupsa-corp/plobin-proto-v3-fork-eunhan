<?php
/**
 * 프로젝트 정보 업데이트
 * PUT /projects/{id}
 */

// 공통 설정 로드
require_once __DIR__ . '/../../common.php';

try {
    // 프로젝트 ID 가져오기
    $projectId = $GLOBALS['id'] ?? null;
    
    if (!$projectId) {
        return [
            'success' => false,
            'message' => '프로젝트 ID가 필요합니다.'
        ];
    }
    
    // JSON 입력 데이터 읽기
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        return [
            'success' => false,
            'message' => '유효한 JSON 데이터가 필요합니다.'
        ];
    }
    
    // 데이터베이스 연결
    $config = getSandboxConfig();
    $pdo = new PDO(
        "sqlite:" . $config['database']['path'],
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 업데이트할 필드 구성
    $updateFields = [];
    $values = [];
    
    // 허용된 필드만 업데이트
    $allowedFields = [
        'name',
        'description', 
        'status',
        'progress',
        'team_members',
        'priority',
        'client',
        'budget'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        return [
            'success' => false,
            'message' => '업데이트할 필드가 없습니다.'
        ];
    }
    
    // 업데이트 날짜 추가
    $updateFields[] = "updated_at = ?";
    $values[] = date('Y-m-d H:i:s');
    
    // 프로젝트 ID 추가
    $values[] = $projectId;
    
    // SQL 쿼리 실행
    $sql = "UPDATE projects SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($values);
    
    if (!$result || $stmt->rowCount() === 0) {
        return [
            'success' => false,
            'message' => '프로젝트 업데이트에 실패했거나 해당 프로젝트를 찾을 수 없습니다.'
        ];
    }
    
    // 업데이트된 프로젝트 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'message' => '프로젝트가 성공적으로 업데이트되었습니다.',
        'data' => $project
    ];
    
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    error_log('Update Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '프로젝트 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}