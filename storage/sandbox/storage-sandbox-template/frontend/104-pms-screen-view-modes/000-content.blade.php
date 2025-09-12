{{-- 샌드박스 칸반 보드 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?><div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-purple-600">📋</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">칸반 보드</h1>
                    <p class="text-gray-600">프로젝트 진행 상황을 시각적으로 관리하세요</p>
                </div>
            </div>
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">새 카드 추가</button>
        </div>
    </div>

    {{-- 칸반 보드 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @php
            $columns = [
                ['title' => '할 일', 'color' => 'blue', 'count' => 5],
                ['title' => '진행 중', 'color' => 'yellow', 'count' => 3],
                ['title' => '검토', 'color' => 'purple', 'count' => 2],
                ['title' => '완료', 'color' => 'green', 'count' => 8]
            ];
        @endphp

        @foreach($columns as $column)
            <div class="bg-gray-100 rounded-lg p-4 min-h-96">
                {{-- 칼럼 헤더 --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full 
                            @if($column['color'] === 'blue') bg-blue-400
                            @elseif($column['color'] === 'yellow') bg-yellow-400
                            @elseif($column['color'] === 'purple') bg-purple-400
                            @elseif($column['color'] === 'green') bg-green-400
                            @endif"></div>
                        <h3 class="font-semibold text-gray-900">{{ $column['title'] }}</h3>
                    </div>
                    <span class="bg-gray-200 text-gray-600 text-sm px-2 py-1 rounded-full">{{ $column['count'] }}</span>
                </div>

                {{-- 카드들 --}}
                <div class="space-y-3">
                    @for($i = 1; $i <= $column['count']; $i++)
                        @php 
                            $priorities = ['높음', '보통', '낮음'];
                            $priority = $priorities[array_rand($priorities)];
                            $priorityColors = [
                                '높음' => 'bg-red-100 text-red-600',
                                '보통' => 'bg-yellow-100 text-yellow-600',
                                '낮음' => 'bg-green-100 text-green-600'
                            ];
                        @endphp
                        <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-move">
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-900">
                                    {{ $column['title'] }} 작업 {{ $i }}
                                </h4>
                                <span class="text-xs px-2 py-1 rounded-full {{ $priorityColors[$priority] }}">
                                    {{ $priority }}
                                </span>
                            </div>
                            
                            <p class="text-xs text-gray-600 mb-3">
                                이 작업은 {{ $column['title'] }} 상태의 {{ $i }}번째 작업입니다.
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1">
                                    <div class="w-5 h-5 bg-gray-300 rounded-full"></div>
                                    <span class="text-xs text-gray-500">담당자{{ $i }}</span>
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ now()->addDays(rand(1, 7))->format('m/d') }}
                                </div>
                            </div>
                            
                            @if(rand(0, 1))
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex -space-x-1">
                                            @for($j = 1; $j <= rand(2, 4); $j++)
                                                <div class="w-5 h-5 bg-gray-300 rounded-full border-2 border-white"></div>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500">협업자 {{ rand(2, 4) }}명</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endfor
                    
                    {{-- 새 카드 추가 버튼 --}}
                    <button class="w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-400 hover:border-gray-400 hover:text-gray-600 text-sm">
                        + 새 카드 추가
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 안내 메시지 --}}
    <div class="mt-8 bg-white rounded-lg p-4 border border-blue-200">
        <div class="flex items-center space-x-2 text-blue-700">
            <span>💡</span>
            <span class="text-sm">카드를 드래그하여 다른 칼럼으로 이동할 수 있습니다.</span>
        </div>
    </div>
</div>