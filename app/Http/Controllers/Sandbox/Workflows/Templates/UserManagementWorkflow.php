<?php
/**
 * User Management Workflow Template
 * 사용자 관리 및 CRUD 작업을 위한 워크플로우 템플릿
 */

namespace App\Http\Controllers\Sandbox\Workflows\Templates;

use App\Http\Sandbox\Workflows\BaseWorkflow;

class UserManagementWorkflow extends BaseWorkflow
{
    public function execute($input)
    {
        $action = $input['action'] ?? 'create';
        
        $this->logStep('Starting user management workflow', ['action' => $action, 'input' => $input]);
        
        switch ($action) {
            case 'create':
                return $this->createUser($input);
            case 'read':
            case 'get':
                return $this->getUser($input);
            case 'update':
                return $this->updateUser($input);
            case 'delete':
                return $this->deleteUser($input);
            case 'list':
                return $this->listUsers($input);
            default:
                return $this->errorResponse('Invalid action. Supported actions: create, read, update, delete, list');
        }
    }
    
    private function createUser($input)
    {
        // 1단계: 사용자 데이터 유효성 검증
        if (!isset($input['user_data'])) {
            return $this->errorResponse('User data is required for create operation');
        }
        
        $userData = $input['user_data'];
        
        // 2단계: 데이터 포맷팅 및 검증
        $validatedData = $this->callFunction('StringHelper', [
            'operation' => 'convert',
            'input' => $userData,
            'from' => 'auto',
            'to' => 'array'
        ]);
        
        if (!isset($validatedData['success']) || !$validatedData['success']) {
            return $this->errorResponse('User data validation failed', $validatedData);
        }
        
        // 3단계: 사용자 생성
        $createResult = $this->callFunction('UserManager', [
            'action' => 'create',
            'data' => $validatedData['converted_data'] ?? $userData
        ]);
        
        // 4단계: 성공 시 리포트 생성 (선택사항)
        if (isset($createResult['success']) && $createResult['success'] && 
            isset($input['generate_report']) && $input['generate_report']) {
            
            $this->generateUserReport('User Created', $createResult);
        }
        
        return [
            'success' => isset($createResult['success']) ? $createResult['success'] : false,
            'message' => 'User creation workflow completed',
            'user_result' => $createResult,
            'workflow' => 'UserManagementWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function getUser($input)
    {
        $userResult = $this->callFunction('UserManager', [
            'action' => 'read',
            'id' => $input['user_id'] ?? null,
            'email' => $input['email'] ?? null
        ]);
        
        return [
            'success' => isset($userResult['success']) ? $userResult['success'] : false,
            'message' => 'User retrieval workflow completed',
            'user_result' => $userResult,
            'workflow' => 'UserManagementWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function updateUser($input)
    {
        if (!isset($input['user_id']) || !isset($input['user_data'])) {
            return $this->errorResponse('User ID and user data are required for update operation');
        }
        
        $userResult = $this->callFunction('UserManager', [
            'action' => 'update',
            'id' => $input['user_id'],
            'data' => $input['user_data']
        ]);
        
        return [
            'success' => isset($userResult['success']) ? $userResult['success'] : false,
            'message' => 'User update workflow completed',
            'user_result' => $userResult,
            'workflow' => 'UserManagementWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function deleteUser($input)
    {
        if (!isset($input['user_id'])) {
            return $this->errorResponse('User ID is required for delete operation');
        }
        
        $userResult = $this->callFunction('UserManager', [
            'action' => 'delete',
            'id' => $input['user_id']
        ]);
        
        return [
            'success' => isset($userResult['success']) ? $userResult['success'] : false,
            'message' => 'User deletion workflow completed',
            'user_result' => $userResult,
            'workflow' => 'UserManagementWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function listUsers($input)
    {
        $userResult = $this->callFunction('UserManager', [
            'action' => 'list',
            'limit' => $input['limit'] ?? 10,
            'offset' => $input['offset'] ?? 0
        ]);
        
        // 결과를 Excel로 내보내기 (선택사항)
        if (isset($userResult['success']) && $userResult['success'] && 
            isset($input['export_excel']) && $input['export_excel']) {
            
            $users = $userResult['data'] ?? [];
            if (!empty($users) && is_array($users)) {
                $this->generateUserReport('User List Export', ['users' => $users]);
            }
        }
        
        return [
            'success' => isset($userResult['success']) ? $userResult['success'] : false,
            'message' => 'User list workflow completed',
            'user_result' => $userResult,
            'workflow' => 'UserManagementWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function generateUserReport($title, $data)
    {
        try {
            // 사용자 데이터를 Excel 형태로 변환
            $excelData = [
                [$title, 'Generated at: ' . now()->toDateTimeString()],
                [''], // 빈 행
            ];
            
            if (isset($data['users']) && is_array($data['users'])) {
                // 사용자 목록인 경우
                $excelData[] = ['ID', 'Name', 'Email', 'Created'];
                foreach ($data['users'] as $user) {
                    $excelData[] = [
                        $user['id'] ?? '',
                        $user['name'] ?? '',
                        $user['email'] ?? '',
                        $user['created_at'] ?? ''
                    ];
                }
            } else {
                // 단일 사용자 데이터인 경우
                $excelData[] = ['Field', 'Value'];
                foreach ($data as $key => $value) {
                    if (!is_array($value) && !is_object($value)) {
                        $excelData[] = [$key, $value];
                    }
                }
            }
            
            $reportResult = $this->callFunction('PHPExcelGenerator', [
                'data' => $excelData,
                'filename' => 'user_report_' . date('Y-m-d_H-i-s') . '.xlsx',
                'sheet_name' => 'User Report',
                'has_headers' => true,
                'auto_width' => true
            ]);
            
            $this->logStep('User report generated', $reportResult);
            
        } catch (\Exception $e) {
            $this->logStep('Failed to generate user report', ['error' => $e->getMessage()]);
        }
    }
    
    private function errorResponse($message, $details = null)
    {
        return [
            'success' => false,
            'message' => $message,
            'details' => $details,
            'workflow' => 'UserManagementWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}