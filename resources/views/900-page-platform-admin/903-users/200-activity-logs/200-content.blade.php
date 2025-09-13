{{-- 사용자 활동 로그 메인 콘텐츠 --}}
<div class="activity-logs-content" style="padding: 24px;" x-data="activityLogsManagement">

    {{-- 필터 섹션 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">필터 및 검색</h3>
        
        <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- 검색 --}}
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                <input type="text" 
                       id="search"
                       name="search"
                       value="{{ $filters['search'] }}"
                       placeholder="설명 또는 로그명으로 검색..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            {{-- 사용자 필터 --}}
            <div>
                <label for="user-filter" class="block text-sm font-medium text-gray-700 mb-2">사용자</label>
                <select name="user_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체 사용자</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- 이벤트 타입 필터 --}}
            <div>
                <label for="event-filter" class="block text-sm font-medium text-gray-700 mb-2">이벤트 타입</label>
                <select name="event" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체 이벤트</option>
                    @foreach($eventTypes as $eventType)
                        <option value="{{ $eventType }}" {{ $filters['event'] == $eventType ? 'selected' : '' }}>
                            {{ $eventType }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- 날짜 필터 --}}
            <div>
                <label for="date-from" class="block text-sm font-medium text-gray-700 mb-2">시작 날짜</label>
                <input type="date" 
                       id="date-from"
                       name="date_from"
                       value="{{ $filters['date_from'] }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            {{-- 버튼들 --}}
            <div class="md:col-span-5 flex justify-between items-center pt-4 border-t border-gray-200">
                <div class="flex gap-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm">
                        필터 적용
                    </button>
                    <a href="{{ request()->url() }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm">
                        필터 초기화
                    </a>
                </div>
                
                <div class="flex gap-2">
                    <label for="date-to" class="text-sm font-medium text-gray-700 flex items-center mr-2">종료 날짜:</label>
                    <input type="date" 
                           id="date-to"
                           name="date_to"
                           value="{{ $filters['date_to'] }}"
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>
        </form>
    </div>

    {{-- 활동 로그 목록 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">활동 로그 목록</h3>
                <div class="text-sm text-gray-500">
                    총 {{ $activities->total() }}개의 활동 로그
                </div>
            </div>
        </div>

        @if($activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                시간
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                사용자
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                액션
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                설명
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                대상
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                세부정보
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $activity->created_at->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-400">{{ $activity->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($activity->causer)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-gray-700">
                                                        {{ substr($activity->causer->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $activity->causer->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $activity->causer->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">시스템</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @switch($activity->event)
                                            @case('created')
                                                bg-green-100 text-green-800
                                                @break
                                            @case('updated')
                                                bg-blue-100 text-blue-800
                                                @break
                                            @case('deleted')
                                                bg-red-100 text-red-800
                                                @break
                                            @case('login')
                                                bg-purple-100 text-purple-800
                                                @break
                                            @default
                                                bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ $activity->event ?: '알 수 없음' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $activity->description ?: '설명 없음' }}</div>
                                    @if($activity->log_name)
                                        <div class="text-xs text-gray-500">{{ $activity->log_name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($activity->subject_type)
                                        <div>{{ class_basename($activity->subject_type) }}</div>
                                        @if($activity->subject_id)
                                            <div class="text-xs text-gray-400">ID: {{ $activity->subject_id }}</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($activity->properties && $activity->properties->count() > 0)
                                        <button @click="showDetails('{{ $activity->id }}')" 
                                                class="text-blue-600 hover:text-blue-900">
                                            상세보기
                                        </button>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 페이지네이션 --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $activities->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="text-gray-400 text-lg mb-2">📋</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">활동 로그가 없습니다</h3>
                <p class="text-sm text-gray-500">필터 조건을 변경하거나 날짜 범위를 조정해보세요.</p>
            </div>
        @endif
    </div>

    {{-- 상세정보 모달 --}}
    <div x-show="showDetailModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="showDetailModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">활동 상세정보</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">상세 정보</label>
                            <pre class="mt-1 text-sm text-gray-600 bg-gray-50 p-3 rounded" x-text="selectedActivityDetails"></pre>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button @click="showDetailModal = false" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        닫기
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('activityLogsManagement', () => ({
        showDetailModal: false,
        selectedActivityDetails: '',

        init() {
            console.log('Activity logs management initialized');
        },

        showDetails(activityId) {
            // 실제 구현시 AJAX로 상세 정보를 가져와야 함
            this.selectedActivityDetails = 'Activity ID: ' + activityId + '\n활동에 대한 상세 정보가 여기에 표시됩니다.';
            this.showDetailModal = true;
        }
    }));
});
</script>