<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'scope_level',
        'organization_id', 
        'project_id',
        'page_id',
        'parent_role_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 역할의 범위 레벨 상수
     */
    const SCOPE_PLATFORM = 'platform';
    const SCOPE_ORGANIZATION = 'organization';
    const SCOPE_PROJECT = 'project';
    const SCOPE_PAGE = 'page';

    /**
     * 조직과의 관계
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * 프로젝트와의 관계 (ProjectPage 모델이 있다면)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectPage::class, 'project_id');
    }

    /**
     * 페이지와의 관계 (Page 모델이 있다면)
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(ProjectPage::class, 'page_id');
    }

    /**
     * 부모 역할과의 관계
     */
    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    /**
     * 자식 역할들과의 관계
     */
    public function childRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    /**
     * 역할 생성자와의 관계
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 플랫폼 레벨 역할인지 확인
     */
    public function isPlatformLevel(): bool
    {
        return $this->scope_level === self::SCOPE_PLATFORM;
    }

    /**
     * 조직 레벨 역할인지 확인
     */
    public function isOrganizationLevel(): bool
    {
        return $this->scope_level === self::SCOPE_ORGANIZATION;
    }

    /**
     * 프로젝트 레벨 역할인지 확인
     */
    public function isProjectLevel(): bool
    {
        return $this->scope_level === self::SCOPE_PROJECT;
    }

    /**
     * 페이지 레벨 역할인지 확인
     */
    public function isPageLevel(): bool
    {
        return $this->scope_level === self::SCOPE_PAGE;
    }

    /**
     * 특정 컨텍스트에서 사용 가능한 역할들을 조회
     */
    public static function getAvailableRoles(
        string $scopeLevel = null,
        int $organizationId = null,
        int $projectId = null,
        int $pageId = null
    ) {
        $query = static::where('is_active', true);

        if ($scopeLevel) {
            $query->where('scope_level', $scopeLevel);
        }

        switch ($scopeLevel) {
            case self::SCOPE_PLATFORM:
                // 플랫폼 레벨은 모든 플랫폼 역할
                $query->whereNull('organization_id');
                break;
                
            case self::SCOPE_ORGANIZATION:
                // 조직 레벨은 해당 조직의 역할 + 플랫폼 역할
                $query->where(function ($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId)
                      ->orWhere('scope_level', self::SCOPE_PLATFORM);
                });
                break;
                
            case self::SCOPE_PROJECT:
                // 프로젝트 레벨은 해당 프로젝트 + 조직 + 플랫폼 역할
                $query->where(function ($q) use ($organizationId, $projectId) {
                    $q->where('project_id', $projectId)
                      ->orWhere(function ($qq) use ($organizationId) {
                          $qq->where('organization_id', $organizationId)
                             ->where('scope_level', self::SCOPE_ORGANIZATION);
                      })
                      ->orWhere('scope_level', self::SCOPE_PLATFORM);
                });
                break;
                
            case self::SCOPE_PAGE:
                // 페이지 레벨은 해당 페이지 + 프로젝트 + 조직 + 플랫폼 역할
                $query->where(function ($q) use ($organizationId, $projectId, $pageId) {
                    $q->where('page_id', $pageId)
                      ->orWhere(function ($qq) use ($projectId) {
                          $qq->where('project_id', $projectId)
                             ->where('scope_level', self::SCOPE_PROJECT);
                      })
                      ->orWhere(function ($qq) use ($organizationId) {
                          $qq->where('organization_id', $organizationId)
                             ->where('scope_level', self::SCOPE_ORGANIZATION);
                      })
                      ->orWhere('scope_level', self::SCOPE_PLATFORM);
                });
                break;
        }

        return $query->with(['parentRole', 'creator', 'organization'])->get();
    }

    /**
     * 역할의 전체 계층 경로를 반환
     */
    public function getHierarchyPath(): string
    {
        $path = [];
        
        switch ($this->scope_level) {
            case self::SCOPE_PLATFORM:
                $path[] = '플랫폼';
                break;
            case self::SCOPE_ORGANIZATION:
                $path[] = '플랫폼';
                $path[] = $this->organization?->name ?? 'Unknown Organization';
                break;
            case self::SCOPE_PROJECT:
                $path[] = '플랫폼';
                $path[] = $this->organization?->name ?? 'Unknown Organization';
                $path[] = $this->project?->title ?? 'Unknown Project';
                break;
            case self::SCOPE_PAGE:
                $path[] = '플랫폼';
                $path[] = $this->organization?->name ?? 'Unknown Organization';
                $path[] = $this->project?->title ?? 'Unknown Project';
                $path[] = $this->page?->title ?? 'Unknown Page';
                break;
        }

        return implode(' > ', $path);
    }

    /**
     * 역할 상속 체인을 반환
     */
    public function getInheritanceChain(): array
    {
        $chain = [];
        $current = $this;

        while ($current) {
            $chain[] = $current;
            $current = $current->parentRole;
        }

        return array_reverse($chain);
    }
}