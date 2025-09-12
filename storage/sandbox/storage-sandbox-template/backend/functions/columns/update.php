<?php
/**
 * 컬럼 정보 업데이트
 * PUT /columns/{id}
 */

// 공통 설정 로드
require_once __DIR__ . '/../../common.php';

try {
    // 컬럼 ID 가져오기
    $columnId = $GLOBALS['id'] ?? null;

    if (!$columnId) {
        return [
            'success' => false,
            'message' => '컬럼 ID가 필요합니다.'
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
        'column_label',
        'default_value',
        'is_required',
        'is_active',
        'sort_order',
        'validation_rule',
        'display_type',
        'options'
    ];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            if ($field === 'options' && is_array($data[$field])) {
                $values[] = json_encode($data[$field]);
            } else {
                $values[] = $data[$field];
            }
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

    // 컬럼 ID 추가
    $values[] = $columnId;

    // SQL 쿼리 실행
    $sql = "UPDATE project_columns SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($values);

    if (!$result || $stmt->rowCount() === 0) {
        return [
            'success' => false,
            'message' => '컬럼 업데이트에 실패했거나 해당 컬럼을 찾을 수 없습니다.'
        ];
    }

    // 업데이트된 컬럼 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM project_columns WHERE id = ?");
    $stmt->execute([$columnId]);
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => '컬럼이 성공적으로 업데이트되었습니다.',
        'data' => $column
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
        'message' => '컬럼 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}
