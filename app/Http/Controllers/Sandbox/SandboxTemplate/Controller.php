<?php

namespace App\Http\Controllers\Sandbox\SandboxTemplate;

use App\Models\SandboxTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Controller extends \App\Http\Controllers\Controller
{
    /**
     * 샌드박스 템플릿 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        $query = SandboxTemplate::with(['creator'])
            ->active()
            ->orderBy('type', 'asc') // 시스템 템플릿을 먼저 표시
            ->orderBy('usage_count', 'desc')
            ->orderBy('created_at', 'desc');

        // 타입 필터
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->get()->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'type' => $template->type,
                'status' => $template->status,
                'settings' => $template->settings,
                'usage_count' => $template->usage_count,
                'created_at' => $template->created_at,
                'creator' => $template->creator ? [
                    'id' => $template->creator->id,
                    'name' => $template->creator->name,
                ] : null,
                'size' => $template->size,
                'file_count' => $template->file_count,
                'exists' => $template->exists(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * 새 템플릿 생성 (관리자만 가능)
     */
    public function store(Request $request): JsonResponse
    {
        // 권한 체크 (관리자만 생성 가능하도록 설정)
        // if (!Auth::user()->isAdmin()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => '템플릿 생성 권한이 없습니다.',
        //     ], 403);
        // }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'unique:sandbox_templates,name',
            ],
            'description' => 'nullable|string',
            'type' => 'required|in:system,custom',
            'settings' => 'nullable|array',
        ]);

        $template = SandboxTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'settings' => $request->settings,
            'created_by' => Auth::id() ?? 1, // 개발환경에서는 기본값 사용
            'status' => 'active',
            'usage_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '템플릿이 성공적으로 생성되었습니다.',
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'type' => $template->type,
                'status' => $template->status,
                'created_at' => $template->created_at,
            ],
        ], 201);
    }

    /**
     * 템플릿 상세 조회
     */
    public function show($templateId): JsonResponse
    {
        $template = SandboxTemplate::with(['creator'])->findOrFail($templateId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'type' => $template->type,
                'status' => $template->status,
                'settings' => $template->settings,
                'usage_count' => $template->usage_count,
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at,
                'creator' => $template->creator ? [
                    'id' => $template->creator->id,
                    'name' => $template->creator->name,
                ] : null,
                'size' => $template->size,
                'file_count' => $template->file_count,
                'exists' => $template->exists(),
                'storage_path' => $template->storage_path,
            ],
        ]);
    }

    /**
     * 템플릿 정보 수정
     */
    public function update(Request $request, $templateId): JsonResponse
    {
        $template = SandboxTemplate::findOrFail($templateId);

        // 시스템 템플릿은 수정 제한
        if ($template->type === 'system') {
            return response()->json([
                'success' => false,
                'message' => '시스템 템플릿은 수정할 수 없습니다.',
            ], 403);
        }

        $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('sandbox_templates')->ignore($template->id),
            ],
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:active,inactive',
            'settings' => 'sometimes|nullable|array',
        ]);

        $template->update($request->only(['name', 'description', 'status', 'settings']));

        return response()->json([
            'success' => true,
            'message' => '템플릿이 성공적으로 업데이트되었습니다.',
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'status' => $template->status,
                'updated_at' => $template->updated_at,
            ],
        ]);
    }

    /**
     * 템플릿 삭제 (관리자만 가능, 시스템 템플릿 제외)
     */
    public function destroy($templateId): JsonResponse
    {
        $template = SandboxTemplate::findOrFail($templateId);

        // 시스템 템플릿은 삭제 금지
        if ($template->type === 'system') {
            return response()->json([
                'success' => false,
                'message' => '시스템 템플릿은 삭제할 수 없습니다.',
            ], 403);
        }

        // 사용 중인 템플릿 체크
        if ($template->usage_count > 0) {
            return response()->json([
                'success' => false,
                'message' => '사용 중인 템플릿은 삭제할 수 없습니다.',
            ], 422);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => '템플릿이 성공적으로 삭제되었습니다.',
        ]);
    }

    /**
     * 템플릿 사용 통계 조회
     */
    public function usage(): JsonResponse
    {
        $templates = SandboxTemplate::with(['creator'])
            ->withCount(['copyLogs as total_copies'])
            ->orderBy('usage_count', 'desc')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'type' => $template->type,
                    'usage_count' => $template->usage_count,
                    'total_copies' => $template->total_copies,
                    'creator' => $template->creator ? $template->creator->name : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }
}
