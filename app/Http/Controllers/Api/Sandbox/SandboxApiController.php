<?php

namespace App\Http\Controllers\Api\Sandbox;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * 샌드박스 API 통합 컨트롤러
 */
class SandboxApiController extends Controller
{
    /**
     * 프로젝트 목록 조회
     */
    public function getProjects(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search', ''),
                'status' => $request->get('status', ''),
                'priority' => $request->get('priority', '')
            ];
            
            $limit = $request->get('limit', 20);
            $offset = $request->get('offset', 0);
            
            // 목업 프로젝트 데이터
            $mockProjects = [
                [
                    'id' => 1,
                    'name' => '샘플 프로젝트 1',
                    'description' => '첫 번째 샘플 프로젝트입니다.',
                    'status' => 'in_progress',
                    'priority' => 'high',
                    'progress' => 75,
                    'start_date' => '2024-01-01',
                    'end_date' => '2024-12-31',
                    'created_at' => '2024-01-15 10:30:00',
                    'updated_at' => '2024-01-20 15:45:00'
                ],
                [
                    'id' => 2,
                    'name' => '샘플 프로젝트 2',
                    'description' => '두 번째 샘플 프로젝트입니다.',
                    'status' => 'completed',
                    'priority' => 'medium',
                    'progress' => 100,
                    'start_date' => '2023-12-01',
                    'end_date' => '2024-06-30',
                    'created_at' => '2023-12-10 09:15:00',
                    'updated_at' => '2024-06-30 17:20:00'
                ],
                [
                    'id' => 3,
                    'name' => '샘플 프로젝트 3',
                    'description' => '세 번째 샘플 프로젝트입니다.',
                    'status' => 'pending',
                    'priority' => 'low',
                    'progress' => 30,
                    'start_date' => '2024-02-01',
                    'end_date' => '2024-08-31',
                    'created_at' => '2024-01-25 11:20:00',
                    'updated_at' => '2024-01-30 14:10:00'
                ]
            ];
            
            // 필터 적용
            if (!empty($filters['search'])) {
                $search = strtolower($filters['search']);
                $mockProjects = array_filter($mockProjects, function($project) use ($search) {
                    return strpos(strtolower($project['name']), $search) !== false ||
                           strpos(strtolower($project['description']), $search) !== false;
                });
            }
            
            if (!empty($filters['status'])) {
                $mockProjects = array_filter($mockProjects, function($project) use ($filters) {
                    return $project['status'] === $filters['status'];
                });
            }
            
            if (!empty($filters['priority'])) {
                $mockProjects = array_filter($mockProjects, function($project) use ($filters) {
                    return $project['priority'] === $filters['priority'];
                });
            }
            
            $mockProjects = array_values($mockProjects);
            $total = count($mockProjects);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'projects' => $mockProjects,
                    'pagination' => [
                        'total' => $total,
                        'limit' => (int)$limit,
                        'offset' => (int)$offset,
                        'hasNext' => ($offset + $limit) < $total,
                        'hasPrev' => $offset > 0
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve projects: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 대시보드 통계 조회
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $stats = [
                'stats' => [
                    'totalProjects' => 15,
                    'activeProjects' => 8,
                    'completedProjects' => 5,
                    'teamMembers' => 12
                ],
                'recentActivities' => [
                    ['id' => 1, 'name' => '샘플 프로젝트 1', 'updated_at' => '2024-01-20 15:45:00'],
                    ['id' => 2, 'name' => '샘플 프로젝트 2', 'updated_at' => '2024-01-19 12:30:00'],
                    ['id' => 3, 'name' => '샘플 프로젝트 3', 'updated_at' => '2024-01-18 09:15:00']
                ],
                'projectProgress' => [
                    ['id' => 1, 'name' => '샘플 프로젝트 1', 'progress' => 75],
                    ['id' => 4, 'name' => '샘플 프로젝트 4', 'progress' => 60],
                    ['id' => 5, 'name' => '샘플 프로젝트 5', 'progress' => 45]
                ],
                'lastUpdated' => date('Y-m-d H:i:s')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard stats: ' . $e->getMessage()
            ], 500);
        }
    }
}