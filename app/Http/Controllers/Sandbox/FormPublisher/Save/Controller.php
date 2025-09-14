<?php

namespace App\Http\Controllers\Sandbox\FormPublisher\Save;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // 샌드박스 FormManager 클래스 로드
            $formManagerPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-1/Backend/Functions/FormPublisher/FormManager.php');
            require_once $formManagerPath;
            
            $title = $request->input('title', '');
            $description = $request->input('description', '');
            $formJson = $request->input('form_json', '');
            $editingId = $request->input('editing_id') ? (int)$request->input('editing_id') : null;

            if (empty($title)) {
                return response()->json([
                    'success' => false,
                    'message' => '폼 제목이 필요합니다.'
                ]);
            }

            if (empty($formJson)) {
                return response()->json([
                    'success' => false,
                    'message' => '폼 JSON이 필요합니다.'
                ]);
            }

            $formManager = new \FormManager();
            $result = $formManager->saveForm($title, $description, $formJson, $editingId);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? $result['message'] : $result['error'],
                'form_id' => $result['success'] ? $result['id'] : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}