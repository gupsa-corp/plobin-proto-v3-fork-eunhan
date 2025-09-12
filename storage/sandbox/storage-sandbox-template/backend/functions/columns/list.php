<?php
/**
 * 컬럼 목록 조회
 * GET /columns
 */

// 공통 설정 로드
require_once __DIR__ . '/../../common.php';

try {
    // 데이터베이스 연결
    $config = getSandboxConfig();
    $pdo = new PDO(
        "sqlite:" . $config['database']['path'],
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 쿼리 파라미터 처리
    $type = $_GET['type'] ?? 'all';
    $whereClause = "WHERE is_active = 1";

    if ($type === 'custom') {
        $whereClause .= " AND (is_system = 0 OR is_system IS NULL)";
    } elseif ($type === 'system') {
        $whereClause .= " AND is_system = 1";
    }

    // 컬럼 목록 조회
    $stmt = $pdo->prepare("
        SELECT
            id,
            column_name,
            column_type,
            column_label,
            default_value,
            is_required,
            is_active,
            sort_order,
            validation_rule,
            display_type,
            options,
            is_system,
            created_at,
            updated_at
        FROM project_columns
        $whereClause
        ORDER BY sort_order ASC, column_label ASC
    ");

    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => '컬럼 목록을 성공적으로 조회했습니다.',
        'data' => $columns
    ];

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    error_log('List Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '컬럼 목록 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}
