<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '사용자 활동 로그'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.900-common.902-sidebar-navigation')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            <div class="p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">🚀 사용자 활동 로그 - Test Page</h1>
                    <p class="mt-2 text-sm text-gray-600">사용자들의 활동을 추적하고 모니터링합니다.</p>
                </div>
                
                <div class="bg-white shadow rounded-lg p-6">
                    <p class="text-center text-gray-500 py-8">활동 로그 데이터가 여기에 표시됩니다.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>