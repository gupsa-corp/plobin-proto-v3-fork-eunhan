<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DeletedProjects extends Component
{
    public $organizationId;

    public function mount($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    public function restoreProject($projectId)
    {
        try {
            DB::transaction(function () use ($projectId) {
                $project = Project::withTrashed()->findOrFail($projectId);
                
                // 프로젝트 복구
                $project->restore();
                
                // 프로젝트의 모든 페이지도 복구
                $project->projectPages()->withTrashed()->restore();
            });

            session()->flash('message', '프로젝트가 성공적으로 복구되었습니다.');
        } catch (\Exception $e) {
            session()->flash('error', '프로젝트 복구 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function forceDeleteProject($projectId)
    {
        try {
            DB::transaction(function () use ($projectId) {
                $project = Project::withTrashed()->findOrFail($projectId);
                
                // 프로젝트의 모든 페이지를 완전히 삭제
                $project->projectPages()->withTrashed()->forceDelete();
                
                // 프로젝트도 완전히 삭제
                $project->forceDelete();
            });

            session()->flash('message', '프로젝트가 완전히 삭제되었습니다.');
        } catch (\Exception $e) {
            session()->flash('error', '프로젝트 완전 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $deletedProjects = Project::onlyTrashed()
            ->where('organization_id', $this->organizationId)
            ->with(['user', 'organization'])
            ->withCount(['projectPages'])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('livewire.700-livewire-deleted-projects', [
            'deletedProjects' => $deletedProjects
        ]);
    }
}