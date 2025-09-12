{{-- 플랫폼 관리자 권한 관리 메인 콘텐츠 --}}
<div class="p-6">
    {{-- 탭 네비게이션 --}}
    @include('900-page-platform-admin.905-page-permissions.300-tab-navigation')

    {{-- 역할 관리 탭 --}}
    @include('900-page-platform-admin.905-page-permissions.901-tab-roles.000-index')

    {{-- 권한 설정 탭 --}}
    @include('900-page-platform-admin.905-page-permissions.902-tab-permissions.000-index')

    {{-- 사용자 권한 탭 --}}
    @include('900-page-platform-admin.905-page-permissions.903-tab-users.000-index')

    {{-- 권한 로그 탭 --}}
    @include('900-page-platform-admin.905-page-permissions.904-tab-audit.000-index')
</div>

{{-- 새 역할 생성 모달 --}}
@include('900-page-platform-admin.905-page-permissions.310-modal-create-role')

{{-- JavaScript --}}
@include('900-page-platform-admin.905-page-permissions.400-js-permissions')