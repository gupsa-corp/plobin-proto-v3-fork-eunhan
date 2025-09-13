<?php
/**
 * 컬럼 정보 업데이트
 * PUT /columns/{id}
 */

// 공통 설정 로드
require_once __DIR__ . '/../../common.php';

try {
    // 컬럼 ID 가져오기
    $columnId = $_GET['id'] ?? null;

    if (!$columnId) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '컬럼 ID가 필요합니다.'
        ]);
        return;
    }

    // JSON 입력 데이터 읽기
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '유효한 JSON 데이터가 필요합니다.'
        ]);
        return;
    }

    // 데이터베이스 연결
    $config = getSandboxConfig();
    $pdo = new PDO(
        "sqlite:" . $config['database']['path'],
        null,
        null,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 컬럼 타입 변경 시 기존 컬럼 정보 조회 (데이터 삭제를 위해)
    $needDataReset = false;
    if (isset($data['column_type'])) {
        $stmt = $pdo->prepare("SELECT column_name, column_type FROM project_columns WHERE id = ?");
        $stmt->execute([$columnId]);
        $currentColumn = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentColumn && $currentColumn['column_type'] !== $data['column_type']) {
            $needDataReset = true;
            $columnName = $currentColumn['column_name'];
        }
    }

    // 업데이트할 필드 구성
    $updateFields = [];
    $values = [];

    // 허용된 필드만 업데이트
    $allowedFields = [
        'column_label',
        'column_type',
        'default_value',
        'is_required',
        'is_active',
        'sort_order',
        'validation_rule',
        'display_type',
        'options'
    ];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            if ($field === 'options' && is_array($data[$field])) {
                $values[] = json_encode($data[$field]);
            } else {
                $values[] = $data[$field];
            }
        }
    }

    if (empty($updateFields)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '업데이트할 필드가 없습니다.'
        ]);
        return;
    }

    // 업데이트 날짜 추가
    $updateFields[] = "updated_at = ?";
    $values[] = date('Y-m-d H:i:s');

    // 컬럼 ID 추가
    $values[] = $columnId;

    // SQL 쿼리 실행
    $sql = "UPDATE project_columns SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($values);

    if (!$result || $stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '컬럼 업데이트에 실패했거나 해당 컬럼을 찾을 수 없습니다.'
        ]);
        return;
    }

    // 컬럼 타입이 변경된 경우 해당 컬럼의 모든 데이터를 삭제
    if ($needDataReset && isset($columnName)) {
        try {
            // 컬럼이 존재하는지 확인 (테이블 스키마 체크)
            $tableInfo = $pdo->query("PRAGMA table_info(projects)")->fetchAll(PDO::FETCH_ASSOC);
            $columnExists = false;
            foreach ($tableInfo as $column) {
                if ($column['name'] === $columnName) {
                    $columnExists = true;
                    break;
                }
            }
            
            if ($columnExists) {
                // 해당 컬럼의 모든 값을 NULL로 설정
                $resetStmt = $pdo->prepare("UPDATE projects SET `$columnName` = NULL");
                $resetStmt->execute();
            }
        } catch (PDOException $e) {
            error_log('Data Reset Error: ' . $e->getMessage());
            // 데이터 삭제 실패해도 컬럼 업데이트는 성공으로 처리
        }
    }

    // 업데이트된 컬럼 정보 조회
    $stmt = $pdo->prepare("SELECT * FROM project_columns WHERE id = ?");
    $stmt->execute([$columnId]);
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    $message = '컬럼이 성공적으로 업데이트되었습니다.';
    if ($needDataReset) {
        $message .= ' (타입 변경으로 인해 기존 데이터가 모두 삭제되었습니다.)';
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $column,
        'data_reset' => $needDataReset
    ]);

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '데이터베이스 오류가 발생했습니다: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Update Error: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '컬럼 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}
