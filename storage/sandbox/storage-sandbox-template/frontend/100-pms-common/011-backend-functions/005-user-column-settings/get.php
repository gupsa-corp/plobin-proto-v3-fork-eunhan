<?php
/**
 * 사용자 컬럼 설정 조회 API
 * GET /user-column-settings
 */

// 공통 설정 파일 포함
require_once __DIR__ . '/../../common.php';

try {
    $config = getSandboxConfig();
    $pdo = new PDO("sqlite:" . $config['database']['path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 현재는 기본 사용자 ID = 1 사용
    $userId = 1;
    $screenType = $_GET['screen_type'] ?? 'table_view';

    // 사용자 컬럼 설정 조회
    $stmt = $pdo->prepare("
        SELECT column_name, is_visible, column_order 
        FROM user_column_settings 
        WHERE user_id = :user_id AND screen_type = :screen_type
        ORDER BY column_order ASC, column_name ASC
    ");
    
    $stmt->execute([
        'user_id' => $userId,
        'screen_type' => $screenType
    ]);
    
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 설정이 없는 경우 기본 설정 반환
    if (empty($settings)) {
        $defaultSettings = [
            ['column_name' => 'id', 'is_visible' => 1, 'column_order' => 1],
            ['column_name' => 'name', 'is_visible' => 1, 'column_order' => 2],
            ['column_name' => 'status', 'is_visible' => 1, 'column_order' => 3],
            ['column_name' => 'progress', 'is_visible' => 1, 'column_order' => 4],
            ['column_name' => 'team_members', 'is_visible' => 1, 'column_order' => 5],
            ['column_name' => 'start_date', 'is_visible' => 1, 'column_order' => 6],
            ['column_name' => 'end_date', 'is_visible' => 1, 'column_order' => 7],
            ['column_name' => 'priority', 'is_visible' => 1, 'column_order' => 8],
            ['column_name' => 'client', 'is_visible' => 1, 'column_order' => 9]
        ];
        
        // 기본 설정을 DB에도 저장
        foreach ($defaultSettings as $setting) {
            $insertStmt = $pdo->prepare("
                INSERT OR IGNORE INTO user_column_settings 
                (user_id, screen_type, column_name, is_visible, column_order) 
                VALUES (:user_id, :screen_type, :column_name, :is_visible, :column_order)
            ");
            $insertStmt->execute([
                'user_id' => $userId,
                'screen_type' => $screenType,
                'column_name' => $setting['column_name'],
                'is_visible' => $setting['is_visible'],
                'column_order' => $setting['column_order']
            ]);
        }
        
        $settings = $defaultSettings;
    }

    // 동적 컬럼 설정도 가져오기
    $dynamicStmt = $pdo->prepare("
        SELECT pc.column_name, 
               COALESCE(ucs.is_visible, 1) as is_visible,
               COALESCE(ucs.column_order, 100 + pc.sort_order) as column_order
        FROM project_columns pc
        LEFT JOIN user_column_settings ucs ON ucs.column_name = 'custom_' || pc.column_name 
                                           AND ucs.user_id = :user_id 
                                           AND ucs.screen_type = :screen_type
        WHERE pc.is_active = 1
        ORDER BY column_order ASC
    ");
    
    $dynamicStmt->execute([
        'user_id' => $userId,
        'screen_type' => $screenType
    ]);
    
    $dynamicSettings = $dynamicStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 동적 컬럼을 custom_ 접두사와 함께 추가
    foreach ($dynamicSettings as &$setting) {
        $setting['column_name'] = 'custom_' . $setting['column_name'];
    }
    
    // 모든 설정 합치기
    $allSettings = array_merge($settings, $dynamicSettings);
    
    // column_order로 정렬
    usort($allSettings, function($a, $b) {
        return $a['column_order'] <=> $b['column_order'];
    });

    return [
        'success' => true,
        'data' => $allSettings,
        'screen_type' => $screenType
    ];

} catch (Exception $e) {
    error_log('사용자 컬럼 설정 조회 오류: ' . $e->getMessage());
    return [
        'success' => false,
        'message' => '컬럼 설정을 불러오는데 실패했습니다: ' . $e->getMessage()
    ];
}
?>