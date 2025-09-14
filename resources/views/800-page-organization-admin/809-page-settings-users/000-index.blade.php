<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include($common . '.301-layout-head', ['title' => '조직 설정'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('800-page-organization-admin.800-common.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('800-page-organization-admin.800-common.100-header-main')

            <!-- 페이지 헤더 -->
            <div class="bg-white shadow">
                <div class="px-8 py-6">
                    <h1 class="text-2xl font-semibold text-gray-900">조직 설정</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        조직의 기본 설정을 관리합니다.
                    </p>
                </div>
            </div>

            <!-- 메인 콘텐츠 -->
            <div class="p-8">
                <!-- 멤버 목록 -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">조직 멤버 (0명)</h3>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <div class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">구현필요</p>
                                <p class="text-sm text-gray-400">조직 설정 기능이 구현되어야 합니다</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>