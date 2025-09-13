<?php

namespace App\Livewire\Platform\Admin;

use App\Models\SandboxCronJob;
use App\Models\SandboxCronJobLog;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class CronManager extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';
    public $selectedJob = null;

    protected $queryString = ['searchTerm', 'statusFilter', 'typeFilter'];

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function toggleJobStatus($jobId)
    {
        $job = SandboxCronJob::findOrFail($jobId);
        $job->toggle();
        
        session()->flash('message', "Job '{$job->name}' status updated successfully.");
    }

    public function runJobManually($jobId)
    {
        $job = SandboxCronJob::findOrFail($jobId);
        
        $log = $job->logs()->create([
            'status' => 'running',
            'started_at' => now(),
        ]);

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

        session()->flash('message', "Job '{$job->name}' executed successfully.");
    }

    public function viewJobDetails($jobId)
    {
        $this->selectedJob = SandboxCronJob::with(['logs' => function($query) {
            $query->latest()->limit(10);
        }])->findOrFail($jobId);
    }

    public function closeJobDetails()
    {
        $this->selectedJob = null;
    }

    public function deleteJob($jobId)
    {
        $job = SandboxCronJob::findOrFail($jobId);
        $jobName = $job->name;
        $job->delete();
        
        session()->flash('message', "Job '{$jobName}' deleted successfully.");
    }

    public function getJobsProperty()
    {
        return SandboxCronJob::query()
            ->when($this->searchTerm, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('target', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } else {
                    $query->where('is_active', false);
                }
            })
            ->when($this->typeFilter !== 'all', function($query) {
                $query->where('type', $this->typeFilter);
            })
            ->with(['logs' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatisticsProperty()
    {
        $totalJobs = SandboxCronJob::count();
        $activeJobs = SandboxCronJob::where('is_active', true)->count();
        $inactiveJobs = SandboxCronJob::where('is_active', false)->count();
        
        $recentLogs = SandboxCronJobLog::where('created_at', '>=', Carbon::now()->subHours(24));
        $successfulRuns = $recentLogs->clone()->where('status', 'success')->count();
        $failedRuns = $recentLogs->clone()->where('status', 'failed')->count();
        $totalRuns = $successfulRuns + $failedRuns;
        
        return [
            'total_jobs' => $totalJobs,
            'active_jobs' => $activeJobs,
            'inactive_jobs' => $inactiveJobs,
            'total_runs_24h' => $totalRuns,
            'successful_runs_24h' => $successfulRuns,
            'failed_runs_24h' => $failedRuns,
            'success_rate' => $totalRuns > 0 ? round(($successfulRuns / $totalRuns) * 100, 2) : 0
        ];
    }

    public function render()
    {
        return view('platform.admin.cron-manager', [
            'jobs' => $this->jobs,
            'statistics' => $this->statistics
        ]);
    }
}