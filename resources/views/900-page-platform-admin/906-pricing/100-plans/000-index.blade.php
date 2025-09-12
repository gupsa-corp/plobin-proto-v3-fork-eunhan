<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '요금제 관리'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.906-pricing.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.906-pricing.100-header-main')
            <div class="p-6">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">💰 요금제 관리</h2>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                새 요금제 추가
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- 기본 요금제 카드들이 여기에 표시됩니다 -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">베이직 플랜</h3>
                                <p class="text-3xl font-bold text-gray-900 mb-4">₩29,900<span class="text-sm text-gray-500">/월</span></p>
                                <ul class="text-sm text-gray-600 space-y-2 mb-4">
                                    <li>• 기본 기능 제공</li>
                                    <li>• 사용자 10명까지</li>
                                    <li>• 이메일 지원</li>
                                </ul>
                                <button class="w-full px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50">
                                    수정
                                </button>
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