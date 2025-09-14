<?php

namespace App\Http\Controllers\Sandbox\ProjectSandbox;

use App\Models\ProjectSandbox;
use App\Models\SandboxTemplate;
use App\Models\SandboxCopyLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Controller extends \App\Http\Controllers\Controller
{
    /**
     * 프로젝트별 샌드박스 목록 조회
     */
    public function index(Request $request, $projectId): JsonResponse
    {
        try {
            $project = Project::findOrFail($projectId);

            $sandboxes = $project->sandboxes()
                ->with(['creator'])
                ->orderBy('last_accessed_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($sandbox) {
                    return [
                        'id' => $sandbox->id,
                        'name' => $sandbox->name,
                        'description' => $sandbox->description,
                        'status' => $sandbox->status,
                        'settings' => $sandbox->settings,
                        'last_accessed_at' => $sandbox->last_accessed_at,
                        'created_at' => $sandbox->created_at,
                        'creator' => $sandbox->creator ? [
                            'id' => $sandbox->creator->id,
                            'name' => $sandbox->creator->name,
                        ] : null,
                        'size' => $sandbox->size,
                        'file_count' => $sandbox->file_count,
                        'exists' => $sandbox->exists(),
                        'database_exists' => $sandbox->databaseExists(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sandboxes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * 새 샌드박스 생성 (템플릿에서 복사)
     */
    public function store(Request $request, $projectId): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('project_sandboxes')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                }),
            ],
            'description' => 'nullable|string',
            'template_id' => 'required|exists:sandbox_templates,id',
        ]);

        $project = Project::findOrFail($projectId);
        $template = SandboxTemplate::findOrFail($request->template_id);

        DB::beginTransaction();
        try {
            // 복사 로그 생성
            $copyLog = SandboxCopyLog::create([
                'project_id' => $projectId,
                'source_type' => 'template',
                'source_id' => $template->id,
                'source_name' => $template->name,
                'target_name' => $request->name,
                'created_by' => Auth::id() ?? 1, // 개발환경에서는 기본값 사용
                'status' => 'in_progress',
            ]);

            // 샌드박스 생성
            $sandbox = ProjectSandbox::create([
                'project_id' => $projectId,
                'name' => $request->name,
                'description' => $request->description,
                'status' => 'active',
                'created_by' => Auth::id() ?? 1, // 개발환경에서는 기본값 사용
                'last_accessed_at' => now(),
            ]);

            // 파일 시스템에서 템플릿 복사
            $sourcePath = $template->storage_path;
            $targetPath = $sandbox->storage_path;

            if (!File::exists($sourcePath)) {
                throw new \Exception("템플릿 파일이 존재하지 않습니다: {$template->name}");
            }

            if (!File::copyDirectory($sourcePath, $targetPath)) {
                throw new \Exception("파일 복사에 실패했습니다.");
            }

            // 성공 처리
            $copyLog->markAsSuccess();
            $template->incrementUsage();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '샌드박스가 성공적으로 생성되었습니다.',
                'data' => [
                    'id' => $sandbox->id,
                    'name' => $sandbox->name,
                    'description' => $sandbox->description,
                    'status' => $sandbox->status,
                    'created_at' => $sandbox->created_at,
                    'copy_log_id' => $copyLog->id,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($copyLog)) {
                $copyLog->markAsFailed($e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => '샌드박스 생성에 실패했습니다.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * 샌드박스 상세 조회
     */
    public function show($projectId, $sandboxId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $sandbox = $project->sandboxes()->with(['creator'])->findOrFail($sandboxId);

        // 마지막 접근 시간 업데이트
        $sandbox->updateLastAccessed();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sandbox->id,
                'name' => $sandbox->name,
                'description' => $sandbox->description,
                'status' => $sandbox->status,
                'settings' => $sandbox->settings,
                'last_accessed_at' => $sandbox->last_accessed_at,
                'created_at' => $sandbox->created_at,
                'updated_at' => $sandbox->updated_at,
                'creator' => [
                    'id' => $sandbox->creator->id,
                    'name' => $sandbox->creator->name,
                ],
                'size' => $sandbox->size,
                'file_count' => $sandbox->file_count,
                'exists' => $sandbox->exists(),
                'database_exists' => $sandbox->databaseExists(),
                'storage_path' => $sandbox->storage_path,
                'database_path' => $sandbox->database_path,
            ],
        ]);
    }

    /**
     * 샌드박스 이름/설명 수정
     */
    public function update(Request $request, $projectId, $sandboxId): JsonResponse
    {
        $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('project_sandboxes')->where(function ($query) use ($projectId, $sandboxId) {
                    return $query->where('project_id', $projectId)->where('id', '!=', $sandboxId);
                }),
            ],
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:active,inactive,error',
        ]);

        $project = Project::findOrFail($projectId);
        $sandbox = $project->sandboxes()->findOrFail($sandboxId);

        $oldName = $sandbox->name;
        $sandbox->update($request->only(['name', 'description', 'status']));

        // 이름이 변경된 경우 파일 시스템의 디렉토리명도 변경
        if ($request->has('name') && $request->name !== $oldName) {
            $oldPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/' . $oldName);
            $newPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/' . $request->name);

            if (File::exists($oldPath)) {
                File::move($oldPath, $newPath);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '샌드박스가 성공적으로 업데이트되었습니다.',
            'data' => [
                'id' => $sandbox->id,
                'name' => $sandbox->name,
                'description' => $sandbox->description,
                'status' => $sandbox->status,
                'updated_at' => $sandbox->updated_at,
            ],
        ]);
    }

    /**
     * 샌드박스 삭제
     */
    public function destroy($projectId, $sandboxId): JsonResponse
    {
        $project = Project::findOrFail($projectId);
        $sandbox = $project->sandboxes()->findOrFail($sandboxId);

        DB::beginTransaction();
        try {
            $sandboxName = $sandbox->name;
            $storagePath = $sandbox->storage_path;

            // 데이터베이스에서 삭제
            $sandbox->delete();

            // 파일 시스템에서 삭제
            if (File::exists($storagePath)) {
                File::deleteDirectory($storagePath);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '샌드박스가 성공적으로 삭제되었습니다.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '샌드박스 삭제에 실패했습니다.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * 샌드박스 복사 (샌드박스에서 다른 샌드박스로)
     */
    public function copy(Request $request, $projectId, $sandboxId): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('project_sandboxes')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                }),
            ],
            'description' => 'nullable|string',
        ]);

        $project = Project::findOrFail($projectId);
        $sourceSandbox = $project->sandboxes()->findOrFail($sandboxId);

        DB::beginTransaction();
        try {
            // 복사 로그 생성
            $copyLog = SandboxCopyLog::create([
                'project_id' => $projectId,
                'source_type' => 'sandbox',
                'source_id' => $sourceSandbox->id,
                'source_name' => $sourceSandbox->name,
                'target_name' => $request->name,
                'created_by' => Auth::id() ?? 1, // 개발환경에서는 기본값 사용
                'status' => 'in_progress',
            ]);

            // 새 샌드박스 생성
            $newSandbox = ProjectSandbox::create([
                'project_id' => $projectId,
                'name' => $request->name,
                'description' => $request->description,
                'status' => 'active',
                'created_by' => Auth::id() ?? 1, // 개발환경에서는 기본값 사용
                'last_accessed_at' => now(),
            ]);

            // 파일 시스템에서 복사
            $sourcePath = $sourceSandbox->storage_path;
            $targetPath = $newSandbox->storage_path;

            if (!File::exists($sourcePath)) {
                throw new \Exception("소스 샌드박스 파일이 존재하지 않습니다: {$sourceSandbox->name}");
            }

            if (!File::copyDirectory($sourcePath, $targetPath)) {
                throw new \Exception("파일 복사에 실패했습니다.");
            }

            // 성공 처리
            $copyLog->markAsSuccess();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '샌드박스가 성공적으로 복사되었습니다.',
                'data' => [
                    'id' => $newSandbox->id,
                    'name' => $newSandbox->name,
                    'description' => $newSandbox->description,
                    'status' => $newSandbox->status,
                    'created_at' => $newSandbox->created_at,
                    'copy_log_id' => $copyLog->id,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($copyLog)) {
                $copyLog->markAsFailed($e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => '샌드박스 복사에 실패했습니다.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
