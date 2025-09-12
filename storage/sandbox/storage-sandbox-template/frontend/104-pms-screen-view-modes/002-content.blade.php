{{-- 샌드박스 달력 뷰 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?><div class="min-h-screen bg-gradient-to-br from-indigo-50 to-cyan-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <span class="text-indigo-600">📅</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">프로젝트 달력</h1>
                    <p class="text-gray-600">일정과 마일스톤을 달력 형태로 관리하세요</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button class="px-3 py-1 text-sm bg-white shadow-sm rounded-md">월</button>
                    <button class="px-3 py-1 text-sm text-gray-600">주</button>
                    <button class="px-3 py-1 text-sm text-gray-600">일</button>
                </div>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">새 이벤트</button>
            </div>
        </div>
    </div>

    {{-- 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">이번 달 일정</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ rand(15, 25) }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <span class="text-indigo-600">📅</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">오늘 일정</p>
                    <p class="text-2xl font-bold text-green-600">{{ rand(2, 8) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-green-600">⏰</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">마일스톤</p>
                    <p class="text-2xl font-bold text-purple-600">{{ rand(3, 7) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-purple-600">🎯</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">지연 일정</p>
                    <p class="text-2xl font-bold text-red-600">{{ rand(0, 3) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <span class="text-red-600">⚠️</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 달력 네비게이션 --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between">
            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded">←</button>
            <h3 class="text-lg font-semibold text-gray-900">{{ now()->format('Y년 m월') }}</h3>
            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded">→</button>
        </div>
    </div>

    {{-- 달력 --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        {{-- 요일 헤더 --}}
        <div class="grid grid-cols-7 bg-gray-50 border-b">
            @foreach(['일', '월', '화', '수', '목', '금', '토'] as $day)
                <div class="p-4 text-center font-semibold text-gray-700">{{ $day }}</div>
            @endforeach
        </div>

        {{-- 달력 날짜들 --}}
        @php
            $today = now();
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();
            $startOfCalendar = $startOfMonth->copy()->startOfWeek();
            $endOfCalendar = $endOfMonth->copy()->endOfWeek();
            $calendarDays = [];
            
            for ($date = $startOfCalendar->copy(); $date->lte($endOfCalendar); $date->addDay()) {
                $calendarDays[] = $date->copy();
            }
        @endphp

        <div class="grid grid-cols-7">
            @foreach($calendarDays as $date)
                @php
                    $isCurrentMonth = $date->month === $today->month;
                    $isToday = $date->isToday();
                    $hasEvents = rand(0, 3); // 랜덤하게 이벤트 생성
                @endphp
                <div class="min-h-24 p-2 border-r border-b border-gray-100 
                           {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : '' }}
                           {{ $isToday ? 'bg-blue-50' : '' }}">
                    
                    {{-- 날짜 --}}
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm {{ $isToday ? 'font-bold text-blue-600' : '' }}">
                            {{ $date->format('j') }}
                        </span>
                        @if($isToday)
                            <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                        @endif
                    </div>

                    {{-- 이벤트들 --}}
                    @if($hasEvents > 0 && $isCurrentMonth)
                        <div class="space-y-1">
                            @for($i = 1; $i <= min($hasEvents, 2); $i++)
                                @php
                                    $eventTypes = [
                                        ['color' => 'bg-blue-100 text-blue-700', 'icon' => '📋', 'title' => '회의'],
                                        ['color' => 'bg-green-100 text-green-700', 'icon' => '🚀', 'title' => '출시'],
                                        ['color' => 'bg-purple-100 text-purple-700', 'icon' => '🎯', 'title' => '마일스톤'],
                                        ['color' => 'bg-yellow-100 text-yellow-700', 'icon' => '📝', 'title' => '리뷰']
                                    ];
                                    $eventType = $eventTypes[array_rand($eventTypes)];
                                @endphp
                                <div class="text-xs p-1 rounded {{ $eventType['color'] }} truncate">
                                    {{ $eventType['icon'] }} {{ $eventType['title'] }} {{ $i }}
                                </div>
                            @endfor
                            @if($hasEvents > 2)
                                <div class="text-xs text-gray-500 text-center">
                                    +{{ $hasEvents - 2 }}개 더
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- 오늘의 일정 --}}
    <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">오늘의 일정 ({{ now()->format('m월 d일') }})</h3>
        <div class="space-y-3">
            @for($i = 1; $i <= 4; $i++)
                @php
                    $times = ['09:00', '11:30', '14:00', '16:30'];
                    $events = [
                        ['title' => '팀 미팅', 'type' => 'meeting', 'color' => 'blue'],
                        ['title' => '프로젝트 리뷰', 'type' => 'review', 'color' => 'purple'],
                        ['title' => '클라이언트 발표', 'type' => 'presentation', 'color' => 'green'],
                        ['title' => '코드 검토', 'type' => 'code', 'color' => 'orange']
                    ];
                    $event = $events[$i-1];
                @endphp
                <div class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg">
                    <div class="w-12 h-12 bg-{{ $event['color'] }}-100 rounded-lg flex items-center justify-center">
                        <span class="text-{{ $event['color'] }}-600">
                            @if($event['type'] === 'meeting') 👥
                            @elseif($event['type'] === 'review') 📋
                            @elseif($event['type'] === 'presentation') 🎯
                            @elseif($event['type'] === 'code') 💻
                            @endif
                        </span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">{{ $event['title'] }}</h4>
                        <p class="text-sm text-gray-600">{{ $times[$i-1] }} - {{ $times[$i-1] ? date('H:i', strtotime($times[$i-1]) + 3600) : '' }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm text-{{ $event['color'] }}-600 hover:bg-{{ $event['color'] }}-50 rounded">참석</button>
                        <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 rounded">편집</button>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- 범례 --}}
    <div class="mt-6 bg-white rounded-lg shadow-sm p-4">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">이벤트 유형</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-100 border border-blue-200 rounded"></div>
                <span class="text-sm text-gray-600">📋 회의</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-100 border border-green-200 rounded"></div>
                <span class="text-sm text-gray-600">🚀 출시</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-purple-100 border border-purple-200 rounded"></div>
                <span class="text-sm text-gray-600">🎯 마일스톤</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-200 rounded"></div>
                <span class="text-sm text-gray-600">📝 리뷰</span>
            </div>
        </div>
    </div>
</div>