<?php
/**
 * 칸반 카드 이동/수정
 */

// PMS 데이터베이스 시스템 로드
require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../../frontend/100-pms-common/001-database/pms-database.php';

try {
    $db = PMSDatabase::getInstance();
    
    // 카드 ID 가져오기
    $cardId = $GLOBALS['id'] ?? null;
    
    if (!$cardId) {
        return [
            'success' => false,
            'message' => '카드 ID가 필요합니다.'
        ];
    }
    
    // 요청 데이터 파싱
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        return [
            'success' => false,
            'message' => '요청 데이터가 유효하지 않습니다.'
        ];
    }
    
    // 카드 존재 여부 확인
    $existingCard = $db->fetchRow("SELECT * FROM projects WHERE id = ?", [$cardId]);
    
    if (!$existingCard) {
        return [
            'success' => false,
            'message' => '존재하지 않는 카드입니다.'
        ];
    }
    
    // 상태 매핑 (프론트엔드 컬럼 ID -> DB 상태)
    $statusMap = [
        'todo' => 'planned',
        'in-progress' => 'in_progress', 
        'review' => 'on_hold',  // 검토 중은 on_hold로 매핑
        'done' => 'completed'
    ];
    
    // 업데이트할 필드 준비
    $updateFields = [];
    $updateParams = [];
    
    // 상태 업데이트
    if (isset($input['status']) && isset($statusMap[$input['column_id']])) {
        $newStatus = $statusMap[$input['column_id']];
        $updateFields[] = "status = ?";
        $updateParams[] = $newStatus;
    }
    
    // 진행률 업데이트 (완료 상태면 100%, 계획/진행 중이면 기존 값 유지)
    if (isset($input['column_id'])) {
        if ($input['column_id'] === 'done') {
            $updateFields[] = "progress = ?";
            $updateParams[] = 100;
        } elseif ($input['column_id'] === 'todo') {
            $updateFields[] = "progress = ?";
            $updateParams[] = 0;
        }
    }
    
    // 제목 업데이트
    if (isset($input['title'])) {
        $updateFields[] = "name = ?";
        $updateParams[] = $input['title'];
    }
    
    // 설명 업데이트
    if (isset($input['description'])) {
        $updateFields[] = "description = ?";
        $updateParams[] = $input['description'];
    }
    
    // 우선순위 업데이트
    if (isset($input['priority'])) {
        $updateFields[] = "priority = ?";
        $updateParams[] = $input['priority'];
    }
    
    // 업데이트할 필드가 있는 경우만 실행
    if (!empty($updateFields)) {
        $updateParams[] = $cardId; // WHERE 조건용 ID
        
        $sql = "UPDATE projects SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $db->query($sql, $updateParams);
        
        // 업데이트된 카드 정보 조회
        $updatedCard = $db->fetchRow("
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
        
        // 컬럼 ID 추가 (상태 기반으로 매핑)
        $reverseStatusMap = array_flip($statusMap);
        $updatedCard['column_id'] = $reverseStatusMap[$updatedCard['status']] ?? 'todo';
        $updatedCard['assignee'] = '팀 멤버'; // 임시값
        
        return [
            'success' => true,
            'message' => '카드가 성공적으로 업데이트되었습니다.',
            'data' => [
                'card' => $updatedCard
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => '업데이트할 데이터가 없습니다.'
        ];
    }
    
} catch (Exception $e) {
    error_log('Kanban update card error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '카드 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
    ];
}