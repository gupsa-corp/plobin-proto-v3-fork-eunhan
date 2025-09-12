{{-- 플랫폼 관리자 권한 관리 탭 네비게이션 --}}
<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-8">
        <a href="{{ route('platform.admin.permissions.roles') }}" 
           class="{{ request()->routeIs('platform.admin.permissions.roles') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            역할 관리
        </a>
        <a href="{{ route('platform.admin.permissions.permissions') }}" 
           class="{{ request()->routeIs('platform.admin.permissions.permissions') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            권한 설정
        </a>
        <a href="{{ route('platform.admin.permissions.users') }}" 
           class="{{ request()->routeIs('platform.admin.permissions.users') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            사용자 권한
        </a>
        <a href="{{ route('platform.admin.permissions.audit') }}" 
           class="{{ request()->routeIs('platform.admin.permissions.audit') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            권한 로그
        </a>
    </nav>
</div>