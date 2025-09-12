{{-- 샌드박스 달력 뷰 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
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
                            <div class="text-xs p-1 rounded truncate"
                                 :class="getEventColorClass(event.type)"
                                 :title="event.name"
                                 x-text="getEventIcon(event.type) + ' ' + event.name">
                            </div>
                        </template>
                        <div x-show="day.events && day.events.length > 2" 
                             class="text-xs text-gray-500 text-center"
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
                <div class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                         :class="getEventBgClass(event.type)">
                        <span x-text="getEventIcon(event.type)" :class="getEventTextClass(event.type)"></span>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900" x-text="event.name"></h4>
                        <p class="text-sm text-gray-600" x-text="formatTimeRange(event)"></p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 rounded">편집</button>
                    </div>
                </div>
            </template>
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
        
        get currentMonthText() {
            return this.currentDate.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long' });
        },
        
        get todayFormatted() {
            const today = new Date();
            return today.toLocaleDateString('ko-KR', { month: 'long', day: 'numeric' });
        },
        
        get todayEvents() {
            const today = new Date().toISOString().split('T')[0];
            return this.events.filter(event => {
                return (event.start_date && event.start_date.startsWith(today)) ||
                       (event.end_date && event.end_date.startsWith(today));
            });
        },
        
        async loadCalendarData() {
            try {
                const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects');
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
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>