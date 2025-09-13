<?php
/**
 * 사용자 컬럼 설정 업데이트 API
 * PUT /user-column-settings
 */

// 공통 설정 파일 포함
require_once __DIR__ . '/../../common.php';

try {
    $config = getSandboxConfig();
    $pdo = new PDO("sqlite:" . $config['database']['path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 요청 데이터 읽기
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        return [
            'success' => false,
            'message' => 'Invalid JSON data'
        ];
    }

    // 현재는 기본 사용자 ID = 1 사용
    $userId = 1;
    $screenType = $data['screen_type'] ?? 'table_view';
    $columnName = $data['column_name'] ?? '';
    $isVisible = $data['is_visible'] ?? true;
    $columnOrder = $data['column_order'] ?? null;

    if (empty($columnName)) {
        return [
            'success' => false,
            'message' => '컬럼명이 필요합니다'
        ];
    }

    // 특정 컬럼 설정 업데이트
    $updateFields = ['is_visible = :is_visible', 'updated_at = CURRENT_TIMESTAMP'];
    $params = [
        'user_id' => $userId,
        'screen_type' => $screenType,
        'column_name' => $columnName,
        'is_visible' => $isVisible ? 1 : 0
    ];

    if ($columnOrder !== null) {
        $updateFields[] = 'column_order = :column_order';
        $params['column_order'] = (int)$columnOrder;
    }

    $sql = "
        INSERT INTO user_column_settings (user_id, screen_type, column_name, is_visible, column_order) 
        VALUES (:user_id, :screen_type, :column_name, :is_visible, :column_order)
        ON CONFLICT(user_id, screen_type, column_name) 
        DO UPDATE SET " . implode(', ', $updateFields);

    if ($columnOrder === null) {
        $params['column_order'] = 0; // 기본값
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return [
        'success' => true,
        'message' => '컬럼 설정이 업데이트되었습니다',
        'column_name' => $columnName,
        'is_visible' => $isVisible,
        'column_order' => $params['column_order']
    ];

} catch (Exception $e) {
    error_log('사용자 컬럼 설정 업데이트 오류: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '컬럼 설정 업데이트에 실패했습니다: ' . $e->getMessage()
    ];
}
?>