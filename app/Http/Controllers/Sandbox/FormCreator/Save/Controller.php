<?php

namespace App\Http\Controllers\Sandbox\FormCreator\Save;

use App\Models\SandboxForm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'data' => 'required|string',
        ]);

        try {
            // Parse JSON data to validate it
            $formData = json_decode($request->data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data provided');
            }
            
            // Add metadata
            $formData['saved_at'] = now()->toISOString();
            $formData['created_at'] = $formData['created_at'] ?? now()->toISOString();
            $formData['modified_at'] = now()->toISOString();

            // Save to file system (existing behavior)
            $directory = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-1/form-creator');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $filePath = $directory . '/' . $request->filename;
            file_put_contents($filePath, json_encode($formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Save to database for Form Publisher
            SandboxForm::updateOrCreate(
                ['title' => $request->name],
                [
                    'title' => $request->name,
                    'description' => $request->description,
                    'form_fields' => $formData['components'] ?? [],
                    'form_settings' => $formData['settings'] ?? [],
                    'sandbox_id' => 'storage-sandbox-1',
                    'is_active' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => '폼이 저장되었습니다.',
                'filename' => $request->filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '폼 저장 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateFilename(string $name): string
    {
        $slug = preg_replace('/[^가-힣a-zA-Z0-9\-_]/', '', $name);
        $timestamp = date('YmdHis');
        return $slug . '_' . $timestamp . '.json';
    }
}