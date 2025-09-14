<?php

/*
|--------------------------------------------------------------------------
| Sandbox PMS API with SQLite Database Integration
|--------------------------------------------------------------------------
|
| 이 파일은 샌드박스 PMS 시스템의 독립적인 API입니다.
| Laravel 없이 순수 PHP와 SQLite로 작동합니다.
|
*/

// 헤더 설정
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// OPTIONS 요청 처리 (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// SQLite 데이터베이스 연결 함수
function getDatabase() {
    $dbPath = __DIR__ . '/../200-Database/release.sqlite';
    if (!file_exists($dbPath)) {
        throw new Exception("Database file not found: " . $dbPath);
    }
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

// JSON 응답 함수
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// 입력 데이터 파싱
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// URL 경로 파싱
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// sandbox/{sandbox}/backend/api.php/ 이후 부분 찾기
$apiIndex = array_search('api.php', $pathParts);
if ($apiIndex !== false && isset($pathParts[$apiIndex + 1])) {
    $endpoint = implode('/', array_slice($pathParts, $apiIndex + 1));
} else {
    $endpoint = '';
}

try {
    $pdo = getDatabase();
    
    // API 라우팅
    switch ($endpoint) {
        
        // 대시보드 통계
        case 'dashboard/stats':
            if ($requestMethod === 'GET') {
                $totalProjects = $pdo->query("SELECT COUNT(*) as count FROM projects")->fetchColumn();
                $activeProjects = $pdo->query("SELECT COUNT(*) as count FROM projects WHERE status = 'in_progress'")->fetchColumn();
                $completedProjects = $pdo->query("SELECT COUNT(*) as count FROM projects WHERE status = 'completed'")->fetchColumn();
                $totalTeamMembers = $pdo->query("SELECT SUM(team_members) as total FROM projects")->fetchColumn();
                
                $recentActivities = $pdo->query("
                    SELECT id, name, updated_at 
                    FROM projects 
                    ORDER BY updated_at DESC 
                    LIMIT 4
                ")->fetchAll();
                
                $projectProgress = $pdo->query("
                    SELECT id, name, progress 
                    FROM projects 
                    WHERE status IN ('in_progress', 'planned') 
                    ORDER BY updated_at DESC 
                    LIMIT 4
                ")->fetchAll();
                
                jsonResponse([
                    'success' => true,
                    'data' => [
                        'stats' => [
                            'totalProjects' => (int)$totalProjects,
                            'activeProjects' => (int)$activeProjects,
                            'completedProjects' => (int)$completedProjects,
                            'teamMembers' => (int)$totalTeamMembers
                        ],
                        'recentActivities' => $recentActivities,
                        'projectProgress' => $projectProgress,
                        'lastUpdated' => date('Y-m-d H:i:s')
                    ]
                ]);
            }
            break;
            
        // 프로젝트 목록
        case 'projects':
            if ($requestMethod === 'GET') {
                $projects = $pdo->query("
                    SELECT id, name, description, start_date, end_date, status, priority, 
                           client, progress, team_members, category, created_at, updated_at
                    FROM projects 
                    ORDER BY updated_at DESC
                ")->fetchAll();
                
                jsonResponse([
                    'success' => true,
                    'data' => [
                        'projects' => $projects
                    ]
                ]);
                
            } elseif ($requestMethod === 'POST') {
                $stmt = $pdo->prepare("
                    INSERT INTO projects (name, description, status, priority, client, 
                                        category, team_members, progress, start_date, end_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $input['name'] ?? '새 프로젝트',
                    $input['description'] ?? '',
                    $input['status'] ?? 'planned',
                    $input['priority'] ?? 'medium',
                    $input['client'] ?? '',
                    $input['category'] ?? '기타',
                    $input['team_members'] ?? 1,
                    $input['progress'] ?? 0,
                    $input['start_date'] ?? null,
                    $input['end_date'] ?? null
                ]);
                
                $projectId = $pdo->lastInsertId();
                $project = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
                $project->execute([$projectId]);
                
                jsonResponse([
                    'success' => true,
                    'data' => $project->fetch(),
                    'message' => '프로젝트가 성공적으로 생성되었습니다.'
                ]);
            }
            break;
            
        // 프로젝트 업데이트
        case (preg_match('/^projects\/(\d+)$/', $endpoint, $matches) ? $matches[0] : ''):
            if ($requestMethod === 'PUT') {
                $projectId = $matches[1];
                
                $stmt = $pdo->prepare("
                    UPDATE projects 
                    SET name = ?, description = ?, status = ?, priority = ?, 
                        client = ?, category = ?, team_members = ?, progress = ?,
                        start_date = ?, end_date = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $input['name'],
                    $input['description'],
                    $input['status'],
                    $input['priority'],
                    $input['client'],
                    $input['category'],
                    $input['team_members'],
                    $input['progress'],
                    $input['start_date'],
                    $input['end_date'],
                    $projectId
                ]);
                
                $project = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
                $project->execute([$projectId]);
                
                jsonResponse([
                    'success' => true,
                    'data' => $project->fetch(),
                    'message' => '프로젝트가 성공적으로 업데이트되었습니다.'
                ]);
            }
            break;
            
        // 칸반 보드
        case 'kanban/boards':
            if ($requestMethod === 'GET') {
                $plannedProjects = $pdo->query("
                    SELECT id, name as title, description, priority, client as assignee, 
                           progress, created_at 
                    FROM projects 
                    WHERE status = 'planned'
                    ORDER BY created_at DESC
                ")->fetchAll();
                
                $inProgressProjects = $pdo->query("
                    SELECT id, name as title, description, priority, client as assignee, 
                           progress, created_at 
                    FROM projects 
                    WHERE status = 'in_progress'
                    ORDER BY created_at DESC
                ")->fetchAll();
                
                $onHoldProjects = $pdo->query("
                    SELECT id, name as title, description, priority, client as assignee, 
                           progress, created_at 
                    FROM projects 
                    WHERE status = 'on_hold'
                    ORDER BY created_at DESC
                ")->fetchAll();
                
                $completedProjects = $pdo->query("
                    SELECT id, name as title, description, priority, client as assignee, 
                           progress, created_at 
                    FROM projects 
                    WHERE status = 'completed'
                    ORDER BY created_at DESC
                ")->fetchAll();
                
                jsonResponse([
                    'success' => true,
                    'data' => [
                        'boards' => [
                            [
                                'column' => [
                                    'id' => 'todo',
                                    'title' => 'To Do',
                                    'color' => 'blue'
                                ],
                                'cards' => $plannedProjects
                            ],
                            [
                                'column' => [
                                    'id' => 'inprogress',
                                    'title' => 'In Progress',
                                    'color' => 'yellow'
                                ],
                                'cards' => $inProgressProjects
                            ],
                            [
                                'column' => [
                                    'id' => 'review',
                                    'title' => 'Review',
                                    'color' => 'orange'
                                ],
                                'cards' => $onHoldProjects
                            ],
                            [
                                'column' => [
                                    'id' => 'done',
                                    'title' => 'Done',
                                    'color' => 'green'
                                ],
                                'cards' => $completedProjects
                            ]
                        ]
                    ]
                ]);
            }
            break;
            
        // 칸반 카드 생성
        case 'kanban/cards':
            if ($requestMethod === 'POST') {
                $statusMap = [
                    'todo' => 'planned',
                    'inprogress' => 'in_progress',
                    'review' => 'on_hold',
                    'done' => 'completed'
                ];
                
                $status = $statusMap[$input['column']] ?? 'planned';
                
                $stmt = $pdo->prepare("
                    INSERT INTO projects (name, description, status, priority, client, progress) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $input['title'] ?? '새 카드',
                    $input['description'] ?? '',
                    $status,
                    $input['priority'] ?? 'medium',
                    $input['assignee'] ?? '미배정',
                    $input['progress'] ?? 0
                ]);
                
                $projectId = $pdo->lastInsertId();
                $project = $pdo->prepare("
                    SELECT id, name as title, description, priority, client as assignee, 
                           progress, created_at 
                    FROM projects 
                    WHERE id = ?
                ");
                $project->execute([$projectId]);
                
                jsonResponse([
                    'success' => true,
                    'data' => $project->fetch(),
                    'message' => '카드가 성공적으로 생성되었습니다.'
                ]);
            }
            break;
            
        default:
            jsonResponse([
                'success' => false,
                'message' => 'Endpoint not found: ' . $endpoint
            ], 404);
            break;
    }
    
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], 500);
}