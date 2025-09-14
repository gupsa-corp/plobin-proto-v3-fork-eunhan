<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

class SandboxTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'settings',
        'created_by',
        'usage_count',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function copyLogs(): HasMany
    {
        return $this->hasMany(SandboxCopyLog::class, 'source_id')
            ->where('source_type', 'template');
    }

    public function getStoragePathAttribute(): string
    {
        return storage_path(env('SANDBOX_TEMPLATE_PATH', 'sandbox/template') . '/' . $this->name);
    }

    public function exists(): bool
    {
        return File::exists($this->storage_path);
    }

    public function getSize(): string
    {
        if (!$this->exists()) {
            return '0 B';
        }

        $size = $this->getDirectorySizeRecursive($this->storage_path);
        return $this->formatBytes($size);
    }

    public function getFileCount(): int
    {
        if (!$this->exists()) {
            return 0;
        }

        try {
            return count(File::allFiles($this->storage_path));
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getDirectorySizeRecursive($path): int
    {
        $size = 0;

        if (is_dir($path)) {
            $files = File::allFiles($path);
            foreach ($files as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function formatBytes($size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 1) . ' ' . $units[$unitIndex];
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    public function scopeCustom($query)
    {
        return $query->where('type', 'custom');
    }
}
