<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => 'Cron 관리 - 플랫폼 관리자'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.900-common.902-sidebar-navigation')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.907-sandboxes.400-cron.100-header-main')
            @include('900-page-platform-admin.907-sandboxes.400-cron.200-content-main')
        </div>
    </div>
    @livewireScripts
</body>
</html>