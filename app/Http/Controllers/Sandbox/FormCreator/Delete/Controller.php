<?php

namespace App\Http\Controllers\Sandbox\FormCreator\Delete;

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

            if (!unlink($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => '폼 파일을 삭제할 수 없습니다.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => '폼이 삭제되었습니다.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '폼 삭제 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}