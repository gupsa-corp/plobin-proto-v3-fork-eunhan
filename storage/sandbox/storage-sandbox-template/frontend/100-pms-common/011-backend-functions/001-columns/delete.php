<?php
/**
 * 컬럼 삭제
 * DELETE /columns/{id}
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

    // 컬럼 ID가 숫자인지 검증
    if (!is_numeric($columnId)) {
        return [
            'success' => false,
            'message' => '유효하지 않은 컬럼 ID입니다.'
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

    // 삭제할 컬럼이 존재하는지 확인
    $checkStmt = $pdo->prepare("SELECT id, column_name, is_system FROM project_columns WHERE id = ?");
    $checkStmt->execute([$columnId]);
    $column = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$column) {
        return [
            'success' => false,
            'message' => '삭제할 컬럼을 찾을 수 없습니다.'
        ];
    }

    // 시스템 컬럼인지 확인 (시스템 컬럼은 삭제 불가)
    if ($column['is_system'] == 1) {
        return [
            'success' => false,
            'message' => '시스템 컬럼은 삭제할 수 없습니다.'
        ];
    }

    // 트랜잭션 시작
    $pdo->beginTransaction();

    try {
        // 해당 컬럼의 모든 커스텀 데이터 삭제
        $deleteDataStmt = $pdo->prepare("DELETE FROM project_custom_data WHERE column_name = ?");
        $deleteDataStmt->execute([$column['column_name']]);

        // 컬럼 삭제 실행
        $deleteStmt = $pdo->prepare("DELETE FROM project_columns WHERE id = ?");
        $result = $deleteStmt->execute([$columnId]);

        if (!$result || $deleteStmt->rowCount() === 0) {
            $pdo->rollBack();
            return [
                'success' => false,
                'message' => '컬럼 삭제에 실패했습니다.'
            ];
        }

        // 트랜잭션 커밋
        $pdo->commit();

        return [
            'success' => true,
            'message' => '컬럼이 성공적으로 삭제되었습니다.',
            'data' => [
                'id' => $columnId,
                'name' => $column['column_name']
            ]
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    error_log('Delete Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '컬럼 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}
