<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

// 인증이 필요한 페이지들을 위한 라우트 그룹
Route::group(['middleware' => 'loginRequired.auth'], function () {
    // 대시보드 페이지는 인증 필요
    Route::get('/dashboard', function () {
        // 조직 관련 페이지들에 조직 데이터 전달
        $organizations = \App\Models\Organization::select(['organizations.id', 'organizations.name'])
            ->selectSub(function($query) {
                $query->from('organization_members')
                      ->whereColumn('organization_id', 'organizations.id')
                      ->where('invitation_status', 'accepted')
                      ->selectRaw('count(*)');
            }, 'members_count')
            ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', Auth::id())
            ->where('organization_members.invitation_status', 'accepted')
            ->orderBy('organizations.created_at', 'desc')
            ->get();

        // 1. 내가 소유한 프로젝트들
        $ownedProjects = \App\Models\Project::select(['projects.id', 'projects.name', 'projects.description', 'projects.created_at', 'organizations.name as organization_name', 'organizations.id as organization_id', 'projects.user_id'])
            ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
            ->where('projects.user_id', Auth::id());

        // 2. 조직 멤버십을 통해 접근 가능한 프로젝트들 (내가 소유한 프로젝트 제외)
        $memberProjects = \App\Models\Project::select(['projects.id', 'projects.name', 'projects.description', 'projects.created_at', 'organizations.name as organization_name', 'organizations.id as organization_id', 'projects.user_id'])
            ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
            ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', Auth::id())
            ->where('organization_members.invitation_status', 'accepted')
            ->where('projects.user_id', '!=', Auth::id());

        // 3. 두 결과를 합치기
        $projects = $ownedProjects->union($memberProjects)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 4. 최근 페이지들 조회 (내가 접근 가능한 프로젝트의 페이지들)
        $pages = \App\Models\ProjectPage::select(['project_pages.id', 'project_pages.title', 'project_pages.content', 'project_pages.updated_at', 'projects.name as project_name', 'projects.id as project_id', 'organizations.name as organization_name', 'organizations.id as organization_id'])
            ->join('projects', 'project_pages.project_id', '=', 'projects.id')
            ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
            ->where(function($query) {
                // 내가 소유한 프로젝트의 페이지들
                $query->where('projects.user_id', Auth::id())
                      // 또는 내가 멤버인 조직의 프로젝트 페이지들
                      ->orWhereExists(function($subQuery) {
                          $subQuery->select(\DB::raw(1))
                                   ->from('organization_members')
                                   ->whereColumn('organization_members.organization_id', 'organizations.id')
                                   ->where('organization_members.user_id', Auth::id())
                                   ->where('organization_members.invitation_status', 'accepted');
                      });
            })
            ->orderBy('project_pages.updated_at', 'desc')
            ->limit(4)
            ->get();

        $viewName = config('routes-web./dashboard.view', '300-page-service.301-page-dashboard.000-index');
        return view($viewName, compact('organizations', 'projects', 'pages'));
    })->name('dashboard');

    // 조직 대시보드
    Route::get('/organizations/{id}/dashboard', function ($id) {
        $organizations = \App\Models\Organization::select(['organizations.id', 'organizations.name'])
            ->selectSub(function($query) {
                $query->from('organization_members')
                      ->whereColumn('organization_id', 'organizations.id')
                      ->where('invitation_status', 'accepted')
                      ->selectRaw('count(*)');
            }, 'members_count')
            ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', Auth::id())
            ->where('organization_members.invitation_status', 'accepted')
            ->orderBy('organizations.created_at', 'desc')
            ->get();

        return view('300-page-service.302-page-organization-dashboard.000-index', compact('organizations'));
    })->name('organization.dashboard');

    // 프로젝트 관련 라우트들
    Route::get('/organizations/{id}/projects/{projectId}', function ($id, $projectId) {
        // 조직과 프로젝트 정보 가져오기
        $organization = \App\Models\Organization::find($id);
        $project = \App\Models\Project::find($projectId);

        // 프로젝트 대시보드를 표시 (자동 리다이렉트 제거)
        return view('300-page-service.308-page-project-dashboard.000-index', [
            'currentPageId' => null,
            'organization' => $organization,
            'project' => $project,
            'page' => null
        ]);
    })->name('project.dashboard');

    Route::get('/organizations/{id}/projects/{projectId}/dashboard', function ($id, $projectId) {
        return redirect()->route('project.dashboard', ['id' => $id, 'projectId' => $projectId]);
    })->name('project.dashboard.full');

    // 페이지 생성 라우트
    Route::post('/organizations/{id}/projects/{projectId}/pages/create', [\App\Http\Controllers\Page\Create\Controller::class, '__invoke']);

    // 페이지 제목 업데이트 라우트
    Route::patch('/organizations/{id}/projects/{projectId}/pages/{pageId}/title', [\App\Http\Controllers\Page\UpdateTitle\Controller::class, '__invoke']);

    // 프로젝트 페이지 라우트들
    Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}', [\App\Http\Controllers\ProjectPage\ShowController::class, '__invoke'])->name('project.dashboard.page');

    // 페이지 설정 라우트들
    Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings', function ($id, $projectId, $pageId) {
        return redirect()->route('project.dashboard.page.settings.name', ['id' => $id, 'projectId' => $projectId, 'pageId' => $pageId]);
    })->name('project.dashboard.page.settings');

    Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/name', function ($id, $projectId, $pageId) {
        return view('300-page-service.309-page-settings-name.000-index', ['currentPageId' => $pageId, 'activeTab' => 'name']);
    })->name('project.dashboard.page.settings.name');

    Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/name', function ($id, $projectId, $pageId) {
        return view('300-page-service.309-page-settings-name.000-index', ['currentPageId' => $pageId, 'activeTab' => 'name']);
    })->name('project.dashboard.page.settings.name.post');

    // 프로젝트 설정 라우트들
    Route::get('/organizations/{id}/projects/{projectId}/settings/name', function ($id, $projectId) {
        return view('300-page-service.314-page-project-settings-name.000-index', [
            'currentProjectId' => $projectId,
            'activeTab' => 'name',
            'organizationId' => $id,
            'projectId' => $projectId
        ]);
    })->name('project.dashboard.project.settings.name');

    Route::post('/organizations/{id}/projects/{projectId}/settings/name', function ($id, $projectId) {
        return view('300-page-service.314-page-project-settings-name.000-index', [
            'currentProjectId' => $projectId,
            'activeTab' => 'name',
            'organizationId' => $id,
            'projectId' => $projectId
        ]);
    })->name('project.dashboard.project.settings.name.post');

    // 프로젝트 샌드박스 설정 라우트들
    Route::get('/organizations/{id}/projects/{projectId}/settings/sandbox', function ($id, $projectId) {
        // 프로젝트 정보 가져오기
        $project = \App\Models\Project::where('id', $projectId)
            ->whereHas('organization', function($query) use ($id) {
                $query->where('id', $id);
            })->first();

        $currentSandboxName = $project ? $project->sandbox_folder : null;

        return view('300-page-service.315-page-project-settings-sandbox.000-index', [
            'currentProjectId' => $projectId,
            'activeTab' => 'sandbox',
            'organizationId' => $id,
            'projectId' => $projectId,
            'currentSandboxName' => $currentSandboxName
        ]);
    })->name('project.dashboard.project.settings.sandbox');

    Route::post('/organizations/{id}/projects/{projectId}/settings/sandbox', [\App\Http\Controllers\Project\SetSandbox\Controller::class, '__invoke'])->name('project.dashboard.project.settings.sandbox.post');

    Route::get('/organizations/{id}/projects/{projectId}/settings/users', function ($id, $projectId) {
        // 프로젝트 정보 가져오기
        $project = \App\Models\Project::where('id', $projectId)
            ->whereHas('organization', function($query) use ($id) {
                $query->where('id', $id);
            })->first();

        if (!$project) {
            return redirect()->route('project.dashboard', ['id' => $id, 'projectId' => $projectId])
                ->with('error', '프로젝트를 찾을 수 없습니다.');
        }

        // 조직 멤버들 가져오기
        $organizationMembers = \App\Models\OrganizationMember::where('organization_id', $id)
            ->where('invitation_status', 'accepted')
            ->with('user')
            ->get();

        return view('300-page-service.315-page-project-settings-users.000-index', [
            'currentProjectId' => $projectId,
            'activeTab' => 'users',
            'organizationId' => $id,
            'projectId' => $projectId,
            'project' => $project,
            'organizationMembers' => $organizationMembers
        ]);
    })->name('project.dashboard.project.settings.users');

    Route::get('/organizations/{id}/projects/{projectId}/settings/permissions', function ($id, $projectId) {
        // 프로젝트 정보 가져오기
        $project = \App\Models\Project::where('id', $projectId)
            ->whereHas('organization', function($query) use ($id) {
                $query->where('id', $id);
            })->first();

        if (!$project) {
            return redirect()->route('project.dashboard', ['id' => $id, 'projectId' => $projectId])
                ->with('error', '프로젝트를 찾을 수 없습니다.');
        }

        // 조직 멤버들 가져오기
        $organizationMembers = \App\Models\OrganizationMember::where('organization_id', $id)
            ->where('invitation_status', 'accepted')
            ->with('user')
            ->get();

        return view('300-page-service.316-page-project-settings-permissions.000-index', [
            'currentProjectId' => $projectId,
            'activeTab' => 'permissions',
            'organizationId' => $id,
            'projectId' => $projectId,
            'project' => $project,
            'organizationMembers' => $organizationMembers
        ]);
    })->name('project.dashboard.project.settings.permissions');

    Route::get('/organizations/{id}/projects/{projectId}/settings/delete', function ($id, $projectId) {
        return view('300-page-service.317-page-project-settings-delete.000-index', ['currentProjectId' => $projectId, 'activeTab' => 'delete', 'organizationId' => $id, 'projectId' => $projectId]);
    })->name('project.dashboard.project.settings.delete');

    Route::get('/organizations/{id}/projects/{projectId}/settings/page-delete', function ($id, $projectId) {
        return view('300-page-service.318-page-project-settings-page-delete.000-index', ['currentProjectId' => $projectId, 'activeTab' => 'page-delete', 'organizationId' => $id, 'projectId' => $projectId]);
    })->name('project.dashboard.project.settings.page-delete');

    // 페이지 삭제 API
    Route::delete('/organizations/{id}/projects/{projectId}/pages/{pageId}', [\App\Http\Controllers\Page\Delete\Controller::class, '__invoke'])->name('pages.delete');
});

// 웹 라우트 일괄 등록 (대시보드 제외)
$routes = config('routes-web');

foreach ($routes as $path => $config) {

    // 이미 loginRequired.auth 그룹에서 처리된 라우트들 스킵
    $protectedPaths = [
        '/dashboard',
        '/organizations/{id}/dashboard',
        '/organizations/{id}/projects/{projectId}/dashboard'
    ];

    if (in_array($path, $protectedPaths)) {
        continue;
    }

    // 이전 버전 호환성 지원
    if (is_string($config)) {
        $viewName = $config;
        $routeName = null;
        $redirectTo = null;
    } else {
        $viewName = $config['view'] ?? null;
        $routeName = $config['name'] ?? null;
        $redirectTo = $config['redirect'] ?? null;
        $controller = $config['controller'] ?? null;
    }

    // 리다이렉트 처리
    if ($redirectTo) {
        $route = Route::get($path, function () use ($redirectTo) {
            return redirect($redirectTo);
        });
    } elseif ($controller) {
        // 컨트롤러 처리
        $route = Route::get($path, $controller);
    } else {
        $route = Route::get($path, function () use ($viewName, $path) {
            // /mypage/edit 경로는 특별 처리 - 비밀번호 확인 후 접근
            if ($path === '/mypage/edit') {
                // 세션에 password_verified가 없으면 /mypage로 리다이렉트
                if (!session('password_verified')) {
                    return redirect('/mypage')->with('show_password_modal', true);
                }

                // 비밀번호 확인이 완료된 경우 세션 삭제하고 진행
                session()->forget('password_verified');
            }

            // 조직 관련 페이지들에 조직 데이터 전달
            if (in_array($path, ['/dashboard', '/organizations', '/mypage', '/mypage/edit', '/mypage/delete', '/organizations/create'])) {

                $organizations = \App\Models\Organization::select(['organizations.id', 'organizations.name'])
                    ->selectSub(function($query) {
                        $query->from('organization_members')
                              ->whereColumn('organization_id', 'organizations.id')
                              ->where('invitation_status', 'accepted')
                              ->selectRaw('count(*)');
                    }, 'members_count')
                    ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
                    ->where('organization_members.user_id', Auth::id())
                    ->where('organization_members.invitation_status', 'accepted')
                    ->orderBy('organizations.created_at', 'desc')
                    ->get();

                // 대시보드 페이지에는 프로젝트 목록도 함께 전달
                if ($path === '/dashboard') {
                    // 1. 내가 소유한 프로젝트들
                    $ownedProjects = \App\Models\Project::select(['projects.id', 'projects.name', 'projects.description', 'projects.created_at', 'organizations.name as organization_name', 'organizations.id as organization_id', 'projects.user_id'])
                        ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
                        ->where('projects.user_id', Auth::id());

                    // 2. 조직 멤버십을 통해 접근 가능한 프로젝트들 (내가 소유한 프로젝트 제외)
                    $memberProjects = \App\Models\Project::select(['projects.id', 'projects.name', 'projects.description', 'projects.created_at', 'organizations.name as organization_name', 'organizations.id as organization_id', 'projects.user_id'])
                        ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
                        ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
                        ->where('organization_members.user_id', Auth::id())
                        ->where('organization_members.invitation_status', 'accepted')
                        ->where('projects.user_id', '!=', Auth::id());

                    // 3. 두 결과를 합치기
                    $projects = $ownedProjects->union($memberProjects)
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();

                    // 4. 최근 페이지들 조회 (내가 접근 가능한 프로젝트의 페이지들)
                    $pages = \App\Models\ProjectPage::select(['project_pages.id', 'project_pages.title', 'project_pages.content', 'project_pages.updated_at', 'projects.name as project_name', 'projects.id as project_id', 'organizations.name as organization_name', 'organizations.id as organization_id'])
                        ->join('projects', 'project_pages.project_id', '=', 'projects.id')
                        ->join('organizations', 'projects.organization_id', '=', 'organizations.id')
                        ->where(function($query) {
                            // 내가 소유한 프로젝트의 페이지들
                            $query->where('projects.user_id', Auth::id())
                                  // 또는 내가 멤버인 조직의 프로젝트 페이지들
                                  ->orWhereExists(function($subQuery) {
                                      $subQuery->select(\DB::raw(1))
                                               ->from('organization_members')
                                               ->whereColumn('organization_members.organization_id', 'organizations.id')
                                               ->where('organization_members.user_id', Auth::id())
                                               ->where('organization_members.invitation_status', 'accepted');
                                  });
                        })
                        ->orderBy('project_pages.updated_at', 'desc')
                        ->limit(4)
                        ->get();

                    return view($viewName, compact('organizations', 'projects', 'pages'));
                }

                return view($viewName, compact('organizations'));
            }

            return view($viewName);
        });
    }

    // 라우트명이 있으면 추가
    if ($routeName) {
        $route->name($routeName);
    }
}

// 페이지 설정 관련 라우트들
Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/name', function ($id, $projectId, $pageId) {
    return view('300-page-service.309-page-settings-name.000-index', ['currentPageId' => $pageId, 'activeTab' => 'name']);
})->name('project.dashboard.page.settings.name');

Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/name', function ($id, $projectId, $pageId) {
    return view('300-page-service.309-page-settings-name.000-index', ['currentPageId' => $pageId, 'activeTab' => 'name']);
})->name('project.dashboard.page.settings.name.post');


Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/custom-screen', function ($id, $projectId, $pageId) {
    $page = \App\Models\ProjectPage::where('id', $pageId)->whereHas('project', function($query) use ($projectId, $id) {
        $query->where('id', $projectId)->whereHas('organization', function($q) use ($id) {
            $q->where('id', $id);
        });
    })->first();

    $currentSandboxName = ($page && $page->project) ? $page->project->sandbox_folder : null;
    $currentCustomScreenId = $page ? $page->sandbox_custom_screen_folder : null;

    // 샌드박스 템플릿 서비스를 사용하여 커스텀 화면 데이터 가져오기
    $sandboxTemplateService = app(\App\Services\SandboxTemplateService::class);
    $customScreens = $sandboxTemplateService->getCustomScreens($currentSandboxName ?? '');

    return view('300-page-service.311-page-settings-custom-screen.000-index', [
        'currentPageId' => $pageId,
        'activeTab' => 'custom-screen',
        'page' => $page,
        'currentSandboxName' => $currentSandboxName,
        'currentCustomScreenId' => $currentCustomScreenId,
        'customScreens' => $customScreens
    ]);
})->name('project.dashboard.page.settings.custom-screen');

Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/custom-screen', [\App\Http\Controllers\Page\SetCustomScreen\Controller::class, '__invoke'])->name('project.dashboard.page.settings.custom-screen.post');

Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/deployment', function ($id, $projectId, $pageId) {
    return view('300-page-service.313-page-settings-deployment.000-index', ['currentPageId' => $pageId, 'activeTab' => 'deployment']);
})->name('project.dashboard.page.settings.deployment');

Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/deployment', function ($id, $projectId, $pageId) {
    return view('300-page-service.313-page-settings-deployment.000-index', ['currentPageId' => $pageId, 'activeTab' => 'deployment']);
})->name('project.dashboard.page.settings.deployment.post');

Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/permissions', function ($id, $projectId, $pageId) {
    return view('300-page-service.320-page-settings-permissions.000-index', ['currentPageId' => $pageId, 'activeTab' => 'permissions']);
})->name('project.dashboard.page.settings.permissions');

Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/permissions', function ($id, $projectId, $pageId) {
    return view('300-page-service.320-page-settings-permissions.000-index', ['currentPageId' => $pageId, 'activeTab' => 'permissions']);
})->name('project.dashboard.page.settings.permissions.post');

Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/history', function ($id, $projectId, $pageId) {
    return view('300-page-service.321-page-settings-history.000-index', ['currentPageId' => $pageId, 'activeTab' => 'history']);
})->name('project.dashboard.page.settings.history');

Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/history', function ($id, $projectId, $pageId) {
    return view('300-page-service.321-page-settings-history.000-index', ['currentPageId' => $pageId, 'activeTab' => 'history']);
})->name('project.dashboard.page.settings.history.post');

Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/delete', function ($id, $projectId, $pageId) {
    return view('300-page-service.312-page-settings-delete.000-index', ['currentPageId' => $pageId, 'activeTab' => 'delete']);
})->name('project.dashboard.page.settings.delete');

Route::post('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings/delete', function ($id, $projectId, $pageId) {
    return view('300-page-service.312-page-settings-delete.000-index', ['currentPageId' => $pageId, 'activeTab' => 'delete']);
})->name('project.dashboard.page.settings.delete.post');

// 페이지 설정 기본 라우트 - 기본적으로 페이지 이름 변경 탭으로 리다이렉트
Route::get('/organizations/{id}/projects/{projectId}/pages/{pageId}/settings', function ($id, $projectId, $pageId) {
    return redirect()->route('project.dashboard.page.settings.name', ['id' => $id, 'projectId' => $projectId, 'pageId' => $pageId]);
})->name('project.dashboard.page.settings');

// 프로젝트 설정 라우트들은 loginRequired.auth 그룹으로 이동됨

// 조직 관리자 페이지 라우트들
Route::get('/organizations/{id}/admin/members', [\App\Http\Controllers\Organization\Admin\Members\Controller::class, '__invoke'])->name('organization.admin.members');

// 권한 관리 기본 라우트 - 개요 탭으로 리다이렉트
Route::get('/organizations/{id}/admin/permissions', function ($id) {
    return redirect()->route('organization.admin.permissions.overview', ['id' => $id]);
})->name('organization.admin.permissions');

// 권한 개요 탭
Route::get('/organizations/{id}/admin/permissions/overview', [\App\Http\Controllers\Organization\Admin\Permissions\Overview\Controller::class, '__invoke'])->name('organization.admin.permissions.overview');

// 역할 관리 탭
Route::get('/organizations/{id}/admin/permissions/roles', [\App\Http\Controllers\Organization\Admin\Permissions\Roles\Controller::class, '__invoke'])->name('organization.admin.permissions.roles');

// 권한 관리 탭
Route::get('/organizations/{id}/admin/permissions/management', [\App\Http\Controllers\Organization\Admin\Permissions\Management\Controller::class, '__invoke'])->name('organization.admin.permissions.management');

// 동적 규칙 탭
Route::get('/organizations/{id}/admin/permissions/rules', [\App\Http\Controllers\Organization\Admin\Permissions\Rules\Controller::class, '__invoke'])->name('organization.admin.permissions.rules');

Route::get('/organizations/{organization}/admin/billing', [\App\Http\Billing\PaymentHistory\Controller::class, 'billing'])->name('organization.admin.billing');

// 플랜 계산기
Route::get('/organizations/{organization}/admin/billing/plan-calculator', function ($organization) {
    return view('800-page-organization-admin.803-page-billing.350-plan-calculator', compact('organization'));
})->name('organization.admin.billing.plan-calculator');

// 결제 성공/실패 페이지
Route::get('/organizations/{organization}/admin/billing/payment-success', function ($organization) {
    return view('800-page-organization-admin.803-page-billing.370-payment-success', compact('organization'));
})->name('organization.admin.billing.payment-success');

Route::get('/organizations/{organization}/admin/billing/payment-fail', function ($organization) {
    return view('800-page-organization-admin.803-page-billing.375-payment-fail', compact('organization'));
})->name('organization.admin.billing.payment-fail');

// 결제 내역 관련 라우트들
Route::get('/organizations/{organization}/admin/billing/payment-history', [\App\Http\Billing\PaymentHistory\Controller::class, 'index'])->name('organization.admin.billing.payment-history');
Route::get('/organizations/{organization}/admin/billing/payment-history/{billingHistory}', [\App\Http\Billing\PaymentDetail\Controller::class, 'show'])->name('organization.admin.billing.payment-detail');
Route::get('/organizations/{organization}/admin/billing/payment-history/{billingHistory}/receipt', [\App\Http\Billing\DownloadReceipt\Controller::class, 'download'])->name('organization.admin.billing.download-receipt');
Route::post('/organizations/{organization}/admin/billing/payment-history/{billingHistory}/retry', [\App\Http\Billing\RetryPayment\Controller::class, 'retry'])->name('organization.admin.billing.retry-payment');
Route::get('/organizations/{organization}/admin/billing/export', [\App\Http\Billing\ExportHistory\Controller::class, 'export'])->name('organization.admin.billing.export');

// AJAX 엔드포인트 (동일한 컨트롤러, AJAX 요청 처리)
Route::post('/organizations/{organization}/admin/billing/payment-history', [\App\Http\Billing\PaymentHistory\Controller::class, 'index'])->name('organization.admin.billing.payment-history.ajax');

Route::get('/organizations/{id}/admin/projects', [\App\Http\Controllers\Organization\Admin\Projects\Controller::class, '__invoke'])->name('organization.admin.projects');

// 조직 설정 - 사용자 관리
Route::get('/organizations/{id}/settings/users', [\App\Http\Controllers\Organization\Settings\Users\Controller::class, '__invoke'])->name('organization.settings.users');

// 플랫폼 관리자 라우트들 (platform_admin 권한 필요) - 개발용으로 일시적으로 인증 제거
// 추후 배포시 ->middleware(['auth', 'role:platform_admin']) 적용 예정

// ========== 대시보드 ==========
Route::get('/platform/admin', [\App\Http\Controllers\PlatformAdmin\DashboardController::class, 'statistics'])->name('platform.admin.dashboard');
Route::get('/platform/admin/dashboard', [\App\Http\Controllers\PlatformAdmin\DashboardController::class, 'statistics'])->name('platform.admin.dashboard.full');
Route::get('/platform/admin/dashboard/statistics', [\App\Http\Controllers\PlatformAdmin\DashboardController::class, 'statistics'])->name('platform.admin.dashboard.statistics');
Route::get('/platform/admin/dashboard/recent-activities', [\App\Http\Controllers\PlatformAdmin\DashboardController::class, 'recentActivities'])->name('platform.admin.dashboard.activities');

// ========== 조직 관리 ==========
Route::get('/platform/admin/organizations', [\App\Http\Controllers\PlatformAdmin\OrganizationsController::class, 'list'])->name('platform.admin.organizations');
Route::get('/platform/admin/organizations/list', [\App\Http\Controllers\PlatformAdmin\OrganizationsController::class, 'list'])->name('platform.admin.organizations.list');
Route::get('/platform/admin/organizations/details/{organization}', [\App\Http\Controllers\PlatformAdmin\OrganizationsController::class, 'details'])->name('platform.admin.organizations.details');
Route::get('/platform/admin/organizations/points', [\App\Http\Controllers\PlatformAdmin\OrganizationsController::class, 'points'])->name('platform.admin.organizations.points');
Route::get('/platform/admin/organizations/points/{organization}', [\App\Http\Controllers\PlatformAdmin\OrganizationsController::class, 'pointsDetail'])->name('platform.admin.organizations.points.detail');
Route::post('/platform/admin/organizations/points/{organization}/adjust', [\App\Http\Controllers\PlatformAdmin\OrganizationsController::class, 'adjustPoints'])->name('platform.admin.organizations.points.adjust');

// ========== 사용자 관리 ==========
Route::get('/platform/admin/users', [\App\Http\Controllers\PlatformAdmin\UsersController::class, 'list'])->name('platform.admin.users');
Route::get('/platform/admin/users/list', [\App\Http\Controllers\PlatformAdmin\UsersController::class, 'list'])->name('platform.admin.users.list');
Route::get('/platform/admin/users/details/{user}', [\App\Http\Controllers\PlatformAdmin\UsersController::class, 'details'])->name('platform.admin.users.details');
Route::get('/platform/admin/users/activity-logs', [\App\Http\Controllers\PlatformAdmin\UsersController::class, 'activityLogs'])->name('platform.admin.users.activity-logs');
Route::get('/platform/admin/users/reports', [\App\Http\Controllers\PlatformAdmin\UsersController::class, 'reports'])->name('platform.admin.users.reports');

// ========== 결제 관리 ==========
Route::get('/platform/admin/payments', [\App\Http\Controllers\PlatformAdmin\PaymentsController::class, 'list'])->name('platform.admin.payments');
Route::get('/platform/admin/payments/history', [\App\Http\Controllers\PlatformAdmin\PaymentsController::class, 'history'])->name('platform.admin.payments.history');
Route::get('/platform/admin/payments/details/{billingHistory}', [\App\Http\Controllers\PlatformAdmin\PaymentsController::class, 'details'])->name('platform.admin.payments.details');
Route::get('/platform/admin/payments/refunds', [\App\Http\Controllers\PlatformAdmin\PaymentsController::class, 'refunds'])->name('platform.admin.payments.refunds');
Route::post('/platform/admin/payments/{billingHistory}/cancel', [\App\Http\Controllers\PlatformAdmin\PaymentsController::class, 'cancel'])->name('platform.admin.payments.cancel');
Route::post('/platform/admin/payments/{billingHistory}/refund', [\App\Http\Controllers\PlatformAdmin\PaymentsController::class, 'refund'])->name('platform.admin.payments.refund');

// ========== 권한 관리 ==========
Route::get('/platform/admin/permissions', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'overview'])->name('platform.admin.permissions');
Route::get('/platform/admin/permissions/overview', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'overview'])->name('platform.admin.permissions.overview');
Route::get('/platform/admin/permissions/roles', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'roles'])->name('platform.admin.permissions.roles');
Route::get('/platform/admin/permissions/permissions', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'permissions'])->name('platform.admin.permissions.permissions');
Route::get('/platform/admin/permissions/users', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'users'])->name('platform.admin.permissions.users');
Route::get('/platform/admin/permissions/audit', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'audit'])->name('platform.admin.permissions.audit');
Route::get('/platform/admin/permissions/audit/details/{id}', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'auditDetails'])->name('platform.admin.permissions.audit.details');

// ========== 권한 관리 API ==========
Route::post('/platform/admin/permissions/users/change-role', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'changeUserRole'])->name('platform.admin.permissions.users.change-role');
Route::post('/platform/admin/permissions/users/toggle-status', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'toggleUserStatus'])->name('platform.admin.permissions.users.toggle-status');
Route::post('/platform/admin/permissions/users/update-tenant-permissions', [\App\Http\Controllers\PlatformAdmin\PermissionsController::class, 'updateTenantPermissions'])->name('platform.admin.permissions.users.update-tenant-permissions');

// ========== 요금제 관리 ==========
Route::get('/platform/admin/pricing', [\App\Http\Controllers\PlatformAdmin\PricingController::class, 'overview'])->name('platform.admin.pricing');
Route::get('/platform/admin/pricing/overview', [\App\Http\Controllers\PlatformAdmin\PricingController::class, 'overview'])->name('platform.admin.pricing.overview');
Route::get('/platform/admin/pricing/plans', [\App\Http\Controllers\PlatformAdmin\PricingController::class, 'plans'])->name('platform.admin.pricing.plans');
Route::get('/platform/admin/pricing/subscriptions', [\App\Http\Controllers\PlatformAdmin\PricingController::class, 'subscriptions'])->name('platform.admin.pricing.subscriptions');
Route::get('/platform/admin/pricing/analytics', [\App\Http\Controllers\PlatformAdmin\PricingController::class, 'analytics'])->name('platform.admin.pricing.analytics');

// ========== 샌드박스 관리 ==========
Route::get('/platform/admin/sandboxes', [\App\Http\Controllers\PlatformAdmin\SandboxesController::class, 'list'])->name('platform.admin.sandboxes');
Route::get('/platform/admin/sandboxes/list', [\App\Http\Controllers\PlatformAdmin\SandboxesController::class, 'list'])->name('platform.admin.sandboxes.list');
Route::get('/platform/admin/sandboxes/templates', [\App\Http\Controllers\PlatformAdmin\SandboxesController::class, 'templates'])->name('platform.admin.sandboxes.templates');
Route::get('/platform/admin/sandboxes/usage', [\App\Http\Controllers\PlatformAdmin\SandboxesController::class, 'usage'])->name('platform.admin.sandboxes.usage');
Route::get('/platform/admin/sandboxes/settings', [\App\Http\Controllers\PlatformAdmin\SandboxesController::class, 'settings'])->name('platform.admin.sandboxes.settings');

// 샌드박스 템플릿 백엔드 API 라우트 (CSRF 보호 제외)
Route::any('/sandbox/{sandboxName}/backend/api.php/{path?}', function ($sandboxName, $path = '') {
    $apiFile = storage_path("sandbox/{$sandboxName}/backend/api.php");
    
    if (!file_exists($apiFile)) {
        return response()->json(['success' => false, 'message' => 'API 파일을 찾을 수 없습니다.'], 404);
    }
    
    // 원래 URI를 API 파일에 맞게 설정
    $_SERVER['REQUEST_URI'] = '/backend/api.php/' . $path;
    
    // 출력 버퍼링 시작
    ob_start();
    
    // API 파일 실행
    $result = include $apiFile;
    
    // 출력 내용 캡처
    $output = ob_get_clean();
    
    // 결과가 배열이면 JSON으로 반환
    if (is_array($result)) {
        return response()->json($result);
    }
    
    // 출력이 있으면 그대로 반환 (이미 JSON 헤더가 설정되어 있을 것)
    if (!empty($output)) {
        return response($output)->header('Content-Type', 'application/json');
    }
    
    return response()->json(['success' => false, 'message' => '응답이 없습니다.'], 500);
})->where('path', '.*');

// 샌드박스 페이지들 - 실제 존재하는 파일들만 라우트 등록
// 메인 인덱스
Route::get('/sandbox', function () {
    return view('700-page-sandbox.000-index');
})->name('sandbox.index');

// 대시보드
Route::get('/sandbox/dashboard', function () {
    return view('700-page-sandbox.701-page-dashboard.000-index');
})->name('sandbox.dashboard');

// SQL 실행기
Route::get('/sandbox/sql-executor', function () {
    return view('700-page-sandbox.702-page-sql-executor.000-index');
})->name('sandbox.sql-executor');


// 파일 에디터
Route::get('/sandbox/file-editor', function () {
    return view('700-page-sandbox.704-page-file-editor.000-index');
})->name('sandbox.file-editor');

// 데이터베이스 매니저
Route::get('/sandbox/database-manager', function () {
    return view('700-page-sandbox.705-page-database-manager.000-index');
})->name('sandbox.database-manager');

// Git 버전 관리
Route::get('/sandbox/git-version-control', function () {
    return view('700-page-sandbox.706-page-git-version-control.000-index');
})->name('sandbox.git-version-control');

// 스토리지 관리자 - config에서 정의한 라우트를 오버라이드
Route::get('/sandbox/storage-manager', [App\Http\Controllers\Sandbox\StorageManager\Controller::class, 'index'])->name('sandbox.storage-manager');
Route::post('/sandbox/storage-manager/create', [App\Http\Controllers\Sandbox\StorageManager\Controller::class, 'create'])->name('sandbox.storage.create');
Route::post('/sandbox/storage-manager/select', [App\Http\Controllers\Sandbox\StorageManager\Controller::class, 'select'])->name('sandbox.storage.select');
Route::delete('/sandbox/storage-manager/delete', [App\Http\Controllers\Sandbox\StorageManager\Controller::class, 'delete'])->name('sandbox.storage.delete');


// Form Creator
Route::get('/sandbox/form-creator', function () {
    return view('700-page-sandbox.709-page-form-creator.000-index');
})->name('sandbox.form-creator');

// Function Browser
Route::get('/sandbox/function-browser', function () {
    return view('700-page-sandbox.708-page-function-browser.000-index');
})->name('sandbox.function-browser');

// Scenario Manager
Route::get('/sandbox/scenario-manager', function () {
    return view('700-page-sandbox.711-page-scenario-manager.000-index');
})->name('sandbox.scenario-manager');

// Documentation Manager
Route::get('/sandbox/documentation-manager', function () {
    return view('700-page-sandbox.710-page-documentation-manager.000-index');
})->name('sandbox.documentation-manager');

// Custom Screens
Route::get('/sandbox/custom-screens', function () {
    return view('700-page-sandbox.706-page-custom-screens.000-index');
})->name('sandbox.custom-screens');


// 실시간 템플릿 화면들 - 직접 경로
Route::get('/sandbox/live-dashboard', function () {
    return view('700-page-sandbox.706-page-custom-screens.100-live-dashboard');
})->name('sandbox.live-dashboard');

Route::get('/sandbox/live-project-list', function () {
    return view('700-page-sandbox.706-page-custom-screens.101-live-project-list');
})->name('sandbox.live-project-list');

Route::get('/sandbox/live-table-view', function () {
    return view('700-page-sandbox.706-page-custom-screens.102-live-table-view');
})->name('sandbox.live-table-view');

Route::get('/sandbox/live-kanban-board', function () {
    return view('700-page-sandbox.706-page-custom-screens.103-live-kanban-board');
})->name('sandbox.live-kanban-board');

Route::get('/sandbox/live-gantt-chart', function () {
    return view('700-page-sandbox.706-page-custom-screens.104-live-gantt-chart');
})->name('sandbox.live-gantt-chart');

Route::get('/sandbox/live-calendar-view', function () {
    return view('700-page-sandbox.706-page-custom-screens.105-live-calendar-view');
})->name('sandbox.live-calendar-view');

// Custom Screen Creator
Route::get('/sandbox/custom-screen-creator', function () {
    return view('700-page-sandbox.707-page-custom-screen-creator.000-index');
})->name('sandbox.custom-screen-creator');

// Organizations List (Generated Custom Screen)
Route::get('/sandbox/organizations-list', function () {
    return view('700-page-sandbox.713-page-organizations-list.000-index');
})->name('sandbox.organizations-list');
// 화면 (Generated Custom Screen)
Route::get('/sandbox/custom-screen-1757421612', function () {
    return view('700-page-sandbox.717-page-custom-screen-1757421612.000-index');
})->name('sandbox.custom-screen-1757421612');
// 프로젝트 목록 (Generated Custom Screen)
Route::get('/sandbox/projects-list', function () {
    return view('700-page-sandbox.715-page-projects-list.000-index');
})->name('sandbox.projects-list');

// 샌드박스 사용 프로젝트 목록
Route::get('/sandbox/using-projects', [\App\Http\Controllers\Sandbox\UsingProjectsController::class, 'index'])->name('sandbox.using-projects');

// 자료 다운로드 페이지
Route::get('/sandbox/downloads', function () {
    return view('700-page-sandbox.718-page-downloads.000-index');
})->name('sandbox.downloads');

// 자료 다운로드 API
Route::get('/sandbox/downloads/file/{filename}', [\App\Http\Sandbox\Downloads\Controller::class, 'download'])->name('sandbox.downloads.file');
Route::get('/sandbox/downloads/stats', [\App\Http\Sandbox\Downloads\Controller::class, 'getStats'])->name('sandbox.downloads.stats');



// Form Publisher - 샌드박스 폼 생성 및 관리 도구 (Livewire + Filament)
Route::prefix('sandbox/form-publisher')->group(function () {
    Route::get('/', function () {
        return view('700-page-sandbox.700-form-publisher.000-index');
    })->name('sandbox.form-publisher.list');

    Route::get('/editor', function () {
        return view('700-page-sandbox.700-form-publisher.100-editor');
    })->name('sandbox.form-publisher.editor');

    Route::post('/editor', function () {
        return view('700-page-sandbox.900-form-publisher-gateway', ['page' => 'editor']);
    })->name('sandbox.form-publisher.editor.post');

    Route::get('/preview/{id}', function ($id) {
        return view('700-page-sandbox.700-form-publisher.200-preview', compact('id'));
    })->name('sandbox.form-publisher.preview');

    Route::post('/preview/{id}', function ($id) {
        return view('700-page-sandbox.900-form-publisher-gateway', ['page' => 'preview', 'id' => $id]);
    })->name('sandbox.form-publisher.preview.post');

    Route::get('/list', function () {
        return view('700-page-sandbox.900-form-publisher-gateway', ['page' => 'list']);
    })->name('sandbox.form-publisher.list.full');

    Route::post('/list', function () {
        return view('700-page-sandbox.900-form-publisher-gateway', ['page' => 'list']);
    })->name('sandbox.form-publisher.list.post');
});

// 로그아웃 라우트 추가
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// 회원 탈퇴 라우트
Route::middleware(['auth'])->group(function () {
    Route::get('/mypage/delete', [\App\Http\UserAccount\Delete\Controller::class, 'show'])->name('mypage.delete');
    Route::post('/mypage/delete', [\App\Http\UserAccount\Delete\Controller::class, 'destroy'])->name('mypage.delete.process');
    Route::get('/api/user/organization-status', [\App\Http\UserAccount\Delete\Controller::class, 'checkOrganizationStatus'])->name('user.organization-status');
});
