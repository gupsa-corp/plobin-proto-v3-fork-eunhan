<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ProjectRole;
use App\Enums\PageAccessLevel;
use App\Services\ProjectChangeLogService;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'progress',
        'organization_id',
        'user_id',
        'default_access_level',
        'project_roles',
        'sandbox_folder',
        'allow_individual_sandbox_per_page',
        'pms_metadata'
    ];

    protected $casts = [
        'project_roles' => 'json',
        'pms_metadata' => 'json',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function ($project) {
            ProjectChangeLogService::logProjectCreated($project->id, $project->name, $project->user_id);
        });

        static::updated(function ($project) {
            if ($project->wasChanged()) {
                $changes = $project->getChanges();
                // timestamps는 제외
                unset($changes['updated_at'], $changes['created_at']);

                if (!empty($changes)) {
                    ProjectChangeLogService::logProjectUpdated($project->id, $changes);
                }
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function rootPages()
    {
        return $this->hasMany(Page::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function projectPages()
    {
        return $this->hasMany(ProjectPage::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function memberRoles()
    {
        return $this->hasMany(ProjectMemberRole::class);
    }

    public function sandboxes()
    {
        return $this->hasMany(ProjectSandbox::class);
    }

    /**
     * 사용자의 프로젝트 내 역할 조회
     */
    public function getUserRole(User $user): ProjectRole
    {
        // 프로젝트 소유자 확인
        if ($this->user_id === $user->id) {
            return ProjectRole::OWNER;
        }

        // 프로젝트별 역할 확인
        $projectRole = $this->memberRoles()->where('user_id', $user->id)->first();
        if ($projectRole) {
            return ProjectRole::from($projectRole->role_name);
        }

        // 조직 멤버십을 통한 기본 역할 확인
        $organizationMember = OrganizationMember::where('organization_id', $this->organization_id)
            ->where('user_id', $user->id)
            ->where('invitation_status', 'accepted')
            ->first();

        if ($organizationMember) {
            return $this->mapOrganizationRoleToProjectRole($organizationMember->role);
        }

        return ProjectRole::GUEST;
    }

    /**
     * 사용자에게 프로젝트 내 역할 할당
     */
    public function setUserRole(User $user, ProjectRole $role): void
    {
        ProjectMemberRole::updateOrCreate(
            [
                'project_id' => $this->id,
                'user_id' => $user->id,
            ],
            [
                'role_name' => $role->value,
            ]
        );
    }

    /**
     * 프로젝트 기본 접근 레벨 조회
     */
    public function getDefaultAccessLevelEnum(): PageAccessLevel
    {
        return PageAccessLevel::from($this->default_access_level ?? 'member');
    }

    /**
     * 사용자가 프로젝트에 접근할 수 있는지 확인
     */
    public function canUserAccess(User $user): bool
    {
        // 프로젝트 소유자는 항상 접근 가능
        if ($this->user_id === $user->id) {
            return true;
        }

        $userRole = $this->getUserRole($user);
        $accessLevel = $this->getDefaultAccessLevelEnum();

        return $accessLevel->canRoleAccess($userRole);
    }

    /**
     * 조직 역할을 프로젝트 역할로 매핑
     */
    private function mapOrganizationRoleToProjectRole(?string $organizationRole): ProjectRole
    {
        if (!$organizationRole) {
            return ProjectRole::GUEST;
        }

        return match($organizationRole) {
            'admin', 'owner' => ProjectRole::ADMIN,
            'moderator' => ProjectRole::MODERATOR,
            'contributor' => ProjectRole::CONTRIBUTOR,
            'member' => ProjectRole::MEMBER,
            default => ProjectRole::GUEST,
        };
    }
}
