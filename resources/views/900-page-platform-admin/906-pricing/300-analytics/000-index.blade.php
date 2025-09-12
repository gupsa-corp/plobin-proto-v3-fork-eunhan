<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '요금제 분석'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.906-pricing.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.906-pricing.100-header-main')
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">총 구독자</h3>
                        <p class="text-2xl font-bold text-gray-900">1,234</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">월 수익</h3>
                        <p class="text-2xl font-bold text-green-600">₩12,345,000</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">이탈률</h3>
                        <p class="text-2xl font-bold text-red-600">5.2%</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-sm font-medium text-gray-500">평균 구독 기간</h3>
                        <p class="text-2xl font-bold text-blue-600">8.3개월</p>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">📈 요금제 분석</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">요금제 성과 및 분석 차트가 여기에 표시됩니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>