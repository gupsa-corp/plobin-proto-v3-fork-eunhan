<?php

namespace App\Http\Controllers\Sandbox\AIServer\Callback;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    private array $supportedActions = [
        'process_complete',
        'process_started',
        'process_error',
        'status_update',
        'data_processed',
        'webhook_received'
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|max:100',
            'data' => 'nullable|array',
            'timestamp' => 'nullable|string',
        ]);

        try {
            $callbackId = $this->generateCallbackId();
            $action = $request->input('action');
            
            // Validate action
            if (!in_array($action, $this->supportedActions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported action: ' . $action,
                    'callback_id' => $callbackId
                ], 400);
            }

            // Log the callback
            $this->logCallback($callbackId, $request->all());

            // Process the callback
            $result = $this->processCallback($action, $request->input('data', []), $callbackId);

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully',
                'data' => [
                    'processed' => true,
                    'callback_id' => $callbackId,
                    'action_result' => $result
                ]
            ]);

        } catch (\Exception $e) {
            $errorId = uniqid('err_');
            Log::error("AI Server Callback Error [{$errorId}]: " . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process callback',
                'error_id' => $errorId
            ], 500);
        }
    }

    private function processCallback(string $action, array $data, string $callbackId): array
    {
        switch ($action) {
            case 'process_complete':
                return $this->handleProcessComplete($data, $callbackId);
            
            case 'process_started':
                return $this->handleProcessStarted($data, $callbackId);
            
            case 'process_error':
                return $this->handleProcessError($data, $callbackId);
            
            case 'status_update':
                return $this->handleStatusUpdate($data, $callbackId);
            
            case 'data_processed':
                return $this->handleDataProcessed($data, $callbackId);
            
            case 'webhook_received':
                return $this->handleWebhookReceived($data, $callbackId);
            
            default:
                return ['status' => 'unknown_action'];
        }
    }

    private function handleProcessComplete(array $data, string $callbackId): array
    {
        $processId = $data['process_id'] ?? 'unknown';
        $result = $data['result'] ?? [];
        
        // Store completion data
        $this->storeProcessResult($processId, $result, $callbackId);
        
        Log::info("Process completed", [
            'process_id' => $processId,
            'callback_id' => $callbackId,
            'result_keys' => array_keys($result)
        ]);
        
        return [
            'status' => 'completed',
            'process_id' => $processId,
            'result_stored' => true
        ];
    }

    private function handleProcessStarted(array $data, string $callbackId): array
    {
        $processId = $data['process_id'] ?? 'unknown';
        
        Log::info("Process started", [
            'process_id' => $processId,
            'callback_id' => $callbackId
        ]);
        
        return [
            'status' => 'started',
            'process_id' => $processId,
            'acknowledged' => true
        ];
    }

    private function handleProcessError(array $data, string $callbackId): array
    {
        $processId = $data['process_id'] ?? 'unknown';
        $error = $data['error'] ?? 'Unknown error';
        
        Log::error("AI Process Error", [
            'process_id' => $processId,
            'error' => $error,
            'callback_id' => $callbackId
        ]);
        
        return [
            'status' => 'error_logged',
            'process_id' => $processId,
            'error_acknowledged' => true
        ];
    }

    private function handleStatusUpdate(array $data, string $callbackId): array
    {
        $processId = $data['process_id'] ?? 'unknown';
        $status = $data['status'] ?? 'unknown';
        $progress = $data['progress'] ?? 0;
        
        Log::info("Status update received", [
            'process_id' => $processId,
            'status' => $status,
            'progress' => $progress,
            'callback_id' => $callbackId
        ]);
        
        return [
            'status' => 'status_updated',
            'process_id' => $processId,
            'current_status' => $status,
            'progress' => $progress
        ];
    }

    private function handleDataProcessed(array $data, string $callbackId): array
    {
        $dataId = $data['data_id'] ?? 'unknown';
        $records = $data['records_processed'] ?? 0;
        
        Log::info("Data processed", [
            'data_id' => $dataId,
            'records_processed' => $records,
            'callback_id' => $callbackId
        ]);
        
        return [
            'status' => 'data_acknowledged',
            'data_id' => $dataId,
            'records_processed' => $records
        ];
    }

    private function handleWebhookReceived(array $data, string $callbackId): array
    {
        $webhookId = $data['webhook_id'] ?? 'unknown';
        $source = $data['source'] ?? 'unknown';
        
        Log::info("Webhook received", [
            'webhook_id' => $webhookId,
            'source' => $source,
            'callback_id' => $callbackId
        ]);
        
        return [
            'status' => 'webhook_acknowledged',
            'webhook_id' => $webhookId,
            'source' => $source
        ];
    }

    private function storeProcessResult(string $processId, array $result, string $callbackId): void
    {
        $directory = storage_path('storage-sandbox-1/ai-server/results');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $filename = $directory . '/' . $processId . '_' . $callbackId . '.json';
        $storage = [
            'process_id' => $processId,
            'callback_id' => $callbackId,
            'result' => $result,
            'stored_at' => now()->toISOString(),
            'sandbox' => 'storage-sandbox-1'
        ];
        
        file_put_contents($filename, json_encode($storage, JSON_PRETTY_PRINT));
    }

    private function logCallback(string $callbackId, array $data): void
    {
        $directory = storage_path('storage-sandbox-1/ai-server/logs');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $logFile = $directory . '/callbacks.log';
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'callback_id' => $callbackId,
            'action' => $data['action'] ?? 'unknown',
            'data' => $data,
            'ip' => $request->ip ?? 'unknown',
            'user_agent' => request()->header('User-Agent', 'unknown')
        ];
        
        file_put_contents(
            $logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    private function generateCallbackId(): string
    {
        return 'cb_' . uniqid() . '_' . time();
    }
}