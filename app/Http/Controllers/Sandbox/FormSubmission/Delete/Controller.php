<?php

namespace App\Http\Controllers\Sandbox\FormSubmission\Delete;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __invoke(Request $request, $id): JsonResponse
    {
        try {
            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid submission ID'
                ], 400);
            }

            // SQLite 데이터베이스 연결 설정
            $dbPath = storage_path('../' . env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . '/{$sandboxTemplate}/100-domain-pms/100-common/200-Database/release.sqlite');

            if (!file_exists($dbPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Database not found'
                ], 404);
            }

            // SQLite 연결
            $pdo = new \PDO("sqlite:$dbPath", null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            // Check if submission exists
            $checkSql = "SELECT id FROM form_submissions WHERE id = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$id]);

            if (!$checkStmt->fetch()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Submission not found'
                ], 404);
            }

            // Delete submission
            $deleteSql = "DELETE FROM form_submissions WHERE id = ?";
            $deleteStmt = $pdo->prepare($deleteSql);
            $result = $deleteStmt->execute([$id]);

            if ($result && $deleteStmt->rowCount() > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Submission deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete submission'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete submission: ' . $e->getMessage()
            ], 500);
        }
    }
}
