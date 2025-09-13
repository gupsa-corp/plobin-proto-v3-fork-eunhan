<?php
/**
 * 사용자 컬럼 설정 저장 API
 * POST /user-column-settings
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
    $columnSettings = $data['column_settings'] ?? [];

    if (empty($columnSettings)) {
        return [
            'success' => false,
            'message' => '컬럼 설정 데이터가 필요합니다'
        ];
    }

    $pdo->beginTransaction();

    try {
        // 기존 설정 삭제
        $deleteStmt = $pdo->prepare("
            DELETE FROM user_column_settings 
            WHERE user_id = :user_id AND screen_type = :screen_type
        ");
        $deleteStmt->execute([
            'user_id' => $userId,
            'screen_type' => $screenType
        ]);

        // 새 설정 저장
        $insertStmt = $pdo->prepare("
            INSERT INTO user_column_settings 
            (user_id, screen_type, column_name, is_visible, column_order) 
            VALUES (:user_id, :screen_type, :column_name, :is_visible, :column_order)
        ");

        $order = 1;
        foreach ($columnSettings as $columnName => $setting) {
            $isVisible = $setting['is_visible'] ?? $setting ?? 1; // 이전 버전 호환성
            
            $insertStmt->execute([
                'user_id' => $userId,
                'screen_type' => $screenType,
                'column_name' => $columnName,
                'is_visible' => $isVisible ? 1 : 0,
                'column_order' => $setting['column_order'] ?? $order++
            ]);
        }

        $pdo->commit();

        return [
            'success' => true,
            'message' => '컬럼 설정이 저장되었습니다',
            'saved_count' => count($columnSettings)
        ];

    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log('사용자 컬럼 설정 저장 오류: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '컬럼 설정 저장에 실패했습니다: ' . $e->getMessage()
    ];
}
?>