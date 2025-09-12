<?php
/**
 * 프로젝트 목록 조회
 */

// PMS 데이터베이스 시스템 로드
require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../../frontend/100-pms-common/001-database/pms-database.php';

try {
    // 검색 파라미터 처리
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $limit = min((int)($_GET['limit'] ?? 20), 100); // 최대 100개로 제한
    $offset = max((int)($_GET['offset'] ?? 0), 0);
    
    // 쿼리 빌더 사용
    $query = pmsDB('projects');
    
    // 검색 조건 추가
    if (!empty($search)) {
        $query->where('name', 'LIKE', "%{$search}%");
    }
    
    // 상태 필터 추가
    if (!empty($status)) {
        $query->where('status', '=', $status);
    }
    
    // 총 개수 조회를 위한 복사본
    $countQuery = clone $query;
    $totalCount = $countQuery->count();
    
    // 프로젝트 목록 조회
    $projects = $query
        ->select('id, name, description, status, priority, progress, start_date, end_date, created_at, updated_at')
        ->orderBy('updated_at', 'DESC')
        ->limit($limit)
        ->offset($offset)
        ->get();
    
    // 상태별 통계 조회
    $statusStats = [];
    $statsResults = PMSDatabase::getInstance()->fetchAll("
        SELECT 
            status,
            COUNT(*) as count
        FROM projects 
        GROUP BY status
    ");
    
    foreach ($statsResults as $row) {
        $statusStats[$row['status']] = (int)$row['count'];
    }
    
    return [
        'success' => true,
        'data' => [
            'projects' => $projects,
            'pagination' => [
                'total' => (int)$totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'hasNext' => ($offset + $limit) < $totalCount,
                'hasPrev' => $offset > 0
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ],
            'stats' => $statusStats
        ]
    ];
    
} catch (Exception $e) {
    error_log('Projects list error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '프로젝트 목록을 조회할 수 없습니다: ' . $e->getMessage()
    ];
}