<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Cron\CronExpression;

class SandboxCronJob extends Model
{
    use HasFactory;

    protected $table = 'sandbox_cron_jobs';

    protected $fillable = [
        'name',
        'description',
        'schedule',
        'type',
        'target',
        'options',
        'is_active',
        'last_run_at',
        'next_run_at',
        'run_count',
        'success_count',
        'failure_count',
        'last_output',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_count' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
    ];

    /**
     * Get the cron job logs for this job.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SandboxCronJobLog::class, 'cron_job_id');
    }

    /**
     * Calculate and update the next run time based on cron expression.
     */
    public function updateNextRunTime(): void
    {
        try {
            $cron = new CronExpression($this->schedule);
            $this->next_run_at = $cron->getNextRunDate();
            $this->save();
        } catch (\Exception $e) {
            // Invalid cron expression, set next run to null
            $this->next_run_at = null;
            $this->save();
        }
    }

    /**
     * Check if this job should run now.
     */
    public function shouldRun(): bool
    {
        if (!$this->is_active || !$this->next_run_at) {
            return false;
        }

        return $this->next_run_at <= now();
    }

    /**
     * Get the human-readable schedule description.
     */
    public function getScheduleDescriptionAttribute(): string
    {
        try {
            $cron = new CronExpression($this->schedule);
            
            // Basic human-readable descriptions
            $descriptions = [
                '* * * * *' => '매분',
                '0 * * * *' => '매시간',
                '0 0 * * *' => '매일 자정',
                '0 12 * * *' => '매일 정오',
                '0 0 * * 0' => '매주 일요일 자정',
                '0 0 1 * *' => '매월 1일 자정',
                '0 0 1 1 *' => '매년 1월 1일 자정',
            ];

            if (isset($descriptions[$this->schedule])) {
                return $descriptions[$this->schedule];
            }

            // Parse the cron expression parts
            $parts = explode(' ', $this->schedule);
            if (count($parts) !== 5) {
                return $this->schedule;
            }

            [$minute, $hour, $day, $month, $dayOfWeek] = $parts;

            $description = [];

            // Minute
            if ($minute === '*') {
                $description[] = '매분';
            } elseif (is_numeric($minute)) {
                $description[] = "{$minute}분";
            }

            // Hour  
            if ($hour === '*' && $minute !== '*') {
                $description[] = '매시간';
            } elseif (is_numeric($hour)) {
                $description[] = "{$hour}시";
            }

            // Day
            if ($day === '*' && ($hour !== '*' || $minute !== '*')) {
                $description[] = '매일';
            } elseif (is_numeric($day)) {
                $description[] = "{$day}일";
            }

            return implode(' ', $description) ?: $this->schedule;

        } catch (\Exception $e) {
            return $this->schedule;
        }
    }

    /**
     * Get the success rate percentage.
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->run_count === 0) {
            return 0;
        }

        return round(($this->success_count / $this->run_count) * 100, 1);
    }

    /**
     * Record a successful run.
     */
    public function recordSuccess(string $output = null): void
    {
        $this->increment('run_count');
        $this->increment('success_count');
        $this->last_run_at = now();
        $this->last_output = $output;
        $this->updateNextRunTime();
    }

    /**
     * Record a failed run.
     */
    public function recordFailure(string $error = null): void
    {
        $this->increment('run_count');
        $this->increment('failure_count');
        $this->last_run_at = now();
        $this->last_output = $error;
        $this->updateNextRunTime();
    }

    /**
     * Toggle the active status.
     */
    public function toggle(): bool
    {
        $this->is_active = !$this->is_active;
        
        if ($this->is_active) {
            $this->updateNextRunTime();
        } else {
            $this->next_run_at = null;
        }
        
        return $this->save();
    }

    /**
     * Get jobs that are due to run.
     */
    public static function getDueJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where('next_run_at', '<=', now())
            ->whereNotNull('next_run_at')
            ->get();
    }
}