<?php

namespace App\Http\Controllers\Sandbox\FormCreator\Load;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __invoke(Request $request, string $filename): JsonResponse
    {
        try {
            $filePath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-1/form-creator/' . $filename);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '폼 파일을 찾을 수 없습니다.',
                ], 404);
            }

            $content = file_get_contents($filePath);
            $formData = json_decode($content, true);

            if (!$formData) {
                return response()->json([
                    'success' => false,
                    'message' => '잘못된 폼 파일 형식입니다.',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'form' => $formData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '폼 로드 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}