<!-- 우측 사이드바 -->
<div x-show="sidebarOpen"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 z-40"
     @click="closeSidebar()"
     style="display: none;"></div>

<div x-show="sidebarOpen"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="translate-x-full"
     class="fixed inset-y-0 right-0 z-50 w-96 bg-white shadow-xl"
     style="display: none;">
    <div class="h-full flex flex-col" x-show="selectedProject">
        <!-- 사이드바 헤더 -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">프로젝트 편집</h3>
                <button @click="closeSidebar()"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- 사이드바 콘텐츠 -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
<?php 
$currentSandbox = basename(dirname(dirname(dirname(__DIR__))));
include storage_path("sandbox/{$currentSandbox}/100-domain-pms/103-screen-table-view/305-sidebar-form.blade.php"); 
?>
        </div>

        <!-- 사이드바 푸터 -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex space-x-3">
                <button @click="updateProject()"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    저장
                </button>
                <button @click="closeSidebar()"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:ring-2 focus:ring-gray-500">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>
