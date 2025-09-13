<?php
/**
 * 새 칸반 카드 생성
 */

// PMS 데이터베이스 시스템 로드
require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../../frontend/100-pms-common/001-database/pms-database.php';

try {
    $db = PMSDatabase::getInstance();
    
    // 요청 데이터 파싱
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        return [
            'success' => false,
            'message' => '요청 데이터가 유효하지 않습니다.'
        ];
    }
    
    // 필수 필드 검증
    if (empty($input['title'])) {
        return [
            'success' => false,
            'message' => '카드 제목이 필요합니다.'
        ];
    }
    
    if (empty($input['column_id'])) {
        return [
            'success' => false,
            'message' => '컬럼 ID가 필요합니다.'
        ];
    }
    
    // 상태 매핑 (프론트엔드 컬럼 ID -> DB 상태)
    $statusMap = [
        'todo' => 'planned',
        'in-progress' => 'in_progress', 
        'review' => 'on_hold',
        'done' => 'completed'
    ];
    
    $status = $statusMap[$input['column_id']] ?? 'planned';
    
    // 진행률 설정
    $progress = 0;
    if ($input['column_id'] === 'done') {
        $progress = 100;
    } elseif ($input['column_id'] === 'in-progress') {
        $progress = 25; // 기본 진행률
    }
    
    // 새 카드 데이터 준비
    $cardData = [
        'name' => $input['title'],
        'description' => $input['description'] ?? '',
        'status' => $status,
        'priority' => $input['priority'] ?? 'medium',
        'progress' => $progress,
        'team_members' => $input['team_members'] ?? 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // 카드 생성
    $cardId = $db->insert("
        INSERT INTO projects (name, description, status, priority, progress, team_members, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ", [
        $cardData['name'],
        $cardData['description'],
        $cardData['status'],
        $cardData['priority'],
        $cardData['progress'],
        $cardData['team_members'],
        $cardData['created_at'],
        $cardData['updated_at']
    ]);
    
    // 생성된 카드 정보 조회
    $newCard = $db->fetchRow("
        SELECT 
            id,
            name as title,
            description,
            status,
            priority,
            progress,
            team_members,
            created_at,
            updated_at
        FROM projects 
        WHERE id = ?
    ", [$cardId]);
    
    // 컬럼 ID 추가
    $newCard['column_id'] = $input['column_id'];
    $newCard['assignee'] = '팀 멤버'; // 임시값
    
    return [
        'success' => true,
        'message' => '새 카드가 성공적으로 생성되었습니다.',
        'data' => [
            'card' => $newCard
        ]
    ];
    
} catch (Exception $e) {
    error_log('Kanban create card error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '카드 생성 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}