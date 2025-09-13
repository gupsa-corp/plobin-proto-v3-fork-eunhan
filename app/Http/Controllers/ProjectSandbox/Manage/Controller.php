<?php

namespace App\Http\Controllers\ProjectSandbox\Manage;

use Illuminate\Routing\Controller as BaseController;
use App\Models\Project;
use App\Models\ProjectSandbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    private function getStoragePath()
    {
        return storage_path();
    }

    private function getTemplateStoragePath()
    {
        return $this->getStoragePath() . '/storage-sandbox-template';
    }

    public function index($organizationId, $projectId)
    {
        // 인증 확인 (개발용으로 임시 비활성화)
        // $user = Auth::user();
        // if (!$user) {
        //     return redirect('/login');
        // }

        $project = Project::findOrFail($projectId);

        // 프로젝트 접근 권한 확인 (개발용으로 임시 비활성화)
        // if (!$project->canUserAccess($user)) {
        //     abort(403);
        // }

        $sandboxes = $project->sandboxes()->with('creator')->orderBy('created_at', 'desc')->get();

        return view('300-page-service.318-page-project-settings-sandboxes.000-index', compact('project', 'sandboxes'));
    }

    public function create(Request $request, $organizationId, $projectId)
    {
        // 인증 확인
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $project = Project::findOrFail($projectId);

        // 프로젝트 접근 권한 확인
        if (!$project->canUserAccess($user)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:50',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:development,testing,staging,demo'
        ], [
            'name.required' => '샌드박스 이름을 입력해주세요.',
            'name.regex' => '영문자, 숫자, 하이픈(-), 언더스코어(_)만 사용 가능합니다.',
            'name.max' => '샌드박스 이름은 50자를 초과할 수 없습니다.',
            'type.required' => '샌드박스 타입을 선택해주세요.',
        ]);

        $sandboxName = $request->name;
        $targetPath = $this->getStoragePath() . '/storage-sandbox-' . $sandboxName;

        // 이미 존재하는 샌드박스 이름 확인 (전역)
        if (File::exists($targetPath)) {
            return back()->withErrors(['name' => '이미 존재하는 샌드박스 이름입니다.']);
        }

        // 프로젝트 내에서 중복 확인
        $existingSandbox = $project->sandboxes()->where('name', $sandboxName)->first();
        if ($existingSandbox) {
            return back()->withErrors(['name' => '이 프로젝트에서 이미 사용 중인 샌드박스 이름입니다.']);
        }

        // 템플릿 존재 확인
        if (!File::exists($this->getTemplateStoragePath())) {
            return back()->with('error', '템플릿 샌드박스가 존재하지 않습니다.');
        }

        try {
            // 템플릿 복사
            File::copyDirectory($this->getTemplateStoragePath(), $targetPath);

            // DB에 샌드박스 정보 저장
            $sandbox = ProjectSandbox::create([
                'project_id' => $project->id,
                'name' => $sandboxName,
                'description' => $request->description,
                'status' => 'active',
                'settings' => [
                    'type' => $request->type,
                    'created_from_template' => true
                ],
                'created_by' => Auth::id(),
            ]);

            return back()->with('success', "샌드박스 '{$sandboxName}'이 성공적으로 생성되었습니다.");
        } catch (\Exception $e) {
            return back()->with('error', '샌드박스 생성 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function delete(Request $request, $organizationId, $projectId)
    {
        // 인증 확인
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $project = Project::findOrFail($projectId);

        // 프로젝트 접근 권한 확인
        if (!$project->canUserAccess($user)) {
            abort(403);
        }

        $request->validate([
            'sandbox_id' => 'required|exists:project_sandboxes,id',
        ]);

        $sandbox = ProjectSandbox::findOrFail($request->sandbox_id);

        // 프로젝트 소유권 확인
        if ($sandbox->project_id !== $project->id) {
            abort(403);
        }

        $storagePath = $this->getStoragePath() . '/storage-sandbox-' . $sandbox->name;

        try {
            // 파일 시스템에서 삭제
            if (File::exists($storagePath)) {
                File::deleteDirectory($storagePath);
            }

            // DB에서 삭제
            $sandbox->delete();

            return back()->with('success', "샌드박스 '{$sandbox->name}'이 성공적으로 삭제되었습니다.");
        } catch (\Exception $e) {
            return back()->with('error', '샌드박스 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $organizationId, $projectId)
    {
        // 인증 확인
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $project = Project::findOrFail($projectId);

        // 프로젝트 접근 권한 확인
        if (!$project->canUserAccess($user)) {
            abort(403);
        }

        $request->validate([
            'sandbox_id' => 'required|exists:project_sandboxes,id',
        ]);

        $sandbox = ProjectSandbox::findOrFail($request->sandbox_id);

        // 프로젝트 소유권 확인
        if ($sandbox->project_id !== $project->id) {
            abort(403);
        }

        $newStatus = $sandbox->status === 'active' ? 'inactive' : 'active';
        $sandbox->update(['status' => $newStatus]);

        $statusText = $newStatus === 'active' ? '활성화' : '비활성화';
        return back()->with('success', "샌드박스 '{$sandbox->name}'이 {$statusText}되었습니다.");
    }
}
