<?php
/**
 * 칸반 보드 데이터 조회
 */

// PMS 데이터베이스 시스템 로드
require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../../frontend/100-pms-common/001-database/pms-database.php';

try {
    $db = PMSDatabase::getInstance();
    
    // 칸반 컬럼들 (하드코딩된 구조)
    $columns = [
        [
            'id' => 'todo',
            'title' => '할 일',
            'color' => 'blue',
            'order' => 1
        ],
        [
            'id' => 'in-progress',
            'title' => '진행 중',
            'color' => 'yellow',
            'order' => 2
        ],
        [
            'id' => 'review',
            'title' => '검토',
            'color' => 'purple',
            'order' => 3
        ],
        [
            'id' => 'done',
            'title' => '완료',
            'color' => 'green',
            'order' => 4
        ]
    ];
    
    // 각 컬럼별 프로젝트 카드들 조회
    $boards = [];
    foreach ($columns as $column) {
        $status = '';
        switch ($column['id']) {
            case 'todo':
                $status = 'planning';
                break;
            case 'in-progress':
                $status = 'in-progress';
                break;
            case 'review':
                $status = 'pending'; // DB에는 pending 상태를 review로 매핑
                break;
            case 'done':
                $status = 'completed';
                break;
        }
        
        // 해당 상태의 프로젝트들 조회
        $cards = $db->fetchAll("
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
            WHERE status = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$status]);
        
        // 각 카드에 추가 정보 설정
        foreach ($cards as &$card) {
            $card['column_id'] = $column['id'];
            $card['assignee'] = '팀 멤버'; // 임시값
            $card['due_date'] = null; // 실제 구현시 due_date 필드 추가
        }
        
        $boards[] = [
            'column' => $column,
            'cards' => $cards,
            'count' => count($cards)
        ];
    }
    
    return [
        'success' => true,
        'data' => [
            'boards' => $boards,
            'total_cards' => array_sum(array_column($boards, 'count')),
            'last_updated' => date('Y-m-d H:i:s')
        ]
    ];
    
} catch (Exception $e) {
    error_log('Kanban boards error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '칸반 보드를 조회할 수 없습니다: ' . $e->getMessage()
    ];
}