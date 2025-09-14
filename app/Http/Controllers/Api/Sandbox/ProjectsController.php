<?php

namespace App\Http\Controllers\Api\Sandbox;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use PDO;
use PDOException;

class ProjectsController extends Controller
{
    /**
     * Get paginated list of projects with optional filtering
     *
     * @param Request $request
     * @param string $sandboxTemplate
     * @return JsonResponse
     */
    public function index(Request $request, string $sandboxTemplate): JsonResponse
    {
        // Get query parameters
        $limit = min((int) $request->get('limit', 20), 100); // 최대 100개로 제한
        $offset = max((int) $request->get('offset', 0), 0);
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $priority = $request->get('priority', '');

        try {
            // Use SQLite database for sandbox data
            $pdo = $this->getSqliteConnection($sandboxTemplate);

            // Build WHERE conditions
            $whereConditions = ['sandbox_folder = ?', 'deleted_at IS NULL'];
            $params = [$sandboxTemplate];

            if (!empty($search)) {
                $whereConditions[] = "(name LIKE ? OR description LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            if (!empty($status)) {
                $whereConditions[] = "status = ?";
                $params[] = $status;
            }

            if (!empty($priority)) {
                $whereConditions[] = "priority = ?";
                $params[] = $priority;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

            // Get total count for pagination
            $countSql = "SELECT COUNT(*) FROM projects $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get paginated projects
            $sql = "SELECT 
                        id, name, description, status, progress, team_members, priority,
                        start_date, end_date, client, category, budget, tags,
                        created_at, updated_at
                    FROM projects
                    $whereClause
                    ORDER BY created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $pdo->prepare($sql);
            $allParams = array_merge($params, [$limit, $offset]);
            $stmt->execute($allParams);
            $projectsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format projects for API response
            $projects = array_map(function ($project) {
                return [
                    'id' => (int) $project['id'],
                    'name' => $project['name'],
                    'description' => $project['description'],
                    'status' => $project['status'] ?? 'pending',
                    'priority' => $project['priority'] ?? 'medium',
                    'progress' => (int) ($project['progress'] ?? 0),
                    'team_members' => (int) ($project['team_members'] ?? 1),
                    'client' => $project['client'],
                    'category' => $project['category'],
                    'budget' => $project['budget'],
                    'start_date' => $project['start_date'],
                    'end_date' => $project['end_date'],
                    'created_at' => $project['created_at'],
                    'updated_at' => $project['updated_at'],
                    'metadata' => [
                        'tags' => $project['tags'] ? json_decode($project['tags'], true) : [],
                        'client' => $project['client'],
                        'budget' => $project['budget']
                    ],
                ];
            }, $projectsData);

            // Calculate pagination
            $hasNext = ($offset + $limit) < $total;
            $hasPrev = $offset > 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'projects' => $projects,
                    'pagination' => [
                        'total' => (int) $total,
                        'limit' => $limit,
                        'offset' => $offset,
                        'hasNext' => $hasNext,
                        'hasPrev' => $hasPrev,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로젝트 목록을 불러오는 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get a specific project by ID
     *
     * @param Request $request
     * @param string $sandboxTemplate
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, string $sandboxTemplate, int $id): JsonResponse
    {
        try {
            // Use SQLite database for sandbox data
            $pdo = $this->getSqliteConnection($sandboxTemplate);

            $stmt = $pdo->prepare("
                SELECT 
                    id, name, description, status, progress, team_members, priority,
                    start_date, end_date, client, category, budget, tags,
                    created_at, updated_at
                FROM projects 
                WHERE id = ? AND sandbox_folder = ? AND deleted_at IS NULL
            ");

            $stmt->execute([$id, $sandboxTemplate]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => '프로젝트를 찾을 수 없습니다.'
                ], 404);
            }

            $projectData = [
                'id' => (int) $project['id'],
                'name' => $project['name'],
                'description' => $project['description'],
                'status' => $project['status'] ?? 'pending',
                'priority' => $project['priority'] ?? 'medium',
                'progress' => (int) ($project['progress'] ?? 0),
                'team_members' => (int) ($project['team_members'] ?? 1),
                'client' => $project['client'],
                'category' => $project['category'],
                'budget' => $project['budget'],
                'start_date' => $project['start_date'],
                'end_date' => $project['end_date'],
                'created_at' => $project['created_at'],
                'updated_at' => $project['updated_at'],
                'metadata' => [
                    'tags' => $project['tags'] ? json_decode($project['tags'], true) : [],
                    'client' => $project['client'],
                    'budget' => $project['budget']
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $projectData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로젝트 정보를 불러오는 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get SQLite database connection for sandbox
     */
    private function getSqliteConnection(string $sandboxTemplate): PDO
    {
        $dbPath = storage_path("../" . env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandboxTemplate}/100-domain-pms/100-common/200-Database/release.sqlite");
        
        if (!file_exists($dbPath)) {
            throw new \Exception("SQLite database not found at: {$dbPath}");
        }

        $pdo = new PDO("sqlite:{$dbPath}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }

    /**
     * Update project via SQLite database
     */
    public function update(Request $request, string $sandboxTemplate, int $id): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);

            // Validate input
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|nullable',
                'status' => 'sometimes|string|in:planned,in_progress,completed,on_hold',
                'priority' => 'sometimes|string|in:low,medium,high',
                'progress' => 'sometimes|integer|min:0|max:100',
                'team_members' => 'sometimes|integer|min:1',
                'client' => 'sometimes|string|nullable',
                'category' => 'sometimes|string|nullable',
                'budget' => 'sometimes|numeric|min:0',
            ]);

            // Add updated_at
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Build update query dynamically
            $setClause = [];
            $params = [];
            foreach ($data as $key => $value) {
                $setClause[] = "{$key} = ?";
                $params[] = $value;
            }
            $params[] = $id;
            $params[] = $sandboxTemplate;

            $updateQuery = "
                UPDATE projects 
                SET " . implode(', ', $setClause) . "
                WHERE id = ? AND sandbox_folder = ? AND deleted_at IS NULL
            ";

            $stmt = $pdo->prepare($updateQuery);
            $result = $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => '프로젝트를 찾을 수 없거나 업데이트할 내용이 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '프로젝트가 성공적으로 업데이트되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로젝트 업데이트 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Create new project via SQLite database
     */
    public function store(Request $request, string $sandboxTemplate): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);

            // Validate input
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string|in:planned,in_progress,completed,on_hold',
                'priority' => 'nullable|string|in:low,medium,high',
                'progress' => 'nullable|integer|min:0|max:100',
                'team_members' => 'nullable|integer|min:1',
                'client' => 'nullable|string',
                'category' => 'nullable|string',
                'budget' => 'nullable|numeric|min:0',
            ]);

            $insertQuery = "
                INSERT INTO projects (
                    name, description, status, priority, progress, team_members,
                    client, category, budget, sandbox_folder, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare($insertQuery);
            $result = $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['status'] ?? 'planned',
                $data['priority'] ?? 'medium',
                $data['progress'] ?? 0,
                $data['team_members'] ?? 1,
                $data['client'] ?? null,
                $data['category'] ?? null,
                $data['budget'] ?? 0,
                $sandboxTemplate,
                $now,
                $now
            ]);

            if ($result) {
                $projectId = $pdo->lastInsertId();

                return response()->json([
                    'success' => true,
                    'message' => '프로젝트가 성공적으로 생성되었습니다.',
                    'data' => [
                        'id' => (int) $projectId,
                        'name' => $data['name']
                    ]
                ]);
            }

            throw new \Exception('프로젝트 생성에 실패했습니다.');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로젝트 생성 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Soft delete project via SQLite database
     */
    public function destroy(Request $request, string $sandboxTemplate, int $id): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);

            $updateQuery = "
                UPDATE projects 
                SET deleted_at = ? 
                WHERE id = ? AND sandbox_folder = ? AND deleted_at IS NULL
            ";

            $stmt = $pdo->prepare($updateQuery);
            $result = $stmt->execute([date('Y-m-d H:i:s'), $id, $sandboxTemplate]);

            if ($stmt->rowCount() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => '프로젝트를 찾을 수 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '프로젝트가 성공적으로 삭제되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '프로젝트 삭제 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get kanban boards from SQLite database
     */
    public function getKanbanBoards(Request $request, string $sandboxTemplate): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);

            // Get column definitions for kanban
            $columnsQuery = "
                SELECT 'todo' as id, 'To Do' as title, 'blue' as color, 1 as sort_order
                UNION ALL
                SELECT 'in-progress' as id, 'In Progress' as title, 'yellow' as color, 2 as sort_order
                UNION ALL  
                SELECT 'review' as id, 'Review' as title, 'purple' as color, 3 as sort_order
                UNION ALL
                SELECT 'done' as id, 'Done' as title, 'green' as color, 4 as sort_order
                ORDER BY sort_order
            ";
            
            $columnsStmt = $pdo->query($columnsQuery);
            $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get projects and organize by status for kanban
            $projectsQuery = "
                SELECT 
                    id, name as title, description, status, priority, progress,
                    team_members, created_at, updated_at,
                    CASE 
                        WHEN status = 'pending' OR status = 'planned' THEN 'todo'
                        WHEN status = 'in_progress' THEN 'in-progress'
                        WHEN status = 'on_hold' THEN 'review'
                        WHEN status = 'completed' THEN 'done'
                        ELSE 'todo'
                    END as column_id
                FROM projects 
                WHERE sandbox_folder = ? AND deleted_at IS NULL
                ORDER BY created_at DESC
            ";
            
            $stmt = $pdo->prepare($projectsQuery);
            $stmt->execute([$sandboxTemplate]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group projects by column
            $boards = [];
            foreach ($columns as $column) {
                $cards = array_filter($projects, function($project) use ($column) {
                    return $project['column_id'] === $column['id'];
                });

                $boards[] = [
                    'column' => $column,
                    'cards' => array_values($cards),
                    'count' => count($cards)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'boards' => $boards
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '칸반 보드를 불러오는 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get specific kanban card
     */
    public function getKanbanCard(Request $request, string $sandboxTemplate, int $id): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);
            
            $stmt = $pdo->prepare("
                SELECT 
                    id, name as title, description, status, priority, progress,
                    team_members, created_at, updated_at,
                    CASE 
                        WHEN status = 'pending' OR status = 'planned' THEN 'todo'
                        WHEN status = 'in_progress' THEN 'in-progress'
                        WHEN status = 'on_hold' THEN 'review'
                        WHEN status = 'completed' THEN 'done'
                        ELSE 'todo'
                    END as column_id
                FROM projects 
                WHERE id = ? AND sandbox_folder = ? AND deleted_at IS NULL
            ");
            
            $stmt->execute([$id, $sandboxTemplate]);
            $card = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => '카드를 찾을 수 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $card
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '카드 정보를 불러오는 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update kanban card (project)
     */
    public function updateKanbanCard(Request $request, string $sandboxTemplate, int $id): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);
            
            $data = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|nullable',
                'priority' => 'sometimes|string',
                'progress' => 'sometimes|integer|min:0|max:100',
                'team_members' => 'sometimes|integer|min:1',
                'status' => 'sometimes|string',
                'column_id' => 'sometimes|string'
            ]);

            // Convert column_id to status
            if (isset($data['column_id'])) {
                $statusMap = [
                    'todo' => 'pending',
                    'in-progress' => 'in_progress',
                    'review' => 'on_hold',
                    'done' => 'completed'
                ];
                $data['status'] = $statusMap[$data['column_id']] ?? 'pending';
                unset($data['column_id']);
            }

            // Convert title to name for database
            if (isset($data['title'])) {
                $data['name'] = $data['title'];
                unset($data['title']);
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            // Build update query dynamically
            $setClause = [];
            $params = [];
            foreach ($data as $key => $value) {
                $setClause[] = "{$key} = ?";
                $params[] = $value;
            }
            $params[] = $id;
            $params[] = $sandboxTemplate;

            $updateQuery = "
                UPDATE projects 
                SET " . implode(', ', $setClause) . "
                WHERE id = ? AND sandbox_folder = ? AND deleted_at IS NULL
            ";

            $stmt = $pdo->prepare($updateQuery);
            $result = $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => '카드를 찾을 수 없거나 업데이트할 내용이 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => '카드가 성공적으로 업데이트되었습니다.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '카드 업데이트 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Create new kanban card (project)
     */
    public function createKanbanCard(Request $request, string $sandboxTemplate): JsonResponse
    {
        try {
            $pdo = $this->getSqliteConnection($sandboxTemplate);
            
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'required|string',
                'team_members' => 'required|integer|min:1',
                'column_id' => 'required|string'
            ]);

            // Convert column_id to status
            $statusMap = [
                'todo' => 'pending',
                'in-progress' => 'in_progress',
                'review' => 'on_hold',
                'done' => 'completed'
            ];
            
            $status = $statusMap[$data['column_id']] ?? 'pending';
            
            $insertQuery = "
                INSERT INTO projects (
                    name, description, status, priority, progress, team_members,
                    sandbox_folder, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            $now = date('Y-m-d H:i:s');
            
            try {
                $stmt = $pdo->prepare($insertQuery);
                
                $insertData = [
                    $data['title'],
                    $data['description'] ?? '',
                    $status,
                    $data['priority'],
                    0, // default progress
                    $data['team_members'],
                    $sandboxTemplate,
                    $now,
                    $now
                ];
                
                \Log::info('SQL Insert data:', $insertData);
                
                $result = $stmt->execute($insertData);
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    \Log::error('SQL Insert failed:', $errorInfo);
                    throw new \Exception('SQL Insert failed: ' . $errorInfo[2]);
                }
                
                \Log::info('SQL execute result:', [$result]);
                
            } catch (\PDOException $e) {
                \Log::error('PDO Exception during insert:', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'data' => $insertData ?? 'not available'
                ]);
                throw new \Exception('데이터베이스 오류: ' . $e->getMessage());
            }

            if ($result) {
                $cardId = $pdo->lastInsertId();
                \Log::info('Insert successful, cardId:', [$cardId]);
                
                if (!$cardId) {
                    \Log::error('lastInsertId() returned empty/false');
                    throw new \Exception('카드 생성 실패: ID를 가져올 수 없습니다.');
                }
                
                // Get the created card
                $getStmt = $pdo->prepare("
                    SELECT 
                        id, name as title, description, status, priority, progress,
                        team_members, created_at, updated_at,
                        CASE 
                            WHEN status = 'pending' OR status = 'planned' THEN 'todo'
                            WHEN status = 'in_progress' THEN 'in-progress'
                            WHEN status = 'on_hold' THEN 'review'
                            WHEN status = 'completed' THEN 'done'
                            ELSE 'todo'
                        END as column_id
                    FROM projects 
                    WHERE id = ?
                ");
                \Log::info('Fetching created card with ID:', [$cardId]);
                $getStmt->execute([$cardId]);
                $card = $getStmt->fetch(PDO::FETCH_ASSOC);
                \Log::info('Fetched card:', $card ? $card : 'null');

                return response()->json([
                    'success' => true,
                    'message' => '카드가 성공적으로 생성되었습니다.',
                    'data' => [
                        'card' => $card
                    ]
                ]);
            }

            throw new \Exception('카드 생성에 실패했습니다.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '카드 생성 중 오류가 발생했습니다.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'details' => [
                    'exception' => get_class($e),
                    'line' => $e->getLine(),
                    'file' => basename($e->getFile())
                ]
            ], 500);
        }
    }
}