{{-- 샌드박스 간트 차트 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
<div class="min-h-screen bg-gray-50 p-6" 
     x-data="ganttData()" 
     x-init="loadGanttData()"
     x-cloak>
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
            <button @click="navigateMonth(-1)" class="p-2 text-gray-600 hover:bg-gray-100 rounded">←</button>
            <h3 class="text-lg font-semibold text-gray-900" x-text="currentMonthText"></h3>
            <button @click="navigateMonth(1)" class="p-2 text-gray-600 hover:bg-gray-100 rounded">→</button>
        </div>
    </div>

    {{-- 간트 차트 --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            {{-- 날짜 헤더 --}}
            <div class="flex border-b">
                <div class="w-64 p-4 bg-gray-50 border-r font-semibold text-gray-900">프로젝트</div>
                <div class="flex-1 flex bg-gray-50">
                    <template x-for="day in monthDays" :key="day.date">
                        <div class="w-8 p-2 text-center border-r border-gray-200">
                            <div class="text-xs text-gray-600" x-text="day.day"></div>
                            <div class="text-xs text-gray-400" x-text="day.dayOfWeek"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 프로젝트 행들 --}}
            <div x-show="projects.length === 0" class="p-8 text-center text-gray-500">
                데이터를 로딩 중...
            </div>
            <template x-for="(project, index) in projects" :key="project.id">
                <div class="flex border-b hover:bg-gray-50">
                    <div class="w-64 p-4 border-r">
                        <div class="font-medium text-gray-900" x-text="project.name"></div>
                        <div class="text-sm text-gray-500" x-text="project.client || '미지정'"></div>
                        <div class="text-xs text-gray-400 mt-1">
                            <span>진행률: </span><span x-text="project.progress + '%'"></span>
                        </div>
                    </div>
                    <div class="flex-1 relative flex items-center" style="height: 60px;">
                        {{-- 간트 바 --}}
                        <div class="absolute inset-y-0 flex items-center" 
                             :style="`left: ${getProjectStartPosition(project)}px; width: ${getProjectDuration(project)}px;`"
                             x-show="project.start_date && project.end_date">
                            <div class="w-full h-6 rounded-lg relative overflow-hidden"
                                 :class="getCategoryColor(project.category)">
                                <div class="h-full bg-black bg-opacity-20 rounded-lg" 
                                     :style="`width: ${project.progress || 0}%`"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-white text-xs font-medium" x-text="(project.progress || 0) + '%'"></span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- 날짜 구분선들 --}}
                        <template x-for="(day, dayIndex) in monthDays" :key="day.date">
                            <div class="absolute inset-y-0 border-r border-gray-100" 
                                 :style="`left: ${(dayIndex + 1) * 32}px;`"></div>
                        </template>
                    </div>
                </div>
            </template>
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
            <div class="text-2xl font-bold text-gray-900" x-text="stats.total || '-'"></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">순조진행</div>
            <div class="text-2xl font-bold text-green-600" x-text="stats.onTrack || '-'"></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">지연</div>
            <div class="text-2xl font-bold text-red-600" x-text="stats.delayed || '-'"></div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="text-sm text-gray-600">완료</div>
            <div class="text-2xl font-bold text-blue-600" x-text="stats.completed || '-'"></div>
        </div>
    </div>
</div>

<script>
function ganttData() {
    return {
        projects: [],
        stats: {
            total: 0,
            onTrack: 0,
            delayed: 0,
            completed: 0
        },
        currentDate: new Date(),
        monthDays: [],
        
        get currentMonthText() {
            return this.currentDate.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long' });
        },
        
        async loadGanttData() {
            try {
                const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects');
                const result = await response.json();
                
                if (result.success && result.data) {
                    this.projects = result.data.projects || [];
                    this.calculateStats();
                    this.generateMonthDays();
                } else {
                    console.error('간트 차트 API 오류:', result.message);
                }
            } catch (error) {
                console.error('간트 차트 데이터 로딩 실패:', error);
            }
        },
        
        generateMonthDays() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            this.monthDays = [];
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dayOfWeek = ['일', '월', '화', '수', '목', '금', '토'][date.getDay()];
                
                this.monthDays.push({
                    date: date.toISOString().split('T')[0],
                    day: day,
                    dayOfWeek: dayOfWeek
                });
            }
        },
        
        navigateMonth(direction) {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + direction, 1);
            this.generateMonthDays();
        },
        
        getProjectStartPosition(project) {
            if (!project.start_date) return 0;
            
            const startDate = new Date(project.start_date);
            const monthStart = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            
            if (startDate < monthStart) {
                return 0;
            }
            
            const dayOfMonth = startDate.getDate();
            return (dayOfMonth - 1) * 32;
        },
        
        getProjectDuration(project) {
            if (!project.start_date || !project.end_date) return 0;
            
            const startDate = new Date(project.start_date);
            const endDate = new Date(project.end_date);
            const monthStart = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const monthEnd = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
            
            const effectiveStart = startDate < monthStart ? monthStart : startDate;
            const effectiveEnd = endDate > monthEnd ? monthEnd : endDate;
            
            const diffTime = Math.abs(effectiveEnd - effectiveStart);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            return Math.max(diffDays * 32, 32);
        },
        
        getCategoryColor(category) {
            const colorMap = {
                'IoT': 'bg-blue-500',
                'Testing': 'bg-yellow-500',
                'Enterprise System': 'bg-purple-500',
                'general': 'bg-gray-500'
            };
            return colorMap[category] || 'bg-green-500';
        },
        
        calculateStats() {
            const now = new Date();
            this.stats.total = this.projects.length;
            this.stats.completed = this.projects.filter(p => p.status === 'completed').length;
            
            // 간단한 지연/순조 로직
            const inProgress = this.projects.filter(p => p.status === 'in-progress');
            this.stats.onTrack = inProgress.filter(p => {
                if (!p.end_date) return true;
                const endDate = new Date(p.end_date);
                return endDate > now;
            }).length;
            
            this.stats.delayed = inProgress.filter(p => {
                if (!p.end_date) return false;
                const endDate = new Date(p.end_date);
                return endDate <= now;
            }).length;
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>