<div class="space-y-6" wire:key="gantt-chart-{{ time() }}-{{ $viewMode }}">
    <!-- 헤더 통계 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 text-sm">📊</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">전체 프로젝트</p>
                    <p class="text-lg font-semibold text-blue-600">{{ $stats['total_projects'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-sm">✅</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">순조진행</p>
                    <p class="text-lg font-semibold text-green-600">{{ $stats['on_track'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 text-sm">⚠️</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">지연</p>
                    <p class="text-lg font-semibold text-red-600">{{ $stats['delayed'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-600 text-sm">🎯</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">완료</p>
                    <p class="text-lg font-semibold text-purple-600">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 컨트롤 헤더 -->
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900">간트 차트</h3>
                <div class="flex items-center space-x-2">
                    <button wire:click="previousMonth" 
                            class="px-2 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                        ←
                    </button>
                    <span class="text-sm font-medium text-gray-900" wire:key="header-{{ $currentYear }}-{{ $currentMonth }}-{{ $viewMode }}">
                        @if($viewMode === 'month')
                            {{ $currentYear }}년 {{ $currentMonth }}월
                        @elseif($viewMode === 'quarter')
                            {{ $currentYear }}년 {{ ceil($currentMonth/3) }}분기
                        @else
                            {{ $currentYear }}년
                        @endif
                    </span>
                    <button wire:click="nextMonth"
                            class="px-2 py-1 text-sm bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                        →
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button wire:click="setViewMode('month')"
                            class="px-3 py-1 text-xs rounded {{ $viewMode === 'month' ? 'bg-blue-600 text-white' : 'text-gray-600' }}">
                        월
                    </button>
                    <button wire:click="setViewMode('quarter')"
                            class="px-3 py-1 text-xs rounded {{ $viewMode === 'quarter' ? 'bg-blue-600 text-white' : 'text-gray-600' }}">
                        분기
                    </button>
                    <button wire:click="setViewMode('year')"
                            class="px-3 py-1 text-xs rounded {{ $viewMode === 'year' ? 'bg-blue-600 text-white' : 'text-gray-600' }}">
                        년
                    </button>
                </div>
                <button wire:click="refreshData" 
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    내보내기
                </button>
            </div>
        </div>
    </div>

    <!-- 필터 및 검색 -->
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-4">
                <!-- 검색 -->
                <div class="relative">
                    <input wire:model.live="searchTerm" 
                           type="text" 
                           placeholder="프로젝트 검색..." 
                           class="pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- 상태 필터 -->
                <select wire:model.live="statusFilter" 
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">모든 상태</option>
                    <option value="active">활성</option>
                    <option value="in_progress">진행중</option>
                    <option value="completed">완료</option>
                    <option value="blocked">차단됨</option>
                </select>

                <!-- 필터 초기화 -->
                @if($searchTerm || $statusFilter)
                    <button wire:click="clearFilters" 
                            class="px-3 py-2 text-xs bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200">
                        필터 초기화
                    </button>
                @endif
            </div>

            <!-- 액션 버튼들 -->
            <div class="flex items-center space-x-2">
                <button wire:click="openProjectModal" 
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500">
                    + 프로젝트 추가
                </button>
                <button class="px-4 py-2 text-sm bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:ring-2 focus:ring-gray-500">
                    📊 내보내기
                </button>
            </div>
        </div>

        <!-- 활성 필터 표시 -->
        @if($searchTerm || $statusFilter)
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span>활성 필터:</span>
                    @if($searchTerm)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                            검색: "{{ $searchTerm }}"
                        </span>
                    @endif
                    @if($statusFilter)
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">
                            상태: {{ $statusFilter }}
                        </span>
                    @endif
                    <span class="text-gray-500">({{ count($projects) }}개 결과)</span>
                </div>
            </div>
        @endif
    </div>

    <!-- 간트 차트 -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <!-- 헤더 (날짜) -->
            <!-- Debug: 현재 viewMode = {{ $viewMode }} -->
            <div class="flex border-b border-gray-200 bg-gray-50" wire:key="header-container-{{ $viewMode }}-{{ uniqid() }}">
                <div class="w-64 p-3 text-sm font-medium text-gray-900 border-r border-gray-200">
                    프로젝트 ({{ $viewMode }})
                </div>
                
                <!-- 월 뷰: 일별 표시 -->
                <div class="flex-1 flex {{ $viewMode === 'month' ? '' : 'hidden' }}" wire:key="month-view">
                    @if(isset($monthDays) && count($monthDays) > 0)
                        @foreach($monthDays as $day)
                            <div class="flex-1 min-w-8 p-1 text-center border-r border-gray-200">
                                <div class="text-xs text-gray-500">{{ $day->format('j') }}</div>
                                <div class="text-xs text-gray-400">{{ $day->format('D') }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="flex-1 p-3 text-center text-gray-500">월별 데이터 로딩 중...</div>
                    @endif
                </div>
                
                <!-- 분기 뷰: 12주 표시 -->
                <div class="flex-1 flex {{ $viewMode === 'quarter' ? '' : 'hidden' }}" wire:key="quarter-view">
                    @for($i = 1; $i <= 12; $i++)
                        <div class="flex-1 min-w-12 p-1 text-center border-r border-gray-200 bg-blue-50">
                            <div class="text-xs text-blue-600 font-medium">{{ $i }}주</div>
                            <div class="text-xs text-blue-400">Q{{ ceil($currentMonth/3) }}</div>
                        </div>
                    @endfor
                </div>
                
                <!-- 년 뷰: 12개월 표시 -->
                <div class="flex-1 flex {{ $viewMode === 'year' ? '' : 'hidden' }}" wire:key="year-view">
                    @for($month = 1; $month <= 12; $month++)
                        <div class="flex-1 min-w-12 p-1 text-center border-r border-gray-200 bg-green-50">
                            <div class="text-xs text-green-600 font-medium">{{ $month }}월</div>
                            <div class="text-xs text-green-400">{{ $currentYear }}</div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- 프로젝트 행들 -->
            @foreach($projects as $project)
                @php
                    $startDate = \Carbon\Carbon::parse($project['start_date']);
                    $endDate = \Carbon\Carbon::parse($project['end_date']);
                    
                    if ($viewMode === 'month') {
                        $currentPeriodStart = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
                        $currentPeriodEnd = $currentPeriodStart->copy()->endOfMonth();
                        
                        $displayStart = $startDate->gte($currentPeriodStart) ? $startDate : $currentPeriodStart;
                        $displayEnd = $endDate->lte($currentPeriodEnd) ? $endDate : $currentPeriodEnd;
                        
                        $startDay = max(1, $displayStart->day);
                        $endDay = min($currentPeriodEnd->day, $displayEnd->day);
                        $duration = $endDay - $startDay + 1;
                    } elseif ($viewMode === 'quarter') {
                        $quarterStartMonth = (ceil($currentMonth / 3) - 1) * 3 + 1;
                        $currentPeriodStart = \Carbon\Carbon::create($currentYear, $quarterStartMonth, 1);
                        $currentPeriodEnd = $currentPeriodStart->copy()->addMonths(2)->endOfMonth();
                        
                        $displayStart = $startDate->gte($currentPeriodStart) ? $startDate : $currentPeriodStart;
                        $displayEnd = $endDate->lte($currentPeriodEnd) ? $endDate : $currentPeriodEnd;
                        
                        $startWeek = $displayStart->weekOfYear;
                        $endWeek = $displayEnd->weekOfYear;
                        $currentWeek = $currentPeriodStart->weekOfYear;
                        
                        $startDay = max(1, $startWeek - $currentWeek + 1);
                        $endDay = min(13, $endWeek - $currentWeek + 1);
                        $duration = $endDay - $startDay + 1;
                    } else {
                        $currentPeriodStart = \Carbon\Carbon::create($currentYear, 1, 1);
                        $currentPeriodEnd = $currentPeriodStart->copy()->endOfYear();
                        
                        $displayStart = $startDate->gte($currentPeriodStart) ? $startDate : $currentPeriodStart;
                        $displayEnd = $endDate->lte($currentPeriodEnd) ? $endDate : $currentPeriodEnd;
                        
                        $startDay = max(1, $displayStart->month);
                        $endDay = min(12, $displayEnd->month);
                        $duration = $endDay - $startDay + 1;
                    }
                    
                    $isVisible = $startDate->lte($currentPeriodEnd) && $endDate->gte($currentPeriodStart);
                @endphp
                
                <div class="flex border-b border-gray-100 hover:bg-gray-50">
                    <!-- 프로젝트 정보 -->
                    <div class="w-64 p-3 border-r border-gray-200">
                        <div class="text-sm font-medium text-gray-900">{{ $project['name'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <div>담당: {{ $project['created_by_name'] }}</div>
                            <div>진행: {{ $project['progress'] }}%</div>
                        </div>
                        <div class="flex items-center mt-1">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($project['status'] === 'active') bg-green-100 text-green-800
                                @elseif($project['status'] === 'in_progress') bg-blue-100 text-blue-800
                                @elseif($project['status'] === 'completed') bg-purple-100 text-purple-800
                                @elseif($project['status'] === 'blocked') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $project['status'] }}
                            </span>
                        </div>
                    </div>

                    <!-- 간트 차트 바 -->
                    <div class="flex-1 relative flex items-center" style="height: 80px;">
                        @if($isVisible)
                            @php
                                $totalPeriods = $viewMode === 'month' ? count($monthDays) : ($viewMode === 'quarter' ? 12 : 12);
                                $leftOffset = (($startDay - 1) / $totalPeriods) * 100;
                                $width = ($duration / $totalPeriods) * 100;
                            @endphp
                            
                            <div class="absolute inset-y-0 flex items-center" 
                                 style="left: {{ $leftOffset }}%; width: {{ $width }}%;">
                                <!-- 진행률 바 -->
                                <div class="w-full h-6 bg-gray-200 rounded-lg overflow-hidden">
                                    <div class="h-full 
                                        @if($project['status'] === 'completed') bg-purple-500
                                        @elseif($project['status'] === 'blocked') bg-red-500
                                        @elseif($project['status'] === 'active') bg-green-500
                                        @else bg-blue-500
                                        @endif"
                                        style="width: {{ $project['progress'] }}%;">
                                    </div>
                                </div>
                                
                                <!-- 진행률 텍스트 -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-xs text-white font-medium">{{ $project['progress'] }}%</span>
                                </div>
                            </div>
                        @endif

                        <!-- 날짜 구분선들 -->
                        @if($viewMode === 'month')
                            @foreach($monthDays as $index => $day)
                                <div class="absolute inset-y-0 border-r border-gray-100" 
                                     style="left: {{ (($index + 1) / count($monthDays)) * 100 }}%;"></div>
                            @endforeach
                        @elseif($viewMode === 'quarter')
                            @for($i = 1; $i <= 12; $i++)
                                <div class="absolute inset-y-0 border-r border-gray-100" 
                                     style="left: {{ ($i / 12) * 100 }}%;"></div>
                            @endfor
                        @else
                            @for($i = 1; $i <= 12; $i++)
                                <div class="absolute inset-y-0 border-r border-gray-100" 
                                     style="left: {{ ($i / 12) * 100 }}%;"></div>
                            @endfor
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 범례 -->
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-3">범례</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-xs text-gray-600">활성 프로젝트</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                <span class="text-xs text-gray-600">진행 중</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-purple-500 rounded"></div>
                <span class="text-xs text-gray-600">완료</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-xs text-gray-600">지연/블록</span>
            </div>
        </div>
    </div>

    <!-- 프로젝트 추가 모달 -->
    @if($showProjectModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeProjectModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                    새 프로젝트 추가
                                </h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="project-name" class="block text-sm font-medium text-gray-700">프로젝트 이름</label>
                                        <input type="text" id="project-name" 
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="프로젝트 이름을 입력하세요">
                                    </div>

                                    <div>
                                        <label for="project-description" class="block text-sm font-medium text-gray-700">설명</label>
                                        <textarea id="project-description" rows="3" 
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                  placeholder="프로젝트 설명을 입력하세요"></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="start-date" class="block text-sm font-medium text-gray-700">시작일</label>
                                            <input type="date" id="start-date" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="end-date" class="block text-sm font-medium text-gray-700">완료일</label>
                                            <input type="date" id="end-date" 
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="project-status" class="block text-sm font-medium text-gray-700">상태</label>
                                        <select id="project-status" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="active">활성</option>
                                            <option value="in_progress">진행중</option>
                                            <option value="completed">완료</option>
                                            <option value="blocked">차단됨</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            프로젝트 추가
                        </button>
                        <button type="button" wire:click="closeProjectModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            취소
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

