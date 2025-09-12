<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSummaryController extends Controller
{
    /**
     * AI 요약 요청 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $summaries = DB::table('ai_summary_requests')
                ->orderBy('requested_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $summaries
            ]);
        } catch (\Exception $e) {
            Log::error('AI 요약 목록 조회 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 목록을 불러오는데 실패했습니다.'
            ], 500);
        }
    }

    /**
     * AI 요약 요청 생성
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'request_id' => 'required|string|max:100|unique:ai_summary_requests,request_id'
            ]);

            $summaryRequest = DB::table('ai_summary_requests')->insertGetId([
                'file_name' => $request->file_name,
                'description' => $request->description,
                'request_id' => $request->request_id,
                'status' => 'pending',
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'AI 요약 요청이 저장되었습니다.',
                'data' => ['id' => $summaryRequest]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI 요약 요청 저장 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 요청 저장에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * AI 요약 요청 새로고침 (AI 서버에서 상태 확인)
     */
    public function refresh(Request $request, $id): JsonResponse
    {
        try {
            $summaryRequest = DB::table('ai_summary_requests')->where('id', $id)->first();
            
            if (!$summaryRequest) {
                return response()->json([
                    'success' => false,
                    'message' => '요약 요청을 찾을 수 없습니다.'
                ], 404);
            }

            // AI 서버에서 요약 상태 확인
            $aiResponse = Http::timeout(30)->post(config('app.ai_summary_retrieve_url'), [
                'request_id' => $summaryRequest->request_id
            ]);

            if ($aiResponse->successful()) {
                $aiData = $aiResponse->json();
                
                // 상태 업데이트
                $updateData = [];
                if (isset($aiData['status'])) {
                    $updateData['status'] = $aiData['status'];
                }
                if (isset($aiData['summary']) && $aiData['status'] === 'completed') {
                    $updateData['completed_at'] = now();
                    
                    // 요약 결과 저장
                    $this->saveSummaryResult($summaryRequest->id, $aiData['summary']);
                }
                if (isset($aiData['error_message'])) {
                    $updateData['error_message'] = $aiData['error_message'];
                }

                if (!empty($updateData)) {
                    $updateData['updated_at'] = now();
                    DB::table('ai_summary_requests')
                        ->where('id', $id)
                        ->update($updateData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => '요약 상태가 새로고침되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('AI 요약 새로고침 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 새로고침에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 요약 결과 저장
     */
    private function saveSummaryResult(int $requestId, string $summaryContent): void
    {
        $version = $this->generateVersionString();
        
        DB::table('ai_summary_results')->insert([
            'request_id' => $requestId,
            'version' => $version,
            'summary_content' => $summaryContent,
            'status' => 'success',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * 버전 문자열 생성 (v년월일시분초)
     */
    private function generateVersionString(): string
    {
        $now = now();
        return 'v' . $now->format('YmdHis');
    }
}
