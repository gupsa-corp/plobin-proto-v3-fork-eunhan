<?php
/**
 * 새 컬럼 생성
 * POST /columns
 */

// 공통 설정 로드
require_once __DIR__ . '/../../common.php';

try {
    // JSON 입력 데이터 읽기
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        return [
            'success' => false,
            'message' => '유효한 JSON 데이터가 필요합니다.'
        ];
    }

    // 필수 필드 검증
    if (empty($data['column_name']) || empty($data['column_label'])) {
        return [
            'success' => false,
            'message' => '컬럼명과 표시 이름은 필수입니다.'
        ];
    }

    // 컬럼명 유효성 검증 (영문, 숫자, 언더스코어만 허용)
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $data['column_name'])) {
        return [
            'success' => false,
            'message' => '컬럼명은 영문, 숫자, 언더스코어로만 구성되어야 합니다.'
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

    // 컬럼명 중복 체크
    $checkStmt = $pdo->prepare("SELECT id FROM project_columns WHERE column_name = ?");
    $checkStmt->execute([$data['column_name']]);
    if ($checkStmt->fetch()) {
        return [
            'success' => false,
            'message' => '이미 존재하는 컬럼명입니다.'
        ];
    }

    // 기본값 설정
    $columnData = [
        'column_name' => $data['column_name'],
        'column_type' => $data['column_type'] ?? 'TEXT',
        'column_label' => $data['column_label'],
        'default_value' => $data['default_value'] ?? null,
        'is_required' => isset($data['is_required']) ? (int)$data['is_required'] : 0,
        'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
        'validation_rule' => $data['validation_rule'] ?? null,
        'display_type' => $data['display_type'] ?? 'input',
        'options' => isset($data['options']) ? json_encode($data['options']) : '[]',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // SQL 쿼리 준비
    $columns = implode(', ', array_keys($columnData));
    $placeholders = implode(', ', array_fill(0, count($columnData), '?'));

    $sql = "INSERT INTO project_columns ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // 값 바인딩
    $values = array_values($columnData);
    $result = $stmt->execute($values);

    if (!$result) {
        return [
            'success' => false,
            'message' => '컬럼 생성에 실패했습니다.'
        ];
    }

    // 생성된 컬럼의 ID 가져오기
    $columnId = $pdo->lastInsertId();

    // 생성된 컬럼 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM project_columns WHERE id = ?");
    $stmt->execute([$columnId]);
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => '컬럼이 성공적으로 생성되었습니다.',
        'data' => $column
    ];

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    error_log('Create Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '컬럼 생성 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}
