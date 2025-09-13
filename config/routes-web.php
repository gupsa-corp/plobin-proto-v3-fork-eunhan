<?php

return [
    // 경로 => [뷰, 라우트명(선택사항)]
    '/' => ['view' => '100-page-landing.101-page-landing-home.000-index'],
    '/login' => ['view' => '200-page-auth.201-page-auth-login.000-index', 'name' => 'login'],
    '/signup' => ['view' => '200-page-auth.202-page-auth-signup.000-index', 'name' => 'register'],
    '/forgot-password' => ['view' => '200-page-auth.203-page-auth-forgot-password.000-index', 'name' => 'password.request'],
    '/reset-password' => ['view' => '200-page-auth.204-page-auth-reset-password.000-index', 'name' => 'password.reset'],
    '/dashboard' => ['view' => '300-page-service.301-page-dashboard.000-index', 'name' => 'dashboard'],
    '/organizations' => ['view' => '300-page-service.306-page-organizations-list.000-index', 'name' => 'organizations.index'],
    '/organizations/create' => ['view' => '300-page-service.306-page-organizations-list.000-index', 'name' => 'organizations.create'],
    '/mypage' => ['view' => '300-page-service.303-page-mypage-profile.000-index', 'name' => 'profile'],
    '/mypage/edit' => ['view' => '300-page-service.304-page-mypage-edit.000-index', 'name' => 'profile.edit'],
    '/mypage/permissions' => ['view' => '300-page-service.306-page-mypage-permissions.000-index', 'name' => 'profile.permissions'],
    '/mypage/delete' => ['view' => '300-page-service.305-page-mypage-delete.000-index', 'name' => 'account.delete'],
    '/organizations/{id}/dashboard' => ['view' => '300-page-service.302-page-organization-dashboard.000-index', 'name' => 'organization.dashboard'],
    '/organizations/{id}/projects' => ['view' => '300-page-service.307-page-organization-projects.000-index', 'name' => 'organization.projects'],
    // '/organizations/{id}/projects/{projectId}' => ['view' => '300-page-service.308-page-project-dashboard.000-index', 'name' => 'project.dashboard'], // 보호된 라우트에서 처리
    '/organizations/{id}/projects/{projectId}/dashboard' => ['view' => '300-page-service.308-page-project-dashboard.000-index', 'name' => 'project.dashboard.full'],
    // 플랫폼 관리자 라우트들 (platform/admin/{페이지별 경로}) - 새 구조
    '/platform/admin' => ['view' => '900-page-platform-admin.901-dashboard.100-statistics.000-index', 'name' => 'platform.admin.dashboard'],
    '/platform/admin/dashboard' => ['view' => '900-page-platform-admin.901-dashboard.100-statistics.000-index', 'name' => 'platform.admin.dashboard.full'],
    '/platform/admin/dashboard/statistics' => ['view' => '900-page-platform-admin.901-dashboard.100-statistics.000-index', 'name' => 'platform.admin.dashboard.statistics'],
    '/platform/admin/dashboard/recent-activities' => ['view' => '900-page-platform-admin.901-dashboard.200-activities.000-index', 'name' => 'platform.admin.dashboard.activities'],
    
    // 조직 관리
    '/platform/admin/organizations' => ['view' => '900-page-platform-admin.902-organizations.000-list.000-index', 'name' => 'platform.admin.organizations'],
    '/platform/admin/organizations/list' => ['view' => '900-page-platform-admin.902-organizations.000-list.000-index', 'name' => 'platform.admin.organizations.list'],
    '/platform/admin/organizations/details/{organization}' => ['view' => '900-page-platform-admin.902-organizations.100-details.000-index', 'name' => 'platform.admin.organizations.details'],
    '/platform/admin/organizations/points' => ['view' => '900-page-platform-admin.902-organizations.200-points.000-index', 'name' => 'platform.admin.organizations.points'],
    '/platform/admin/organizations/points/{organization}' => ['view' => '900-page-platform-admin.902-organizations.200-points.000-index', 'name' => 'platform.admin.organizations.points.detail'],
    
    // 사용자 관리
    '/platform/admin/users' => ['view' => '900-page-platform-admin.903-users.000-list.000-index', 'name' => 'platform.admin.users'],
    '/platform/admin/users/list' => ['view' => '900-page-platform-admin.903-users.000-list.000-index', 'name' => 'platform.admin.users.list'],
    '/platform/admin/users/details/{user}' => ['view' => '900-page-platform-admin.903-users.100-details.000-index', 'name' => 'platform.admin.users.details'],
    '/platform/admin/users/activity-logs' => ['view' => '900-page-platform-admin.903-users.200-activity-logs.000-index', 'name' => 'platform.admin.users.activity-logs'],
    '/platform/admin/users/reports' => ['view' => '900-page-platform-admin.903-users.300-reports.000-index', 'name' => 'platform.admin.users.reports'],
    
    // 결제 관리
    '/platform/admin/payments' => ['view' => '900-page-platform-admin.904-payments.000-history.000-index', 'name' => 'platform.admin.payments'],
    '/platform/admin/payments/history' => ['view' => '900-page-platform-admin.904-payments.000-history.000-index', 'name' => 'platform.admin.payments.history'],
    '/platform/admin/payments/details/{billingHistory}' => ['view' => '900-page-platform-admin.904-payments.100-details.000-index', 'name' => 'platform.admin.payments.details'],
    '/platform/admin/payments/refunds' => ['view' => '900-page-platform-admin.904-payments.200-refunds.000-index', 'name' => 'platform.admin.payments.refunds'],
    
    // 권한 관리
    '/platform/admin/permissions' => ['view' => '900-page-platform-admin.905-permissions.000-overview.000-index', 'name' => 'platform.admin.permissions'],
    '/platform/admin/permissions/overview' => ['view' => '900-page-platform-admin.905-permissions.000-overview.000-index', 'name' => 'platform.admin.permissions.overview'],
    '/platform/admin/permissions/roles' => ['view' => '900-page-platform-admin.905-permissions.100-roles.000-index', 'name' => 'platform.admin.permissions.roles'],
    '/platform/admin/permissions/permissions' => ['view' => '900-page-platform-admin.905-permissions.200-permissions.000-index', 'name' => 'platform.admin.permissions.permissions'],
    '/platform/admin/permissions/users' => ['view' => '900-page-platform-admin.905-permissions.300-users.000-index', 'name' => 'platform.admin.permissions.users'],
    '/platform/admin/permissions/audit' => ['view' => '900-page-platform-admin.905-permissions.400-audit.000-index', 'name' => 'platform.admin.permissions.audit'],
    '/platform/admin/permissions/audit/details/{id}' => ['view' => '900-page-platform-admin.905-permissions.400-audit.100-details.000-index', 'name' => 'platform.admin.permissions.audit.details'],
    
    // 요금제 관리
    '/platform/admin/pricing' => ['view' => '900-page-platform-admin.906-pricing.000-overview.000-index', 'name' => 'platform.admin.pricing'],
    '/platform/admin/pricing/overview' => ['view' => '900-page-platform-admin.906-pricing.000-overview.000-index', 'name' => 'platform.admin.pricing.overview'],
    '/platform/admin/pricing/plans' => ['view' => '900-page-platform-admin.906-pricing.100-plans.000-index', 'name' => 'platform.admin.pricing.plans'],
    '/platform/admin/pricing/subscriptions' => ['view' => '900-page-platform-admin.906-pricing.200-subscriptions.000-index', 'name' => 'platform.admin.pricing.subscriptions'],
    '/platform/admin/pricing/analytics' => ['view' => '900-page-platform-admin.906-pricing.300-analytics.000-index', 'name' => 'platform.admin.pricing.analytics'],
    
    // 샌드박스 관리
    '/platform/admin/sandboxes' => ['view' => '900-page-platform-admin.907-sandboxes.000-list.000-index', 'name' => 'platform.admin.sandboxes'],
    '/platform/admin/sandboxes/list' => ['view' => '900-page-platform-admin.907-sandboxes.000-list.000-index', 'name' => 'platform.admin.sandboxes.list'],
    '/platform/admin/sandboxes/templates' => ['view' => '900-page-platform-admin.907-sandboxes.100-templates.000-index', 'name' => 'platform.admin.sandboxes.templates'],
    '/platform/admin/sandboxes/usage' => ['view' => '900-page-platform-admin.907-sandboxes.200-usage.000-index', 'name' => 'platform.admin.sandboxes.usage'],
    '/platform/admin/sandboxes/settings' => ['view' => '900-page-platform-admin.907-sandboxes.300-settings.000-index', 'name' => 'platform.admin.sandboxes.settings'],
    '/organizations/{id}/admin' => ['view' => '800-page-organization-admin.800-common.000-index', 'name' => 'organization.admin'],
    '/organizations/{id}/admin/members' => ['view' => '800-page-organization-admin.801-page-members.000-index', 'name' => 'organization.admin.members'],
    '/organizations/{id}/admin/permissions' => ['view' => '800-page-organization-admin.805-page-permissions-overview.000-index', 'name' => 'organization.admin.permissions'],
    '/organizations/{id}/admin/permissions/overview' => ['view' => '800-page-organization-admin.805-page-permissions-overview.000-index', 'name' => 'organization.admin.permissions.overview'],
    '/organizations/{id}/admin/permissions/roles' => ['view' => '800-page-organization-admin.806-page-permissions-roles.000-index', 'name' => 'organization.admin.permissions.roles'],
    '/organizations/{id}/admin/permissions/management' => ['view' => '800-page-organization-admin.807-page-permissions-management.000-index', 'name' => 'organization.admin.permissions.management'],
    '/organizations/{id}/admin/permissions/rules' => ['view' => '800-page-organization-admin.808-page-permissions-rules.000-index', 'name' => 'organization.admin.permissions.rules'],
    '/organizations/{id}/admin/projects' => ['view' => '800-page-organization-admin.804-page-projects.000-index', 'name' => 'organization.admin.projects'],
    // 샌드박스 라우트들
    '/sandbox' => ['view' => '700-page-sandbox.000-index', 'name' => 'sandbox.index'],
    '/sandbox/dashboard' => ['view' => '700-page-sandbox.701-page-dashboard.000-index', 'name' => 'sandbox.dashboard'],
    '/sandbox/sql-executor' => ['view' => '700-page-sandbox.702-page-sql-executor.000-index', 'name' => 'sandbox.sql-executor'],
    '/sandbox/file-editor' => ['view' => '700-page-sandbox.704-page-file-editor.000-index', 'name' => 'sandbox.file-editor'],
    '/sandbox/database-manager' => ['view' => '700-page-sandbox.705-page-database-manager.000-index', 'name' => 'sandbox.database-manager'],
    '/sandbox/git-version-control' => ['view' => '700-page-sandbox.705-page-git-version-control.000-index', 'name' => 'sandbox.git-version-control'],
    '/sandbox/api-list' => ['view' => '700-page-sandbox.704-page-api-list.000-index', 'name' => 'sandbox.api-list'],
    '/sandbox/blade-creator' => ['view' => '700-page-sandbox.705-page-blade-creator.000-index', 'name' => 'sandbox.blade-creator'],
    '/sandbox/custom-screens' => ['view' => '700-page-sandbox.706-page-custom-screens.000-index', 'name' => 'sandbox.custom-screens'],
    '/sandbox/custom-screen-creator' => ['view' => '700-page-sandbox.707-page-custom-screen-creator.000-index', 'name' => 'sandbox.custom-screen-creator'],
    '/sandbox/storage-manager' => ['view' => '700-page-sandbox.707-page-storage-manager.000-index', 'name' => 'sandbox.storage-manager'],
    '/sandbox/file-editor-integrated' => ['view' => '700-page-sandbox.708-page-file-editor-integrated.000-index', 'name' => 'sandbox.file-editor-integrated'],
    '/sandbox/form-creator' => ['view' => '700-page-sandbox.709-page-form-creator.000-index', 'name' => 'sandbox.form-creator'],
    '/sandbox/form-publisher' => ['view' => '700-page-sandbox.900-form-publisher-gateway', 'name' => 'sandbox.form-publisher'],
    '/sandbox/form-publisher/editor' => ['view' => '700-page-sandbox.900-form-publisher-gateway', 'name' => 'sandbox.form-publisher.editor'],
    '/sandbox/form-publisher/list' => ['view' => '700-page-sandbox.900-form-publisher-gateway', 'name' => 'sandbox.form-publisher.list'],
    '/sandbox/form-publisher/preview/{id}' => ['view' => '700-page-sandbox.900-form-publisher-gateway', 'name' => 'sandbox.form-publisher.preview'],
    // Function Browser 라우트들
    '/sandbox/function-browser' => ['view' => '700-page-sandbox.708-page-function-browser-main.000-index', 'name' => 'sandbox.function-browser'],
    '/sandbox/function-creator' => ['view' => '700-page-sandbox.709-page-function-creator.000-index', 'name' => 'sandbox.function-creator'],
    '/sandbox/function-dependencies' => ['view' => '700-page-sandbox.710-page-function-dependencies.000-index', 'name' => 'sandbox.function-dependencies'],
    '/sandbox/function-automation' => ['view' => '700-page-sandbox.711-page-function-automation.000-index', 'name' => 'sandbox.function-automation'],
    '/sandbox/function-templates' => ['view' => '700-page-sandbox.712-page-function-templates.000-index', 'name' => 'sandbox.function-templates'],
    '/sandbox/scenario-manager' => ['view' => '700-page-sandbox.711-page-scenario-manager.000-index', 'name' => 'sandbox.scenario-manager'],
    '/sandbox/organizations-list' => ['view' => '700-page-sandbox.713-page-organizations-list.000-index', 'name' => 'sandbox.organizations-list'],
    '/sandbox/custom-screen/preview/{id}' => ['view' => '700-page-sandbox.714-page-custom-screen-preview.000-index', 'name' => 'sandbox.custom-screen-preview'],
    '/sandbox/custom-screen/raw/{id}' => ['controller' => 'App\Http\Controllers\Sandbox\CustomScreen\RawController@show', 'name' => 'sandbox.custom-screen-raw'],
    '/sandbox/{storage_name}/{screen_folder_name}' => ['controller' => 'App\Http\Controllers\Sandbox\CustomScreen\RawController@showByPath', 'name' => 'sandbox.custom-screen-by-path'],
    '/sandbox/using-projects' => ['view' => '700-page-sandbox.716-page-using-projects.000-index', 'name' => 'sandbox.using-projects'],
    '/sandbox/projects-list' => ['view' => '700-page-sandbox.715-page-projects-list.000-index', 'name' => 'sandbox.projects-list'],

    '/sandbox/custom-screen-1757421612' => ['view' => '700-page-sandbox.717-page-custom-screen-1757421612.000-index', 'name' => 'sandbox.custom-screen-1757421612'],
];
