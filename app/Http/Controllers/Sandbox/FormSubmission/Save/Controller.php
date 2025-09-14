<?php

namespace App\Http\Controllers\Sandbox\FormSubmission\Save;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'formName' => 'required|string|max:255',
            'formData' => 'required|array',
            'timestamp' => 'nullable|string',
        ]);

        try {
            // SQLite 데이터베이스 연결 설정
            $dbPath = storage_path('../sandbox/container/{$sandboxTemplate}/100-domain-pms/100-common/200-Database/release.sqlite');

            // 데이터베이스 파일이 없으면 생성
            if (!file_exists($dbPath)) {
                $this->initializeDatabase($dbPath);
            }

            // SQLite 연결
            $pdo = new \PDO("sqlite:$dbPath", null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);

            // 폼 제출 데이터 저장
            $sql = "
                INSERT INTO form_submissions (
                    form_name,
                    form_data,
                    ip_address,
                    user_agent,
                    session_id,
                    submitted_at,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $request->formName,
                json_encode($request->formData, JSON_UNESCAPED_UNICODE),
                $request->ip() ?? '127.0.0.1',
                $request->userAgent() ?? '',
                session()->getId() ?? uniqid(),
                $request->timestamp ?? now()->toISOString(),
                now()->toISOString()
            ]);

            if ($result) {
                $submissionId = $pdo->lastInsertId();

                return response()->json([
                    'success' => true,
                    'message' => '폼이 성공적으로 제출되었습니다.',
                    'data' => [
                        'submission_id' => $submissionId,
                        'form_name' => $request->formName,
                        'submitted_data' => $request->formData,
                        'submitted_at' => $request->timestamp ?? now()->toISOString(),
                        'metadata' => [
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'session_id' => session()->getId()
                        ]
                    ]
                ]);
            } else {
                throw new \Exception('데이터베이스 저장에 실패했습니다.');
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '폼 제출 저장 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 데이터베이스 초기화
     */
    private function initializeDatabase(string $dbPath): void
    {
        // 디렉토리 생성
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // SQLite 연결 및 테이블 생성
        $pdo = new \PDO("sqlite:$dbPath", null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        // 폼 제출 테이블 생성
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS form_submissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                form_name VARCHAR(255) NOT NULL,
                form_data JSON NOT NULL,
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                session_id VARCHAR(128),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $pdo->exec($createTableSQL);

        // 인덱스 추가
        $indexSQL = [
            "CREATE INDEX IF NOT EXISTS idx_form_submissions_form_name ON form_submissions(form_name)",
            "CREATE INDEX IF NOT EXISTS idx_form_submissions_submitted_at ON form_submissions(submitted_at)",
            "CREATE INDEX IF NOT EXISTS idx_form_submissions_created_at ON form_submissions(created_at)"
        ];

        foreach ($indexSQL as $sql) {
            $pdo->exec($sql);
        }
    }
}
