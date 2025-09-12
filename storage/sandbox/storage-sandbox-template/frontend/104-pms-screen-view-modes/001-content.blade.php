{{-- 샌드박스 간트 차트 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?><div class="min-h-screen bg-gray-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <span class="text-orange-600">📈</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">간트 차트</h1>
                    <p class="text-gray-600">프로젝트 일정과 진행률을 시각적으로 관리하세요</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button class="px-3 py-1 text-sm bg-white shadow-sm rounded-md">월</button>
                    <button class="px-3 py-1 text-sm text-gray-600">분기</button>
                    <button class="px-3 py-1 text-sm text-gray-600">년</button>
                </div>
                <button class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">내보내기</button>
            </div>
        </div>
    </div>

    {{-- 시간 네비게이션 --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex items-center justify-between">
            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded">←</button>
            <h3 class="text-lg font-semibold text-gray-900">{{ now()->format('Y년 m월') }}</h3>
            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded">→</button>
        </div>
    </div>

    {{-- 간트 차트 --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            {{-- 날짜 헤더 --}}
            <div class="flex border-b">
                <div class="w-64 p-4 bg-gray-50 border-r font-semibold text-gray-900">프로젝트</div>
                <div class="flex-1 flex bg-gray-50">
                    @for($day = 1; $day <= 30; $day++)
                        <div class="w-8 p-2 text-center border-r border-gray-200">
                            <div class="text-xs text-gray-600">{{ $day }}</div>
                            <div class="text-xs text-gray-400">{{ ['월','화','수','목','금','토','일'][($day-1)%7] }}</div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- 프로젝트 행들 --}}
            @for($i = 1; $i <= 8; $i++)
                @php
                    $startDay = rand(1, 10);
                    $duration = rand(5, 15);
                    $endDay = min($startDay + $duration, 30);
                    $progress = rand(30, 95);
                    $colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500', 'bg-red-500'];
                    $color = $colors[array_rand($colors)];
                @endphp
                <div class="flex border-b hover:bg-gray-50">
                    <div class="w-64 p-4 border-r">
                        <div class="font-medium text-gray-900">프로젝트 {{ $i }}</div>
                        <div class="text-sm text-gray-500">담당자{{ $i }}</div>
                        <div class="text-xs text-gray-400 mt-1">진행률: {{ $progress }}%</div>
                    </div>
                    <div class="flex-1 relative flex items-center" style="height: 60px;">
                        {{-- 간트 바 --}}
                        <div class="absolute inset-y-0 flex items-center" 
                             style="left: {{ ($startDay - 1) * 32 }}px; width: {{ $duration * 32 }}px;">
                            <div class="w-full h-6 {{ $color }} rounded-lg relative overflow-hidden">
                                <div class="h-full bg-black bg-opacity-20 rounded-lg" 
                                     style="width: {{ $progress }}%"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-white text-xs font-medium">{{ $progress }}%</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- 날짜 구분선들 --}}
                        @for($day = 1; $day <= 30; $day++)
                            <div class="absolute inset-y-0 border-r border-gray-100" 
                                 style="left: {{ $day * 32 }}px;"></div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>
    </div>

    {{-- 범례 --}}
    <div class="mt-6 bg-white rounded-lg shadow-sm p-4">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">범례</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                <span class="text-sm text-gray-600">개발</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-sm text-gray-600">디자인</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-purple-500 rounded"></div>
                <span class="text-sm text-gray-600">기획</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                <span class="text-sm text-gray-600">테스트</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-sm text-gray-600">배포</span>
            </div>
        </div>
    </div>

    {{-- 통계 --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">전체 프로젝트</div>
            <div class="text-2xl font-bold text-gray-900">8</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">순조진행</div>
            <div class="text-2xl font-bold text-green-600">5</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">지연</div>
            <div class="text-2xl font-bold text-red-600">2</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">완료</div>
            <div class="text-2xl font-bold text-blue-600">1</div>
        </div>
    </div>
</div>