<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '샌드박스 설정'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.907-sandboxes.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.907-sandboxes.100-header-main')
            <div class="p-6">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">⚙️ 샌드박스 설정</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">리소스 제한</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">CPU 제한 (%)</label>
                                    <input type="number" value="80" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">메모리 제한 (MB)</label>
                                    <input type="number" value="2048" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">보안 설정</h3>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="mr-2">
                                    <span class="text-sm text-gray-700">네트워크 액세스 제한</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="mr-2">
                                    <span class="text-sm text-gray-700">파일 시스템 접근 제한</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                설정 저장
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>