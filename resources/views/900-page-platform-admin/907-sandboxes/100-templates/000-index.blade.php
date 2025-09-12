<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '샌드박스 템플릿'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.907-sandboxes.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.907-sandboxes.100-header-main')
            <div class="p-6">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">🎨 샌드박스 템플릿 관리</h2>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                새 템플릿 추가
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">React 기본 템플릿</h3>
                                <p class="text-sm text-gray-600 mb-4">React 18 + TypeScript 기본 구성</p>
                                <div class="flex space-x-2">
                                    <button class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded">편집</button>
                                    <button class="px-3 py-1 text-xs bg-gray-100 text-gray-800 rounded">복제</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>