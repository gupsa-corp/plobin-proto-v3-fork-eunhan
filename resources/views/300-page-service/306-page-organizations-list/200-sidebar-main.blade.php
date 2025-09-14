@php
    // 조직 목록 페이지용 사이드바 메뉴 - 조직 목록만 표시
    $navItems = [
        [
            'title' => '조직 목록',
            'url' => '/organizations',
            'active' => request()->is('organizations') && !request()->is('organizations/*/'),
            'icon' => '<svg width="20" height="20" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" fill="currentColor"/>
                      </svg>'
        ]
    ];
@endphp

{{-- 조직 목록 페이지 사이드바 --}}
<nav class="sidebar" style="position: fixed; left: 0; top: 0; width: 240px; height: 100vh; background: #ffffff; border-right: 1px solid #E1E1E4; display: flex; flex-direction: column; z-index: 10; box-sizing: border-box;">
    @include('000-common-assets.100-logo')

    @include('300-page-service.300-common.201-sidebar-navigation', ['navItems' => $navItems])
</nav>