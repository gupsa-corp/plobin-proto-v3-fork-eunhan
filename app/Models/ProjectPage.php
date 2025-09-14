<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PageAccessLevel;
use App\Enums\ProjectRole;
use App\Services\ProjectChangeLogService;

class ProjectPage extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'slug',
        'content',
        'parent_id',
        'user_id',
        'sandbox_folder',
        'sandbox_mode',
        'sandbox_custom_screen_folder',
        'sandbox_domain',
        'custom_screen_enabled',
        'custom_screen_applied_at',
        'template_path',
        'access_level',
        'allowed_roles',
        'sort_order',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'custom_screen_enabled' => 'boolean',
        'custom_screen_applied_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function ($page) {
            ProjectChangeLogService::logPageCreated($page->project_id, $page->id, $page->title);
        });

        static::updated(function ($page) {
            if ($page->wasChanged()) {
                $changes = $page->getChanges();
                // timestamps는 제외
                unset($changes['updated_at'], $changes['created_at']);

                if (!empty($changes)) {
                    ProjectChangeLogService::logPageUpdated($page->project_id, $page->id, $page->title, $changes);
                }
            }
        });

        static::deleted(function ($page) {
            ProjectChangeLogService::logPageDeleted($page->project_id, $page->id, $page->title);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectPage::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProjectPage::class, 'parent_id');
    }

    public function deploymentLogs(): HasMany
    {
        return $this->hasMany(ProjectPageDeploymentLog::class);
    }

    // 탭용 페이지들 (parent_id가 null인 것들)
    public function scopeTabs($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Sandbox 폴더 관련 헬퍼 메서드들
     */

    /**
     * Sandbox 폴더 경로 반환
     */
    public function getSandboxFolder(): ?string
    {
        return $this->sandbox_folder;
    }

    /**
     * Sandbox 커스텀 스크린 폴더 경로 반환
     */
    public function getSandboxCustomScreenFolder(): ?string
    {
        return $this->sandbox_custom_screen_folder;
    }

    /**
     * Sandbox 설정이 있는지 확인
     */
    public function hasSandboxSettings(): bool
    {
        return !empty($this->sandbox_folder) || !empty($this->sandbox_custom_screen_folder);
    }

    /**
     * 모든 sandbox 설정을 배열로 반환
     */
    public function getSandboxSettings(): array
    {
        return [
            'folder' => $this->getSandboxFolder(),
            'custom_screen_folder' => $this->getSandboxCustomScreenFolder(),
        ];
    }

    /**
     * Access Control 관련 메소드들
     */

    /**
     * 페이지의 실효적인 접근 레벨을 반환
     */
    public function getEffectivePageAccessLevel(): PageAccessLevel
    {
        // 페이지에 명시적으로 설정된 접근 레벨이 있으면 사용
        if ($this->access_level) {
            try {
                return PageAccessLevel::from($this->access_level);
            } catch (\ValueError $e) {
                // 잘못된 값인 경우 기본값 사용
            }
        }

        // 페이지 레벨 설정이 없으면 프로젝트의 기본 접근 레벨 사용
        $project = $this->project;
        if ($project && $project->default_access_level) {
            try {
                return PageAccessLevel::from($project->default_access_level);
            } catch (\ValueError $e) {
                // 잘못된 값인 경우 기본값 사용
            }
        }

        // 모든 설정이 없으면 기본값 반환
        return PageAccessLevel::MEMBER;
    }

    /**
     * 페이지의 접근 레벨을 설정
     */
    public function setAccessLevel(PageAccessLevel $accessLevel): void
    {
        $this->access_level = $accessLevel->value;
    }

    /**
     * 커스텀 접근 역할 설정
     */
    public function setAllowedRoles(array $roles): void
    {
        $this->allowed_roles = $roles;
    }

    /**
     * 커스텀 접근 역할 추가
     */
    public function addAllowedRole(string $role): void
    {
        $allowedRoles = $this->allowed_roles ?? [];
        if (!in_array($role, $allowedRoles)) {
            $allowedRoles[] = $role;
            $this->allowed_roles = $allowedRoles;
        }
    }

    /**
     * 커스텀 접근 역할 제거
     */
    public function removeAllowedRole(string $role): void
    {
        $allowedRoles = $this->allowed_roles ?? [];
        $this->allowed_roles = array_values(array_filter($allowedRoles, function($r) use ($role) {
            return $r !== $role;
        }));
    }

    /**
     * 페이지가 공개 접근인지 확인
     */
    public function isPublic(): bool
    {
        return $this->getEffectivePageAccessLevel() === PageAccessLevel::PUBLIC;
    }

    /**
     * 페이지가 커스텀 접근 제어를 사용하는지 확인
     */
    public function hasCustomAccess(): bool
    {
        return $this->getEffectivePageAccessLevel() === PageAccessLevel::CUSTOM;
    }
}
