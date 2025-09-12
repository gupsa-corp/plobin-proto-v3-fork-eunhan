{{-- 플랫폼 관리자 사이드바 --}}
<nav class="sidebar" style="position: fixed; left: 0; top: 0; width: 240px; height: 100vh; background: #ffffff; border-right: 1px solid #E1E1E4; display: flex; flex-direction: column; z-index: 10; box-sizing: border-box;">
    플랫폼 관리자
    @include('000-common-assets.100-logo')
    {{-- 플랫폼 관리자 정보 --}}
    <div style="padding: 16px; border-bottom: 1px solid #E1E1E4;">
        <div class="text-sm text-gray-600 mb-1">플랫폼 관리자</div>
        <div class="font-medium text-gray-900">{{ auth()->check() ? auth()->user()->name : '게스트 사용자' }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ auth()->check() ? auth()->user()->email : 'guest@example.com' }}</div>
    </div>

    {{-- 네비게이션 메뉴 --}}
    <div style="flex: 1; overflow-y: auto; padding: 8px 0;">
        <ul class="space-y-1">
            {{-- 대시보드 --}}
            <li>
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">대시보드</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.dashboard.overview') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.dashboard*') ? 'bg-blue-50 text-blue-700' : '' }}">📊 개요</a></li>
                    <li><a href="{{ route('platform.admin.dashboard.statistics') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.dashboard.statistics') ? 'bg-blue-50 text-blue-700' : '' }}">📈 통계</a></li>
                    <li><a href="{{ route('platform.admin.dashboard.activities') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.dashboard.activities') ? 'bg-blue-50 text-blue-700' : '' }}">🕒 최근 활동</a></li>
                </ul>
            </li>

            {{-- 조직 관리 --}}
            <li class="mt-4">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">조직 관리</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.organizations.list') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.organizations.list') || request()->routeIs('platform.admin.organizations') ? 'bg-blue-50 text-blue-700' : '' }}">🏢 조직 목록</a></li>
                    <li><a href="{{ route('platform.admin.organizations.points') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.organizations.points*') ? 'bg-blue-50 text-blue-700' : '' }}">⭐ 포인트 관리</a></li>
                </ul>
            </li>

            {{-- 사용자 관리 --}}
            <li class="mt-4">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">사용자 관리</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.users.list') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.users.list') || request()->routeIs('platform.admin.users') ? 'bg-blue-50 text-blue-700' : '' }}">👥 사용자 목록</a></li>
                    <li><a href="{{ route('platform.admin.users.activity-logs') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.users.activity-logs') ? 'bg-blue-50 text-blue-700' : '' }}">📋 활동 로그</a></li>
                    <li><a href="{{ route('platform.admin.users.reports') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.users.reports') ? 'bg-blue-50 text-blue-700' : '' }}">📊 리포트</a></li>
                </ul>
            </li>

            {{-- 결제 관리 --}}
            <li class="mt-4">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">결제 관리</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.payments.history') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.payments.history') || request()->routeIs('platform.admin.payments') ? 'bg-blue-50 text-blue-700' : '' }}">💳 결제 내역</a></li>
                    <li><a href="{{ route('platform.admin.payments.refunds') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.payments.refunds') ? 'bg-blue-50 text-blue-700' : '' }}">🔄 환불 관리</a></li>
                </ul>
            </li>

            {{-- 권한 관리 --}}
            <li class="mt-4">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">권한 관리</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.permissions.overview') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.permissions.overview') || request()->routeIs('platform.admin.permissions') ? 'bg-blue-50 text-blue-700' : '' }}">🔐 권한 개요</a></li>
                    <li><a href="{{ route('platform.admin.permissions.roles') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.permissions.roles') ? 'bg-blue-50 text-blue-700' : '' }}">👑 역할 관리</a></li>
                    <li><a href="{{ route('platform.admin.permissions.users') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.permissions.users') ? 'bg-blue-50 text-blue-700' : '' }}">🔑 사용자 권한</a></li>
                    <li><a href="{{ route('platform.admin.permissions.audit') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.permissions.audit*') ? 'bg-blue-50 text-blue-700' : '' }}">📝 감사 로그</a></li>
                </ul>
            </li>

            {{-- 요금제 관리 --}}
            <li class="mt-4">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">요금제 관리</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.pricing.overview') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.pricing.overview') || request()->routeIs('platform.admin.pricing') ? 'bg-blue-50 text-blue-700' : '' }}">💰 요금제 개요</a></li>
                    <li><a href="{{ route('platform.admin.pricing.plans') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.pricing.plans') ? 'bg-blue-50 text-blue-700' : '' }}">📋 플랜 관리</a></li>
                    <li><a href="{{ route('platform.admin.pricing.subscriptions') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.pricing.subscriptions') ? 'bg-blue-50 text-blue-700' : '' }}">🔄 구독 관리</a></li>
                    <li><a href="{{ route('platform.admin.pricing.analytics') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.pricing.analytics') ? 'bg-blue-50 text-blue-700' : '' }}">📈 분석</a></li>
                </ul>
            </li>

            {{-- 샌드박스 관리 --}}
            <li class="mt-4">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">샌드박스 관리</div>
                <ul class="space-y-1 ml-2">
                    <li><a href="{{ route('platform.admin.sandboxes.list') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.sandboxes.list') || request()->routeIs('platform.admin.sandboxes') ? 'bg-blue-50 text-blue-700' : '' }}">🛠️ 샌드박스 목록</a></li>
                    <li><a href="{{ route('platform.admin.sandboxes.templates') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.sandboxes.templates') ? 'bg-blue-50 text-blue-700' : '' }}">📄 템플릿 관리</a></li>
                    <li><a href="{{ route('platform.admin.sandboxes.usage') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.sandboxes.usage') ? 'bg-blue-50 text-blue-700' : '' }}">📊 사용량 분석</a></li>
                    <li><a href="{{ route('platform.admin.sandboxes.settings') }}" class="flex items-center px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-md {{ request()->routeIs('platform.admin.sandboxes.settings') ? 'bg-blue-50 text-blue-700' : '' }}">⚙️ 설정</a></li>
                </ul>
            </li>
        </ul>
    </div>

    {{-- 하단 메뉴 --}}
    <div style="border-top: 1px solid #E1E1E4; padding: 16px;">
        <a href="{{ route('dashboard') }}"
           class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            서비스로 돌아가기
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
                <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                </svg>
                로그아웃
            </button>
        </form>
    </div>
</nav>
