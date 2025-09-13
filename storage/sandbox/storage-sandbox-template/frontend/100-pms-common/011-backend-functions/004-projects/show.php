<?php
/**
 * 프로젝트 정보 조회
 * GET /projects/{id}
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
    
    // 데이터베이스 연결
    $config = getSandboxConfig();
    $pdo = new PDO(
        "sqlite:" . $config['database']['path'],
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 프로젝트 조회
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        return [
            'success' => false,
            'message' => '해당 프로젝트를 찾을 수 없습니다.'
        ];
    }
    
    return [
        'success' => true,
        'message' => '프로젝트 정보를 성공적으로 조회했습니다.',
        'data' => $project
    ];
    
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    error_log('Show Error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '프로젝트 조회 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}