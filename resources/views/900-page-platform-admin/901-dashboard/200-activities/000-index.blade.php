<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '최근 활동'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.901-dashboard.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">🕒 최근 활동</h1>
                
                <div class="bg-white rounded-lg shadow">
                    @if(isset($activities) && count($activities) > 0)
                        <div class="divide-y divide-gray-200">
                            @foreach($activities as $activity)
                                <div class="p-4 flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full bg-{{ $activity['color'] }}-100 flex items-center justify-center">
                                            <span class="text-{{ $activity['color'] }}-600">
                                                @if($activity['icon'] === 'building')
                                                    🏢
                                                @elseif($activity['icon'] === 'credit-card')
                                                    💳
                                                @elseif($activity['icon'] === 'star')
                                                    ⭐
                                                @else
                                                    📋
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $activity['user'] ?? 'System' }} · {{ $activity['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <p class="text-gray-500">최근 활동이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>