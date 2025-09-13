<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '역할 관리'])
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
                
                {{-- 역할 관리 콘텐츠 --}}
                <div>
                    @include('900-page-platform-admin.905-permissions.901-tab-roles.100-header')
                    @include('900-page-platform-admin.905-permissions.901-tab-roles.200-content-main')
                </div>
            </div>
        </div>
    </div>
    
    {{-- 기존 모달과 JavaScript는 Livewire 컴포넌트에서 처리하므로 제거 --}}
    {{-- @include('900-page-platform-admin.905-permissions.310-modal-create-role') --}}
    
    {{-- @include('900-page-platform-admin.905-permissions.400-js-permissions') --}}
    
    @livewireScripts
</body>
</html>