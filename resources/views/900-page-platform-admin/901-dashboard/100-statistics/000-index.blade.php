<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '플랫폼 통계'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.901-dashboard.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">📈 플랫폼 통계</h1>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">월별 성장 통계</h2>
                    
                    @if(isset($monthlyStats))
                        <div class="space-y-4">
                            @foreach($monthlyStats as $stat)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                    <span class="font-medium">{{ $stat['label'] }}</span>
                                    <div class="text-sm text-gray-600">
                                        조직: {{ $stat['organizations'] }}개 | 
                                        사용자: {{ $stat['users'] }}명 | 
                                        매출: {{ number_format($stat['revenue']) }}원
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">통계 데이터를 불러오는 중...</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>