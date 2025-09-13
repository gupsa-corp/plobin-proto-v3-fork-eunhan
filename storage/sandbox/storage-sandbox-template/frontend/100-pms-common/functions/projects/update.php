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
        'budget',
        'start_date',
        'end_date'
    ];

    $hasCustomFields = false;
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    // 커스텀 필드 확인
    foreach ($data as $key => $value) {
        if (strpos($key, 'custom_') === 0) {
            $hasCustomFields = true;
            break;
        }
    }

    // 기본 필드와 커스텀 필드 둘 다 없으면 에러
    if (empty($updateFields) && !$hasCustomFields) {
        return [
            'success' => false,
            'message' => '업데이트할 필드가 없습니다.'
        ];
    }

    // 기본 필드가 있는 경우에만 projects 테이블 업데이트
    if (!empty($updateFields)) {
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
    }

    // 커스텀 데이터 처리
    $customFields = [];
    
    // custom_data 형태로 온 경우
    if (isset($data['custom_data']) && is_array($data['custom_data'])) {
        $customFields = $data['custom_data'];
    }
    
    // 개별 custom_ 필드들도 처리
    foreach ($data as $key => $value) {
        if (strpos($key, 'custom_') === 0) {
            $columnName = substr($key, 7); // 'custom_' 제거
            $customFields[$columnName] = $value;
        }
    }
    
    // 커스텀 필드가 있는 경우 처리
    if (!empty($customFields)) {
        // 먼저 해당 프로젝트의 모든 커스텀 데이터를 조회
        $selectCustomStmt = $pdo->prepare("
            SELECT column_name, column_value 
            FROM project_custom_data 
            WHERE project_id = ?
        ");
        $selectCustomStmt->execute([$projectId]);
        $existingCustomData = $selectCustomStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 개별 커스텀 필드 업데이트/삽입
        foreach ($customFields as $columnName => $columnValue) {
            if (isset($existingCustomData[$columnName])) {
                // 기존 데이터 업데이트
                $updateCustomStmt = $pdo->prepare("
                    UPDATE project_custom_data 
                    SET column_value = ? 
                    WHERE project_id = ? AND column_name = ?
                ");
                $updateCustomStmt->execute([$columnValue, $projectId, $columnName]);
            } else {
                // 새 데이터 삽입
                $insertCustomStmt = $pdo->prepare("
                    INSERT INTO project_custom_data (project_id, column_name, column_value)
                    VALUES (?, ?, ?)
                ");
                $insertCustomStmt->execute([$projectId, $columnName, $columnValue]);
            }
        }
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
