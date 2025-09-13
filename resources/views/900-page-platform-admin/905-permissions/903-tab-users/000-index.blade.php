<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '사용자 권한'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.900-common.902-sidebar-navigation')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            <div class="p-6">
                {{-- 페이지 헤더 --}}
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">권한 관리</h1>
                    <p class="mt-2 text-sm text-gray-600">플랫폼 권한과 역할을 관리합니다.</p>
                </div>
                
                {{-- 탭 네비게이션 --}}
                @include('900-page-platform-admin.905-permissions.300-tab-navigation')
                
                {{-- 사용자 권한 콘텐츠 --}}
                <div>
                    @include('900-page-platform-admin.905-permissions.903-tab-users.100-header')
                    @include('900-page-platform-admin.905-permissions.903-tab-users.200-content-main')
                </div>
                
                {{-- 모달들 --}}
                @include('900-page-platform-admin.905-permissions.903-tab-users.400-role-change-modal')
                @include('900-page-platform-admin.905-permissions.903-tab-users.401-tenant-permission-modal')
            </div>
        </div>
    </div>
    
    {{-- JavaScript --}}
    @include('900-page-platform-admin.905-permissions.400-js-permissions')
    
    @livewireScripts
</body>
</html>