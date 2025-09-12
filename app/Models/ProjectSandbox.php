<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;

class ProjectSandbox extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'template_id',
        'status',
        'settings',
        'last_accessed_at',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'last_accessed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStoragePathAttribute(): string
    {
        return storage_path('sandbox/' . $this->name);
    }

    public function getDatabasePathAttribute(): string
    {
        return $this->storage_path . '/Backend/Databases/Release.sqlite';
    }

    public function exists(): bool
    {
        return File::exists($this->storage_path);
    }

    public function databaseExists(): bool
    {
        return File::exists($this->database_path);
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

    public function updateLastAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }
}
