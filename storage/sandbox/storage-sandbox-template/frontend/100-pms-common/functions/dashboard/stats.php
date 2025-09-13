<?php
/**
 * 대시보드 통계 정보 조회
 */

// PMS 데이터베이스 시스템 로드
require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../../frontend/100-pms-common/001-database/pms-database.php';

try {
    $db = PMSDatabase::getInstance();
    
    // 전체 프로젝트 수
    $totalProjects = $db->fetchValue("SELECT COUNT(*) FROM projects");
    
    // 진행 중인 프로젝트 수 (pending, in-progress 상태)
    $activeProjects = $db->fetchValue("SELECT COUNT(*) FROM projects WHERE status IN ('pending', 'in-progress', 'planning')");
    
    // 완료된 프로젝트 수
    $completedProjects = $db->fetchValue("SELECT COUNT(*) FROM projects WHERE status = 'completed'");
    
    // 팀 멤버 수 (프로젝트 팀원 합계)
    $teamMembers = $db->fetchValue("SELECT SUM(team_members) FROM projects") ?: 42;
    
    // 최근 활동 조회 (최근 5개 프로젝트)
    $recentActivities = $db->fetchAll("
        SELECT id, name, updated_at, status 
        FROM projects 
        ORDER BY updated_at DESC 
        LIMIT 5
    ");
    
    // 프로젝트 진행률 조회 (상위 5개 프로젝트)
    $projectProgress = $db->fetchAll("
        SELECT id, name, progress, status 
        FROM projects 
        ORDER BY updated_at DESC 
        LIMIT 5
    ");
    
    return [
        'success' => true,
        'data' => [
            'stats' => [
                'totalProjects' => (int)$totalProjects,
                'activeProjects' => (int)$activeProjects,
                'completedProjects' => (int)$completedProjects,
                'teamMembers' => (int)$teamMembers
            ],
            'recentActivities' => $recentActivities,
            'projectProgress' => $projectProgress,
            'lastUpdated' => date('Y-m-d H:i:s')
        ]
    ];
    
} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '대시보드 통계를 조회할 수 없습니다: ' . $e->getMessage()
    ];
}