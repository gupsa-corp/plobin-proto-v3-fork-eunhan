<?php
/**
 * 새 프로젝트 생성
 * POST /projects
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
    if (empty($data['name'])) {
        return [
            'success' => false,
            'message' => '프로젝트명은 필수입니다.'
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

    // 기본값 설정
    $projectData = [
        'name' => $data['name'],
        'description' => $data['description'] ?? '',
        'status' => $data['status'] ?? 'planned',
        'progress' => isset($data['progress']) ? (int)$data['progress'] : 0,
        'team_members' => isset($data['team_members']) ? (int)$data['team_members'] : 1,
        'priority' => $data['priority'] ?? 'medium',
        'client' => $data['client'] ?? '',
        'budget' => isset($data['budget']) ? (int)$data['budget'] : 0,
        'category' => $data['category'] ?? '',
        'start_date' => $data['start_date'] ?? null,
        'end_date' => $data['end_date'] ?? null,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // SQL 쿼리 준비
    $columns = implode(', ', array_keys($projectData));
    $placeholders = implode(', ', array_fill(0, count($projectData), '?'));

    $sql = "INSERT INTO projects ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);

    // 값 바인딩
    $values = array_values($projectData);
    $result = $stmt->execute($values);

    if (!$result) {
        return [
            'success' => false,
            'message' => '프로젝트 생성에 실패했습니다.'
        ];
    }

    // 생성된 프로젝트의 ID 가져오기
    $projectId = $pdo->lastInsertId();

    // 생성된 프로젝트 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => '프로젝트가 성공적으로 생성되었습니다.',
        'data' => $project
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
        'message' => '프로젝트 생성 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}
