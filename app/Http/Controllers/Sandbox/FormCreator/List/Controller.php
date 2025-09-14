<?php

namespace App\Http\Controllers\Sandbox\FormCreator\List;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $formsPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-1/form-creator/');
            
            if (!is_dir($formsPath)) {
                mkdir($formsPath, 0755, true);
            }

            $files = glob($formsPath . '*.json');
            $forms = [];

            foreach ($files as $file) {
                $content = file_get_contents($file);
                $formData = json_decode($content, true);
                
                if ($formData) {
                    $forms[] = [
                        'name' => $formData['name'] ?? 'Untitled Form',
                        'description' => $formData['description'] ?? '',
                        'filename' => basename($file),
                        'created_at' => $formData['created_at'] ?? $formData['saved_at'] ?? date('c'),
                        'modified_at' => $formData['modified_at'] ?? $formData['saved_at'] ?? date('c'),
                        'component_count' => count($formData['components'] ?? []),
                    ];
                }
            }

            // 최신순으로 정렬
            usort($forms, function($a, $b) {
                return strtotime($b['modified_at']) - strtotime($a['modified_at']);
            });

            return response()->json([
                'success' => true,
                'forms' => $forms,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '폼 목록 로드 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}