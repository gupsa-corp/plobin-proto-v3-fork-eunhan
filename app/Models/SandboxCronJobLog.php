<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SandboxCronJobLog extends Model
{
    use HasFactory;

    protected $table = 'sandbox_cron_job_logs';

    protected $fillable = [
        'cron_job_id',
        'status',
        'output',
        'error_message',
        'duration_ms',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the cron job that owns this log.
     */
    public function cronJob(): BelongsTo
    {
        return $this->belongsTo(SandboxCronJob::class);
    }

    /**
     * Get the duration in seconds.
     */
    public function getDurationAttribute(): float
    {
        return $this->duration_ms ? round($this->duration_ms / 1000, 2) : 0;
    }

    /**
     * Check if the job is still running.
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if the job succeeded.
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if the job failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark the log as completed with success.
     */
    public function markAsSuccess(string $output = null, int $durationMs = null): void
    {
        $this->update([
            'status' => 'success',
            'output' => $output,
            'duration_ms' => $durationMs ?: $this->calculateDuration(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the log as failed.
     */
    public function markAsFailed(string $errorMessage = null, int $durationMs = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs ?: $this->calculateDuration(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate duration from started_at to now.
     */
    private function calculateDuration(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        return $this->started_at->diffInMilliseconds(now());
    }
}