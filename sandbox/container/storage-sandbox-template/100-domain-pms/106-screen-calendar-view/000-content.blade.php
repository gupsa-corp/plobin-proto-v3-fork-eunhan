{{-- 샌드박스 달력 뷰 템플릿 --}}
<?php
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;
    

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-cyan-50 p-6"
     x-data="calendarData()"
     x-init="loadCalendarData()"
     x-cloak>
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
                <button @click="openCreateEventModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">새 이벤트</button>
            </div>
        </div>
    </div>

    {{-- 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">이번 달 일정</p>
                    <p class="text-2xl font-bold text-indigo-600" x-text="stats.monthlyEvents || '-'"></p>
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
                    <p class="text-2xl font-bold text-green-600" x-text="stats.todayEvents || '-'"></p>
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
                    <p class="text-2xl font-bold text-purple-600" x-text="stats.milestones || '-'"></p>
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
                    <p class="text-2xl font-bold text-red-600" x-text="stats.overdue || '-'"></p>
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
            <button @click="navigateMonth(-1)" class="p-2 text-gray-600 hover:bg-gray-100 rounded">←</button>
            <h3 class="text-lg font-semibold text-gray-900" x-text="currentMonthText"></h3>
            <button @click="navigateMonth(1)" class="p-2 text-gray-600 hover:bg-gray-100 rounded">→</button>
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
        <div class="grid grid-cols-7">
            <template x-for="day in calendarDays" :key="day.date">
                <div class="min-h-24 p-2 border-r border-b border-gray-100"
                     :class="{
                         'bg-gray-50 text-gray-400': !day.isCurrentMonth,
                         'bg-blue-50': day.isToday
                     }">

                    {{-- 날짜 --}}
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm"
                              :class="{ 'font-bold text-blue-600': day.isToday }"
                              x-text="day.dayNumber">
                        </span>
                        <span x-show="day.isToday" class="w-2 h-2 bg-blue-600 rounded-full"></span>
                    </div>

                    {{-- 이벤트들 --}}
                    <div x-show="day.events && day.events.length > 0 && day.isCurrentMonth" class="space-y-1">
                        <template x-for="(event, index) in (day.events || []).slice(0, 2)" :key="event.id">
                            <div class="text-xs p-1 rounded truncate cursor-pointer hover:shadow-sm transition-shadow"
                                 :class="getEventColorClass(event.type)"
                                 :title="event.name"
                                 @click="openEventSidebar(event)"
                                 x-text="getEventIcon(event.type) + ' ' + event.name">
                            </div>
                        </template>
                        <div x-show="day.events && day.events.length > 2"
                             class="text-xs text-gray-500 text-center cursor-pointer hover:text-gray-700"
                             @click="openDayEvents(day)"
                             x-text="'+' + (day.events.length - 2) + '개 더'">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- 오늘의 일정 --}}
    <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="'오늘의 일정 (' + todayFormatted + ')'"></h3>
        <div x-show="todayEvents.length === 0" class="text-gray-500 text-center py-4">
            오늘 일정이 없습니다.
        </div>
        <div class="space-y-3">
            <template x-for="event in todayEvents" :key="event.id">
                <div class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                     @click="openEventSidebar(event)">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                         :class="getEventBgClass(event.type)">
                        <span x-text="getEventIcon(event.type)" :class="getEventTextClass(event.type)"></span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900" x-text="event.name"></h4>
                        <p class="text-sm text-gray-600" x-text="formatTimeRange(event)"></p>
                    </div>
                    <div class="flex space-x-2">
                        <button @click.stop="openEventSidebar(event)" class="px-3 py-1 text-sm text-indigo-600 hover:bg-indigo-50 rounded">
                            상세보기
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- 이벤트 상세 사이드바 --}}
    <div x-show="sidebarOpen"
         class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl transform transition-transform duration-300 z-50"
         :class="{ 'translate-x-0': sidebarOpen, 'translate-x-full': !sidebarOpen }"
         @click.outside="closeSidebar()">
        <div class="flex flex-col h-full">
            {{-- 사이드바 헤더 --}}
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">이벤트 상세</h3>
                    <button @click="closeSidebar()" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                        <span class="text-xl">×</span>
                    </button>
                </div>
            </div>

            {{-- 사이드바 내용 --}}
            <div x-show="selectedEvent" class="flex-1 overflow-y-auto p-6 space-y-6">
                {{-- 이벤트 기본 정보 --}}
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                             :class="getEventBgClass(selectedEvent?.type)">
                            <span x-text="getEventIcon(selectedEvent?.type)"
                                  :class="getEventTextClass(selectedEvent?.type)"
                                  class="text-lg"></span>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900" x-text="selectedEvent?.name"></h4>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                  :class="getEventColorClass(selectedEvent?.type)"
                                  x-text="getEventTypeText(selectedEvent?.type)"></span>
                        </div>
                    </div>
                </div>

                {{-- 프로젝트 편집 정보 --}}
                <div x-show="selectedProject">
                    <h5 class="text-sm font-medium text-gray-700 mb-3">연결된 프로젝트 편집</h5>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">프로젝트명</label>
                            <input type="text"
                                   :value="selectedProject?.name || ''"
                                   @input="selectedProject ? selectedProject.name = $event.target.value : null"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                            <textarea :value="selectedProject?.description || ''"
                                      @input="selectedProject ? selectedProject.description = $event.target.value : null"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                            <select :value="selectedProject?.status || 'planned'"
                                    @change="selectedProject ? selectedProject.status = $event.target.value : null"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="planned">계획</option>
                                <option value="in-progress">진행 중</option>
                                <option value="completed">완료</option>
                                <option value="on-hold">보류</option>
                                <option value="cancelled">취소</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">진행률 (%)</label>
                            <input type="range"
                                   :value="selectedProject?.progress || 0"
                                   @input="selectedProject ? selectedProject.progress = $event.target.value : null"
                                   min="0" max="100"
                                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            <div class="flex justify-between text-sm text-gray-500 mt-1">
                                <span>0%</span>
                                <span x-text="(selectedProject?.progress || 0) + '%'" class="font-medium text-indigo-600"></span>
                                <span>100%</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                            <select :value="selectedProject?.priority || 'medium'"
                                    @change="selectedProject ? selectedProject.priority = $event.target.value : null"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="low">낮음</option>
                                <option value="medium">보통</option>
                                <option value="high">높음</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">클라이언트</label>
                            <input type="text"
                                   :value="selectedProject?.client || ''"
                                   @input="selectedProject ? selectedProject.client = $event.target.value : null"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">시작일</label>
                            <input type="date"
                                   :value="selectedProject?.start_date || ''"
                                   @input="selectedProject ? selectedProject.start_date = $event.target.value : null"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">종료일</label>
                            <input type="date"
                                   :value="selectedProject?.end_date || ''"
                                   @input="selectedProject ? selectedProject.end_date = $event.target.value : null"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- 일정 정보 --}}
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">일정 정보</h5>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">시작일</span>
                            <span class="text-sm text-gray-900" x-text="selectedEvent?.start_date || '미지정'"></span>
                        </div>
                        <div class="flex justify-between" x-show="selectedEvent?.end_date">
                            <span class="text-sm text-gray-500">종료일</span>
                            <span class="text-sm text-gray-900" x-text="selectedEvent?.end_date"></span>
                        </div>
                        <div class="flex justify-between" x-show="selectedEvent?.start_date">
                            <span class="text-sm text-gray-500">D-Day</span>
                            <span class="text-sm text-gray-900" x-text="calculateDDay(selectedEvent?.start_date)"></span>
                        </div>
                    </div>
                </div>

                {{-- 추가 정보 --}}
                <div x-show="selectedProject">
                    <h5 class="text-sm font-medium text-gray-700 mb-3">프로젝트 상세</h5>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">상태</span>
                            <span class="text-sm text-gray-900" x-text="getStatusText(selectedProject?.status)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">우선순위</span>
                            <span class="text-sm text-gray-900" x-text="getPriorityText(selectedProject?.priority)"></span>
                        </div>
                        <div class="flex justify-between" x-show="selectedProject?.client">
                            <span class="text-xs text-gray-500">클라이언트</span>
                            <span class="text-sm text-gray-900" x-text="selectedProject?.client"></span>
                        </div>
                    </div>
                </div>

                {{-- 액션 버튼 --}}
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <button @click="saveProjectChanges()"
                                class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors">
                            프로젝트 저장
                        </button>
                        <button @click="closeSidebar()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition-colors">
                            닫기
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 사이드바 오버레이 --}}
    <div x-show="sidebarOpen"
         class="fixed inset-0 bg-black bg-opacity-25 z-40"
         @click="closeSidebar()"></div>

    {{-- 새 이벤트 생성 모달 --}}
    <div x-show="createEventModalOpen"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="closeCreateEventModal()">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">새 이벤트 추가</h3>
                    <button @click="closeCreateEventModal()" class="text-gray-400 hover:text-gray-600">
                        <span class="text-xl">×</span>
                    </button>
                </div>

                <form @submit.prevent="createEvent()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">이벤트명 *</label>
                            <input type="text"
                                   x-model="newEvent.name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="이벤트명을 입력하세요">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">이벤트 유형</label>
                            <select x-model="newEvent.type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="meeting">회의</option>
                                <option value="milestone">마일스톤</option>
                                <option value="review">리뷰</option>
                                <option value="start">시작</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">시작일 *</label>
                            <input type="date"
                                   x-model="newEvent.start_date"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                            <input type="date"
                                   x-model="newEvent.end_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">연결 프로젝트</label>
                            <select x-model="newEvent.project_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">선택안함</option>
                                <template x-for="project in projects" :key="project.id">
                                    <option :value="project.id" x-text="project.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="submit"
                                class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-md font-medium hover:bg-indigo-700 transition-colors">
                            이벤트 생성
                        </button>
                        <button type="button"
                                @click="closeCreateEventModal()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-50 transition-colors">
                            취소
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calendarData() {
    return {
        projects: [],
        events: [],
        currentDate: new Date(),
        calendarDays: [],
        stats: {
            monthlyEvents: 0,
            todayEvents: 0,
            milestones: 0,
            overdue: 0
        },

        // 사이드바 상태
        sidebarOpen: false,
        selectedEvent: null,
        selectedProject: null,

        // 이벤트 생성 모달 상태
        createEventModalOpen: false,
        newEvent: {
            name: '',
            type: 'meeting',
            start_date: '',
            end_date: '',
            project_id: ''
        },

        get currentMonthText() {
            return this.currentDate.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long' });
        },

        get todayFormatted() {
            const today = new Date();
            return today.toLocaleDateString('ko-KR', { month: 'long', day: 'numeric' });
        },

        get todayEvents() {
            const today = new Date().toISOString().split('T')[0];
            console.log('=== todayEvents getter called ===');
            console.log('Today date:', today);
            console.log('Total events available:', this.events.length);
            
            const filteredEvents = this.events.filter(event => {
                const matchesStart = event.start_date && event.start_date.startsWith(today);
                const matchesEnd = event.end_date && event.end_date.startsWith(today);
                
                console.log(`Event "${event.name}": start_date=${event.start_date}, matches=${matchesStart || matchesEnd}`);
                
                return matchesStart || matchesEnd;
            });
            
            console.log('=== Today events result ===');
            console.log('Found', filteredEvents.length, 'events for today');
            console.log('Events:', filteredEvents);
            return filteredEvents;
        },

        async loadCalendarData() {
            try {
                const response = await fetch('/api/sandbox/projects');
                const result = await response.json();

                if (result.success && result.data) {
                    this.projects = result.data.projects || [];
                    this.generateEvents();
                    this.generateCalendar();
                    this.calculateStats();
                } else {
                    console.error('캘린더 API 오류:', result.message);
                }
            } catch (error) {
                console.error('캘린더 데이터 로딩 실패:', error);
            }
        },

        generateEvents() {
            // 프로젝트를 기반으로 이벤트 생성
            this.events = [];

            this.projects.forEach(project => {
                if (project.start_date) {
                    this.events.push({
                        id: `start_${project.id}`,
                        name: `${project.name} 시작`,
                        type: 'start',
                        start_date: project.start_date,
                        project_id: project.id
                    });
                }

                if (project.end_date) {
                    this.events.push({
                        id: `end_${project.id}`,
                        name: `${project.name} 완료`,
                        type: 'milestone',
                        start_date: project.end_date,
                        project_id: project.id
                    });
                }

                // 진행률 50% 달성 이벤트 (중간 체크포인트)
                if (project.progress >= 50 && project.start_date && project.end_date) {
                    const startDate = new Date(project.start_date);
                    const endDate = new Date(project.end_date);
                    const midDate = new Date(startDate.getTime() + (endDate.getTime() - startDate.getTime()) / 2);

                    this.events.push({
                        id: `mid_${project.id}`,
                        name: `${project.name} 중간 체크`,
                        type: 'review',
                        start_date: midDate.toISOString().split('T')[0],
                        project_id: project.id
                    });
                }
            });
            
            // 테스트용 오늘 이벤트 추가 (항상 오늘 이벤트가 표시되도록)
            const today = new Date().toISOString().split('T')[0];
            console.log('Generating events, today is:', today);
            
            // 오늘 이벤트를 항상 추가하여 테스트
            this.events.push({
                id: 'test_today_1',
                name: '오늘 팀 회의',
                type: 'meeting', 
                start_date: today,
                project_id: null,
                description: '주간 팀 미팅'
            });
            
            this.events.push({
                id: 'test_today_2', 
                name: '프로젝트 리뷰',
                type: 'review',
                start_date: today,
                project_id: null,
                description: '월간 프로젝트 진행 상황 검토'
            });
            
            this.events.push({
                id: 'test_today_3',
                name: '클라이언트 미팅',
                type: 'meeting',
                start_date: today,
                end_date: today,
                project_id: null,
                description: '분기별 클라이언트 미팅'
            });
            
            console.log('Added test events for today. Total events:', this.events.length);
            
            console.log('Generated events:', this.events);
            console.log('Today date:', today);
        },

        generateCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();

            // 달의 첫날과 마지막날
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);

            // 캘린더 시작일 (주의 첫날부터)
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            // 캘린더 종료일 (주의 마지막날까지)
            const endDate = new Date(lastDay);
            endDate.setDate(endDate.getDate() + (6 - lastDay.getDay()));

            this.calendarDays = [];
            const currentDate = new Date(startDate);

            while (currentDate <= endDate) {
                const dateStr = currentDate.toISOString().split('T')[0];
                const dayEvents = this.events.filter(event =>
                    event.start_date === dateStr
                );

                this.calendarDays.push({
                    date: dateStr,
                    dayNumber: currentDate.getDate(),
                    isCurrentMonth: currentDate.getMonth() === month,
                    isToday: this.isToday(currentDate),
                    events: dayEvents
                });

                currentDate.setDate(currentDate.getDate() + 1);
            }
        },

        navigateMonth(direction) {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + direction, 1);
            this.generateCalendar();
            this.calculateStats();
        },

        calculateStats() {
            const today = new Date();
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            this.stats.monthlyEvents = this.events.filter(event => {
                const eventDate = new Date(event.start_date);
                return eventDate >= monthStart && eventDate <= monthEnd;
            }).length;

            this.stats.todayEvents = this.todayEvents.length;

            this.stats.milestones = this.events.filter(event =>
                event.type === 'milestone'
            ).length;

            this.stats.overdue = this.events.filter(event => {
                const eventDate = new Date(event.start_date);
                return eventDate < today && event.type !== 'milestone';
            }).length;
        },

        isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        },

        getEventColorClass(type) {
            const colorClasses = {
                'start': 'bg-green-100 text-green-700',
                'milestone': 'bg-purple-100 text-purple-700',
                'review': 'bg-yellow-100 text-yellow-700',
                'meeting': 'bg-blue-100 text-blue-700'
            };
            return colorClasses[type] || 'bg-gray-100 text-gray-700';
        },

        getEventBgClass(type) {
            const bgClasses = {
                'start': 'bg-green-100',
                'milestone': 'bg-purple-100',
                'review': 'bg-yellow-100',
                'meeting': 'bg-blue-100'
            };
            return bgClasses[type] || 'bg-gray-100';
        },

        getEventTextClass(type) {
            const textClasses = {
                'start': 'text-green-600',
                'milestone': 'text-purple-600',
                'review': 'text-yellow-600',
                'meeting': 'text-blue-600'
            };
            return textClasses[type] || 'text-gray-600';
        },

        getEventIcon(type) {
            const icons = {
                'start': '🚀',
                'milestone': '🎯',
                'review': '📝',
                'meeting': '👥'
            };
            return icons[type] || '📅';
        },

        formatTimeRange(event) {
            if (event.start_date && event.end_date && event.start_date !== event.end_date) {
                return `${event.start_date} - ${event.end_date}`;
            }
            return event.start_date || '시간 미지정';
        },

        // 사이드바 관리
        openEventSidebar(event) {
            this.selectedEvent = { ...event };
            this.selectedProject = null;

            if (event.project_id) {
                this.selectedProject = this.projects.find(p => p.id == event.project_id) || null;
            }

            this.sidebarOpen = true;
        },

        openDayEvents(day) {
            // 하루에 많은 이벤트가 있을 때 처리
            if (day.events && day.events.length > 0) {
                this.openEventSidebar(day.events[0]);
            }
        },

        closeSidebar() {
            this.sidebarOpen = false;
            this.selectedEvent = null;
            this.selectedProject = null;
        },

        editEvent() {
            // 이벤트 편집 기능 (추후 구현)
            alert('이벤트 편집 기능은 추후 구현 예정입니다.');
        },

        // 이벤트 생성 모달 관리
        openCreateEventModal() {
            this.createEventModalOpen = true;
            const today = new Date().toISOString().split('T')[0];
            this.newEvent = {
                name: '',
                type: 'meeting',
                start_date: today,
                end_date: '',
                project_id: ''
            };
        },

        closeCreateEventModal() {
            this.createEventModalOpen = false;
            this.newEvent = {
                name: '',
                type: 'meeting',
                start_date: '',
                end_date: '',
                project_id: ''
            };
        },

        async createEvent() {
            if (!this.newEvent.name.trim()) {
                alert('이벤트명을 입력해주세요.');
                return;
            }

            try {
                // 새 이벤트 생성 (임시 ID 사용)
                const newEvent = {
                    id: `custom_${Date.now()}`,
                    name: this.newEvent.name,
                    type: this.newEvent.type,
                    start_date: this.newEvent.start_date,
                    end_date: this.newEvent.end_date,
                    project_id: this.newEvent.project_id
                };

                // 이벤트 목록에 추가
                this.events.push(newEvent);

                // 캘린더 재생성
                this.generateCalendar();
                this.calculateStats();

                this.closeCreateEventModal();
                alert('이벤트가 성공적으로 생성되었습니다.');

            } catch (error) {
                console.error('이벤트 생성 오류:', error);
                alert('이벤트 생성 중 오류가 발생했습니다.');
            }
        },

        // 헬퍼 함수들
        calculateDDay(dateStr) {
            if (!dateStr) return '미정';

            const targetDate = new Date(dateStr);
            const today = new Date();

            // 시간 제거하고 날짜만 비교
            targetDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);

            const diffTime = targetDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 0) return 'D-Day';
            if (diffDays > 0) return `D-${diffDays}`;
            return `D+${Math.abs(diffDays)}`;
        },

        getEventTypeText(type) {
            const typeMap = {
                'start': '시작',
                'milestone': '마일스톤',
                'review': '리뷰',
                'meeting': '회의'
            };
            return typeMap[type] || type;
        },

        getStatusText(status) {
            const statusMap = {
                'planned': '계획',
                'in-progress': '진행 중',
                'completed': '완료',
                'on-hold': '보류',
                'cancelled': '취소'
            };
            return statusMap[status] || status;
        },

        getPriorityText(priority) {
            const priorityMap = {
                'low': '낮음',
                'medium': '보통',
                'high': '높음'
            };
            return priorityMap[priority] || priority;
        },

        // 프로젝트 변경사항 저장
        async saveProjectChanges() {
            if (!this.selectedProject) return;

            try {
                const response = await fetch(`/api/sandbox/projects/${this.selectedProject.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.selectedProject.name,
                        description: this.selectedProject.description,
                        status: this.selectedProject.status,
                        progress: this.selectedProject.progress,
                        priority: this.selectedProject.priority,
                        client: this.selectedProject.client,
                        start_date: this.selectedProject.start_date,
                        end_date: this.selectedProject.end_date
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // 메인 데이터 업데이트
                    const projectIndex = this.projects.findIndex(p => p.id === this.selectedProject.id);
                    if (projectIndex !== -1) {
                        this.projects[projectIndex] = { ...this.selectedProject };
                    }

                    // 이벤트 재생성 및 캘린더 업데이트
                    this.generateEvents();
                    this.generateCalendar();
                    this.calculateStats();

                    alert('프로젝트가 성공적으로 저장되었습니다.');
                } else {
                    alert('저장 실패: ' + (result.message || '알 수 없는 오류'));
                }
            } catch (error) {
                console.error('저장 오류:', error);
                alert('저장 중 오류가 발생했습니다.');
            }
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
