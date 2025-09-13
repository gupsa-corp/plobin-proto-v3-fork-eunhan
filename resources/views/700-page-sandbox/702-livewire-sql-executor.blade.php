<!-- DataGrip 스타일 4패널 레이아웃 -->
<div class="flex h-screen bg-gray-100">
    <!-- 왼쪽 패널: 테이블 리스트 -->
    <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">데이터베이스</h3>
            <div class="text-xs text-gray-500">
                storage-sandbox-{{ $selectedSandbox }}
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-2">
            <div class="mb-2">
                <div class="text-xs font-semibold text-gray-600 px-2 py-1">테이블</div>
            </div>
            
            @forelse($tables as $table)
                <button 
                    wire:click="selectTable('{{ $table['name'] }}')"
                    class="w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100 rounded {{ $selectedTable === $table['name'] ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}"
                >
                    <svg class="inline w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                    {{ $table['name'] }}
                </button>
            @empty
                <div class="px-3 py-2 text-xs text-gray-500">테이블이 없습니다</div>
            @endforelse
        </div>
    </div>

    <!-- 가운데 패널: 쿼리 에디터 -->
    <div class="flex-1 flex flex-col">
        <!-- 상단: 쿼리 에디터 -->
        <div class="flex-1 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">쿼리 에디터</h3>
                <div class="flex space-x-2">
                    <button 
                        wire:click="executeSql" 
                        wire:loading.attr="disabled"
                        class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 disabled:opacity-50 flex items-center"
                    >
                        <svg wire:loading.remove class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                        </svg>
                        <span wire:loading.remove>실행</span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            실행 중...
                        </span>
                    </button>
                    <button 
                        wire:click="clearQuery"
                        class="px-3 py-1.5 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400"
                    >
                        초기화
                    </button>
                </div>
            </div>
            
            <div class="flex-1 p-4">
                <textarea 
                    wire:model.live="sqlQuery" 
                    placeholder="SQL 쿼리를 입력하세요..."
                    class="w-full h-32 px-3 py-2 border border-gray-300 rounded font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                    @keydown.ctrl.enter="$wire.executeSql()"
                ></textarea>
                <div class="text-xs text-gray-500 mt-1">
                    Ctrl + Enter로 실행
                </div>
            </div>
        </div>

        <!-- 하단: 실행 결과 -->
        <div class="h-80 bg-white border-t border-gray-200 flex flex-col">
            <div class="p-3 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900">실행 결과</h4>
            </div>
            
            <div class="flex-1 overflow-auto p-4">
                @if($executionResult)
                    @if($executionResult['status'] === 'success')
                        <div class="mb-3 text-xs text-green-600">
                            실행 완료 ({{ $executionResult['execution_time'] }}ms, {{ $executionResult['affected_rows'] }}행)
                        </div>
                        
                        @if(is_array($executionResult['data']) && isset($executionResult['data'][0]) && is_array($executionResult['data'][0]))
                            <!-- 테이블 형식 결과 -->
                            <div class="overflow-auto">
                                <table class="min-w-full text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b">
                                            @foreach(array_keys($executionResult['data'][0]) as $column)
                                                <th class="px-3 py-2 text-left font-medium text-gray-700 border-r">
                                                    {{ $column }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($executionResult['data'] as $row)
                                            <tr class="border-b hover:bg-gray-50">
                                                @foreach($row as $value)
                                                    <td class="px-3 py-2 border-r text-gray-900">
                                                        {{ is_null($value) ? 'NULL' : $value }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-green-800 text-sm">
                                {{ is_array($executionResult['data']) ? ($executionResult['data']['message'] ?? 'SQL 실행 완료') : ($executionResult['data'] ?? 'SQL 실행 완료') }}
                            </div>
                        @endif
                    @else
                        <div class="text-red-600 text-sm">
                            <div class="font-medium mb-2">오류 ({{ $executionResult['execution_time'] }}ms)</div>
                            <div class="font-mono bg-red-50 p-2 rounded">
                                {{ $executionResult['error'] }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-gray-500 text-sm">쿼리를 실행하면 결과가 여기에 표시됩니다</div>
                @endif
            </div>
        </div>
    </div>

    <!-- 오른쪽 패널 -->
    <div class="w-80 flex flex-col">
        <!-- 오른쪽 상단: 실행 쿼리 목록 -->
        <div class="flex-1 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">실행 기록</h3>
            </div>
            
            <div class="flex-1 overflow-y-auto">
                @if($executionHistory && $executionHistory->count() > 0)
                    <div class="space-y-1 p-2">
                        @foreach($executionHistory->take(10) as $execution)
                            <div class="p-2 text-xs border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                                 wire:click="$set('sqlQuery', '{{ addslashes($execution->sql_query) }}')">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="px-2 py-1 text-xs rounded {{ 
                                        $execution->query_type === 'SELECT' ? 'bg-green-100 text-green-800' :
                                        ($execution->query_type === 'INSERT' ? 'bg-blue-100 text-blue-800' :
                                        ($execution->query_type === 'UPDATE' ? 'bg-orange-100 text-orange-800' :
                                        'bg-gray-100 text-gray-800'))
                                    }}">
                                        {{ $execution->query_type }}
                                    </span>
                                    <span class="text-gray-500">{{ $execution->created_at->format('H:i:s') }}</span>
                                </div>
                                <div class="font-mono text-gray-800 truncate">
                                    {{ Str::limit($execution->sql_query, 50) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-sm text-gray-500 text-center">
                        실행 기록이 없습니다
                    </div>
                @endif
            </div>
        </div>

        <!-- 오른쪽 하단: 데이터베이스 연결 정보 -->
        <div class="h-48 bg-white border-r border-gray-200 border-t flex flex-col">
            <div class="p-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">연결 정보</h3>
            </div>
            
            <div class="flex-1 p-3 text-xs space-y-2">
                <div>
                    <span class="font-medium text-gray-700">데이터베이스:</span>
                    <div class="text-gray-600">SQLite</div>
                </div>
                <div>
                    <span class="font-medium text-gray-700">샌드박스:</span>
                    <div class="text-gray-600">storage-sandbox-{{ $selectedSandbox }}</div>
                </div>
                <div>
                    <span class="font-medium text-gray-700">테이블 수:</span>
                    <div class="text-gray-600">{{ count($tables) }}개</div>
                </div>
                @if($selectedTable)
                <div>
                    <span class="font-medium text-gray-700">선택된 테이블:</span>
                    <div class="text-blue-600">{{ $selectedTable }}</div>
                </div>
                @endif
                @if($executionResult)
                <div>
                    <span class="font-medium text-gray-700">마지막 실행:</span>
                    <div class="text-gray-600">
                        {{ $executionResult['status'] === 'success' ? '성공' : '실패' }}
                        ({{ $executionResult['execution_time'] }}ms)
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(session()->has('success'))
    <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
        {{ session('success') }}
    </div>
@endif

@if(session()->has('error'))
    <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
        {{ session('error') }}
    </div>
@endif