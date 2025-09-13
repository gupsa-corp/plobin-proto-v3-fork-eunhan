<?php

namespace App\Livewire\Sandbox;

use App\Models\SandboxCronJob;
use Livewire\Component;
use Livewire\WithPagination;

class CronManager extends Component
{
    use WithPagination;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingJobId = null;
    
    public $name = '';
    public $description = '';
    public $schedule = '';
    public $type = 'url';
    public $target = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'schedule' => 'required|string',
        'type' => 'required|in:url,command,class',
        'target' => 'required|string',
        'is_active' => 'boolean'
    ];

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->reset(['name', 'description', 'schedule', 'target']);
        $this->type = 'url';
        $this->is_active = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createJob()
    {
        $this->validate();

        $job = SandboxCronJob::create([
            'name' => $this->name,
            'description' => $this->description,
            'schedule' => $this->schedule,
            'type' => $this->type,
            'target' => $this->target,
            'is_active' => $this->is_active,
        ]);

        $job->updateNextRunTime();

        $this->closeCreateModal();
        session()->flash('message', 'Cron job created successfully.');
    }

    public function toggleJob($jobId)
    {
        $job = SandboxCronJob::findOrFail($jobId);
        $job->toggle();
        
        session()->flash('message', 'Job status updated successfully.');
    }

    public function deleteJob($jobId)
    {
        SandboxCronJob::findOrFail($jobId)->delete();
        session()->flash('message', 'Cron job deleted successfully.');
    }

    public function runJob($jobId)
    {
        $job = SandboxCronJob::findOrFail($jobId);
        
        // 간단한 실행 로그 생성 (실제 실행은 별도 서비스에서)
        $log = $job->logs()->create([
            'status' => 'running',
            'started_at' => now(),
        ]);

        // URL 호출 시뮬레이션
        if ($job->type === 'url') {
            try {
                $response = file_get_contents($job->target);
                $log->markAsSuccess($response);
                $job->recordSuccess($response);
            } catch (\Exception $e) {
                $log->markAsFailed($e->getMessage());
                $job->recordFailure($e->getMessage());
            }
        } else {
            $log->markAsSuccess('Manual execution completed');
            $job->recordSuccess('Manual execution');
        }

        session()->flash('message', 'Job executed successfully.');
    }

    public function render()
    {
        $jobs = SandboxCronJob::with(['logs' => function($query) {
            $query->latest()->limit(1);
        }])->paginate(10);

        return view('sandbox.cron-manager', compact('jobs'));
    }
}