<?php

namespace App\Http\Controllers\Sandbox\FormSubmission\List;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // SQLite 데이터베이스 연결 설정
            $dbPath = storage_path('../' . env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . '/{$sandboxTemplate}/100-domain-pms/100-common/200-Database/release.sqlite');

            if (!file_exists($dbPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Database not found. Please submit a form first to initialize the database.'
                ], 404);
            }

            // SQLite 연결
            $pdo = new \PDO("sqlite:$dbPath", null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            // Get query parameters
            $page = max(1, intval($request->get('page', 1)));
            $limit = min(50, max(1, intval($request->get('limit', 20))));
            $formName = $request->get('form_name', '');
            $search = $request->get('search', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');
            $sortBy = $request->get('sort_by', 'submitted_at');
            $sortOrder = strtoupper($request->get('sort_order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Build WHERE clause
            $whereConditions = [];
            $params = [];

            if (!empty($formName)) {
                $whereConditions[] = "form_name = ?";
                $params[] = $formName;
            }

            if (!empty($search)) {
                $whereConditions[] = "(form_name LIKE ? OR form_data LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            if (!empty($dateFrom)) {
                $whereConditions[] = "submitted_at >= ?";
                $params[] = $dateFrom . ' 00:00:00';
            }

            if (!empty($dateTo)) {
                $whereConditions[] = "submitted_at <= ?";
                $params[] = $dateTo . ' 23:59:59';
            }

            $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);

            // Validate sort column
            $allowedSortColumns = ['id', 'form_name', 'submitted_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'submitted_at';
            }

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM form_submissions $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalCount = $countStmt->fetch()['total'];

            // Get submissions
            $sql = "SELECT * FROM form_submissions $whereClause ORDER BY $sortBy $sortOrder LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $submissions = $stmt->fetchAll();

            // Process submissions
            foreach ($submissions as &$submission) {
                // Parse JSON data
                $submission['form_data'] = json_decode($submission['form_data'], true);

                // Format date
                $submission['submitted_at_formatted'] = date('Y-m-d H:i:s', strtotime($submission['submitted_at']));
            }

            // Calculate pagination info
            $totalPages = ceil($totalCount / $limit);
            $hasNext = $page < $totalPages;
            $hasPrev = $page > 1;

            // Get unique form names for filter
            $formNamesSql = "SELECT DISTINCT form_name FROM form_submissions ORDER BY form_name";
            $formNamesStmt = $pdo->prepare($formNamesSql);
            $formNamesStmt->execute();
            $formNames = $formNamesStmt->fetchAll(\PDO::FETCH_COLUMN);

            return response()->json([
                'success' => true,
                'data' => $submissions,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'limit' => $limit,
                    'has_next' => $hasNext,
                    'has_prev' => $hasPrev
                ],
                'filters' => [
                    'form_names' => $formNames,
                    'current_filters' => [
                        'form_name' => $formName,
                        'search' => $search,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                        'sort_by' => $sortBy,
                        'sort_order' => $sortOrder
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch submissions: ' . $e->getMessage()
            ], 500);
        }
    }
}
