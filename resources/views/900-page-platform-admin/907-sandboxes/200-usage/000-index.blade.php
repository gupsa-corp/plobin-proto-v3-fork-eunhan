<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '샌드박스 사용량'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.907-sandboxes.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.907-sandboxes.100-header-main')
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">총 샌드박스</h3>
                        <p class="text-2xl font-bold text-gray-900">342</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">활성 샌드박스</h3>
                        <p class="text-2xl font-bold text-green-600">156</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">CPU 사용량</h3>
                        <p class="text-2xl font-bold text-blue-600">72%</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">메모리 사용량</h3>
                        <p class="text-2xl font-bold text-orange-600">68%</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">📊 샌드박스 사용량 분석</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">샌드박스 리소스 사용량 차트가 여기에 표시됩니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>