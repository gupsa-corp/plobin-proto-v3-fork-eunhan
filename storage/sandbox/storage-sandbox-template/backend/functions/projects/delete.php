<?php
/**
 * 프로젝트 삭제
 * DELETE /projects/{id}
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

    // 프로젝트 ID가 숫자인지 검증
    if (!is_numeric($projectId)) {
        return [
            'success' => false,
            'message' => '유효하지 않은 프로젝트 ID입니다.'
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

    // 삭제할 프로젝트가 존재하는지 확인
    $checkStmt = $pdo->prepare("SELECT id, name FROM projects WHERE id = ?");
    $checkStmt->execute([$projectId]);
    $project = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        return [
            'success' => false,
            'message' => '삭제할 프로젝트를 찾을 수 없습니다.'
        ];
    }

    // 프로젝트 삭제 실행
    $deleteStmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $result = $deleteStmt->execute([$projectId]);

    if (!$result || $deleteStmt->rowCount() === 0) {
        return [
            'success' => false,
            'message' => '프로젝트 삭제에 실패했습니다.'
        ];
    }

    return [
        'success' => true,
        'message' => '프로젝트가 성공적으로 삭제되었습니다.',
        'data' => [
            'id' => $projectId,
            'name' => $project['name']
        ]
    ];

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
        'message' => '프로젝트 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}
