<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiSummaryResultController extends Controller
{
    /**
     * AI 요약 결과 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('ai_summary_results')
                ->join('ai_summary_requests', 'ai_summary_results.request_id', '=', 'ai_summary_requests.id')
                ->select(
                    'ai_summary_results.*',
                    'ai_summary_requests.file_name',
                    'ai_summary_requests.description',
                    'ai_summary_requests.status as request_status'
                );

            // request_id 필터
            if ($request->has('request_id')) {
                $query->where('ai_summary_results.request_id', $request->request_id);
            }

            $results = $query->orderBy('ai_summary_results.created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('AI 요약 결과 목록 조회 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 결과를 불러오는데 실패했습니다.'
            ], 500);
        }
    }

    /**
     * AI 요약 결과 생성 (새 버전 추가)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'request_id' => 'required|integer|exists:ai_summary_requests,id',
                'version' => 'required|string|max:50',
                'summary_content' => 'required|string'
            ]);

            // 요약 요청이 존재하는지 확인
            $summaryRequest = DB::table('ai_summary_requests')->where('id', $request->request_id)->first();
            if (!$summaryRequest) {
                return response()->json([
                    'success' => false,
                    'message' => '요약 요청을 찾을 수 없습니다.'
                ], 404);
            }

            // 버전 중복 확인
            $existingVersion = DB::table('ai_summary_results')
                ->where('request_id', $request->request_id)
                ->where('version', $request->version)
                ->exists();

            if ($existingVersion) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 존재하는 버전입니다.'
                ], 422);
            }

            $result = DB::table('ai_summary_results')->insertGetId([
                'request_id' => $request->request_id,
                'version' => $request->version,
                'summary_content' => $request->summary_content,
                'status' => 'success',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => '새 버전이 추가되었습니다.',
                'data' => ['id' => $result]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI 요약 결과 저장 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 결과 저장에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 특정 요약 결과 조회
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $result = DB::table('ai_summary_results')
                ->join('ai_summary_requests', 'ai_summary_results.request_id', '=', 'ai_summary_requests.id')
                ->select(
                    'ai_summary_results.*',
                    'ai_summary_requests.file_name',
                    'ai_summary_requests.description',
                    'ai_summary_requests.status as request_status'
                )
                ->where('ai_summary_results.id', $id)
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => '요약 결과를 찾을 수 없습니다.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('AI 요약 결과 조회 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 결과를 불러오는데 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 요약 결과 업데이트
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'summary_content' => 'required|string'
            ]);

            $result = DB::table('ai_summary_results')->where('id', $id)->first();
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => '요약 결과를 찾을 수 없습니다.'
                ], 404);
            }

            DB::table('ai_summary_results')
                ->where('id', $id)
                ->update([
                    'summary_content' => $request->summary_content,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => '요약 결과가 업데이트되었습니다.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '입력 데이터가 올바르지 않습니다.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI 요약 결과 업데이트 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 결과 업데이트에 실패했습니다.'
            ], 500);
        }
    }

    /**
     * 요약 결과 삭제
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $result = DB::table('ai_summary_results')->where('id', $id)->first();
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => '요약 결과를 찾을 수 없습니다.'
                ], 404);
            }

            DB::table('ai_summary_results')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => '요약 결과가 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            Log::error('AI 요약 결과 삭제 실패: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '요약 결과 삭제에 실패했습니다.'
            ], 500);
        }
    }
}
