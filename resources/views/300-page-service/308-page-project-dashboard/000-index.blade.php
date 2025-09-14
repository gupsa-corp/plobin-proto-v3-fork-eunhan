@include('000-common-layouts.001-html-lang')
@include('300-page-service.300-common.301-layout-head', ['title' => '프로젝트 대시보드'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('300-page-service.308-page-project-dashboard.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('300-page-service.308-page-project-dashboard.101-unified-header', [
                'organization' => $organization,
                'project' => $project,
                'page' => $page,
                'sandboxInfo' => $sandboxInfo,
                'customScreen' => $customScreen
            ])
            @include('300-page-service.308-page-project-dashboard.200-content-main', [
                'organization' => $organization,
                'project' => $project,
                'page' => $page,
                'sandboxInfo' => $sandboxInfo,
                'customScreen' => $customScreen,
                'customScreens' => $customScreens
            ])
        </div>

    </div>

    <!-- JavaScript -->
    @include('300-page-service.300-common.303-layout-js-imports')
</body>
</html>
