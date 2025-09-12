<?php

namespace App\Livewire\SandboxManagement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProjectSandbox;

class SandboxList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $showCreateModal = false;
    public $showDeleteModal = false;
    public $deletingSandboxId = null;
    public $newSandbox = [
        'name' => '',
        'description' => '',
        'template_id' => null,
    ];

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->newSandbox = [
            'name' => '',
            'description' => '',
            'template_id' => null,
        ];
    }

    public function createSandbox()
    {
        $this->validate([
            'newSandbox.name' => 'required|string|max:255',
            'newSandbox.description' => 'nullable|string',
            'newSandbox.template_id' => 'nullable|string',
        ]);

        ProjectSandbox::create([
            'project_id' => null, // 플랫폼 레벨 샌드박스는 특정 프로젝트와 연결되지 않음
            'name' => $this->newSandbox['name'],
            'description' => $this->newSandbox['description'],
            'template_id' => $this->newSandbox['template_id'],
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        $this->reset('newSandbox');
        
        session()->flash('message', '샌드박스가 성공적으로 생성되었습니다.');
    }

    public function confirmDelete($sandboxId)
    {
        $this->deletingSandboxId = $sandboxId;
        $this->showDeleteModal = true;
    }

    public function deleteSandbox()
    {
        if ($this->deletingSandboxId) {
            $sandbox = ProjectSandbox::find($this->deletingSandboxId);
            if ($sandbox) {
                $sandbox->delete();
                session()->flash('message', '샌드박스가 삭제되었습니다.');
            }
        }
        
        $this->showDeleteModal = false;
        $this->deletingSandboxId = null;
    }

    public function render()
    {
        $sandboxes = ProjectSandbox::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        // 템플릿 목록 (임시 데이터)
        $templates = [
            [
                'name' => 'basic',
                'display_name' => '기본 템플릿',
                'file_count' => 5
            ],
            [
                'name' => 'react',
                'display_name' => 'React 프로젝트',
                'file_count' => 12
            ],
            [
                'name' => 'vue',
                'display_name' => 'Vue 프로젝트',
                'file_count' => 10
            ]
        ];

        return view('livewire.sandbox-management.sandbox-list', [
            'sandboxes' => $sandboxes,
            'templates' => $templates
        ]);
    }
}
