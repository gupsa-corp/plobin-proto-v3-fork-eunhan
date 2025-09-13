{{-- 샌드박스 간트 차트 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
<div class="min-h-screen bg-gray-50 p-6" 
     x-data="ganttData()" 
     x-init="init(); loadGanttData()"
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
                    <div class="w-64 p-4 border-r cursor-pointer" @click="openSidebar(project)">
                        <div class="font-medium text-gray-900" x-text="project.name"></div>
                        <div class="text-sm text-gray-500" x-text="project.client || project.description || '설명 없음'"></div>
                        <div class="text-xs text-gray-400 mt-1">
                            <span>진행률: </span><span x-text="(project.progress || 0) + '%'"></span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            <span x-text="formatDateRange(project.start_date, project.end_date)"></span>
                        </div>
                    </div>
                    <div class="flex-1 relative flex items-center" style="height: 80px;">
                        {{-- 간트 바 --}}
                        <div x-show="project.start_date && project.end_date"
                             class="absolute inset-y-0 flex items-center cursor-move group"
                             :style="getGanttBarStyle(project)"
                             @mousedown="startDrag($event, project)"
                             @click="openSidebar(project)">
                            <div class="w-full h-8 rounded-lg relative overflow-hidden shadow-sm transition-all group-hover:h-10 group-hover:shadow-lg"
                                 :class="getStatusColor(project.status)">
                                <div class="h-full bg-white bg-opacity-30 rounded-lg transition-all" 
                                     :style="`width: ${project.progress || 0}%`"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-white text-xs font-medium group-hover:text-sm transition-all" 
                                          x-text="(project.progress || 0) + '%'"></span>
                                </div>
                                {{-- 드래그 핸들 --}}
                                <div class="absolute left-0 inset-y-0 w-2 bg-black bg-opacity-20 rounded-l-lg cursor-ew-resize opacity-0 group-hover:opacity-100 transition-opacity"
                                     @mousedown.stop="startResizeLeft($event, project)"></div>
                                <div class="absolute right-0 inset-y-0 w-2 bg-black bg-opacity-20 rounded-r-lg cursor-ew-resize opacity-0 group-hover:opacity-100 transition-opacity"
                                     @mousedown.stop="startResizeRight($event, project)"></div>
                            </div>
                        </div>
                        
                        {{-- 시작일만 있고 종료일이 없는 경우 --}}
                        <div x-show="project.start_date && !project.end_date"
                             class="absolute inset-y-0 flex items-center cursor-move group"
                             :style="getGanttBarStyle(project)"
                             @click="openSidebar(project)">
                            <div class="w-8 h-8 rounded-full bg-orange-500 relative overflow-hidden shadow-sm transition-all group-hover:w-10 group-hover:h-10 group-hover:shadow-lg">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">!</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- 날짜 구분선들 --}}
                        <template x-for="(day, dayIndex) in monthDays" :key="day.date">
                            <div class="absolute inset-y-0 border-r border-gray-100 pointer-events-none" 
                                 :style="`left: ${(dayIndex + 1) * 32}px;`"></div>
                        </template>
                        
                        {{-- 오늘 표시선 --}}
                        <div x-show="showTodayLine" 
                             class="absolute inset-y-0 border-r-2 border-red-500 pointer-events-none z-10"
                             :style="`left: ${todayPosition}px;`">
                            <div class="absolute -top-2 -left-3 w-6 h-4 bg-red-500 text-white text-xs flex items-center justify-center rounded">
                                오늘
                            </div>
                        </div>
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

    {{-- 프로젝트 상세 사이드바 --}}
    <div x-show="sidebarOpen" 
         class="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl transform transition-transform duration-300 z-50"
         :class="{ 'translate-x-0': sidebarOpen, 'translate-x-full': !sidebarOpen }"
         @click.outside="closeSidebar()">
        <div class="flex flex-col h-full">
            {{-- 사이드바 헤더 --}}
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">프로젝트 상세</h3>
                    <button @click="closeSidebar()" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                        <span class="text-xl">×</span>
                    </button>
                </div>
            </div>

            {{-- 사이드바 내용 --}}
            <div x-show="selectedProject" class="flex-1 overflow-y-auto p-6 space-y-6">
                {{-- 프로젝트 기본 정보 --}}
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-3" x-text="selectedProject?.name"></h4>
                    <p class="text-sm text-gray-600 mb-4" x-text="selectedProject?.description || '설명 없음'"></p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">상태</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1"
                                  :class="getStatusBadgeClass(selectedProject?.status)"
                                  x-text="getStatusText(selectedProject?.status)"></span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">우선순위</label>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1"
                                  :class="getPriorityBadgeClass(selectedProject?.priority)"
                                  x-text="getPriorityText(selectedProject?.priority)"></span>
                        </div>
                    </div>
                </div>

                {{-- 진행률 --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-sm font-medium text-gray-700">진행률</label>
                        <span class="text-sm text-gray-600" x-text="(selectedProject?.progress || 0) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all duration-300"
                             :class="getStatusColor(selectedProject?.status)"
                             :style="`width: ${selectedProject?.progress || 0}%`"></div>
                    </div>
                    <input type="range" min="0" max="100" step="5"
                           :value="selectedProject?.progress || 0"
                           @input="updateProgress($event.target.value)"
                           class="w-full mt-2 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                </div>

                {{-- 일정 정보 --}}
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">일정</h5>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500">시작일</label>
                            <input type="date" 
                                   :value="selectedProject?.start_date || ''"
                                   @change="updateStartDate($event.target.value)"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">종료일</label>
                            <input type="date" 
                                   :value="selectedProject?.end_date || ''"
                                   @change="updateEndDate($event.target.value)"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500">기간</label>
                            <p class="mt-1 text-sm text-gray-600" x-text="calculateDuration(selectedProject?.start_date, selectedProject?.end_date)"></p>
                        </div>
                    </div>
                </div>

                {{-- 팀 정보 --}}
                <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-3">팀 정보</h5>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">팀 멤버 수</span>
                            <span class="text-sm text-gray-900" x-text="(selectedProject?.team_members || 0) + '명'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">클라이언트</span>
                            <span class="text-sm text-gray-900" x-text="selectedProject?.client || '미지정'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">카테고리</span>
                            <span class="text-sm text-gray-900" x-text="selectedProject?.category || '일반'"></span>
                        </div>
                    </div>
                </div>

                {{-- 액션 버튼 --}}
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex space-x-3">
                        <button @click="saveChanges()" 
                                class="flex-1 bg-orange-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-orange-700 transition-colors">
                            변경사항 저장
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
        
        // 사이드바 상태
        sidebarOpen: false,
        selectedProject: null,
        
        // 드래그 관련 상태
        isDragging: false,
        isResizing: false,
        dragProject: null,
        dragType: null, // 'move', 'resize-left', 'resize-right'
        startX: 0,
        originalStartDate: null,
        originalEndDate: null,
        
        get currentMonthText() {
            return this.currentDate.toLocaleDateString('ko-KR', { year: 'numeric', month: 'long' });
        },
        
        get showTodayLine() {
            const today = new Date();
            const currentMonth = this.currentDate.getMonth();
            const currentYear = this.currentDate.getFullYear();
            
            return today.getMonth() === currentMonth && today.getFullYear() === currentYear;
        },
        
        get todayPosition() {
            const today = new Date();
            const dayOfMonth = today.getDate();
            return (dayOfMonth - 1) * 32;
        },
        
        init() {
            document.addEventListener('mousemove', this.handleMouseMove.bind(this));
            document.addEventListener('mouseup', this.handleMouseUp.bind(this));
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
        
        // 사이드바 관리
        openSidebar(project) {
            this.selectedProject = { ...project };
            this.sidebarOpen = true;
        },
        
        closeSidebar() {
            this.sidebarOpen = false;
            this.selectedProject = null;
        },
        
        // 드래그 앤 드롭 기능
        startDrag(event, project) {
            if (event.button !== 0) return; // 좌클릭만
            
            this.isDragging = true;
            this.dragProject = project;
            this.dragType = 'move';
            this.startX = event.clientX;
            this.originalStartDate = project.start_date;
            this.originalEndDate = project.end_date;
            
            event.preventDefault();
        },
        
        startResizeLeft(event, project) {
            this.isDragging = true;
            this.dragProject = project;
            this.dragType = 'resize-left';
            this.startX = event.clientX;
            this.originalStartDate = project.start_date;
            
            event.preventDefault();
            event.stopPropagation();
        },
        
        startResizeRight(event, project) {
            this.isDragging = true;
            this.dragProject = project;
            this.dragType = 'resize-right';
            this.startX = event.clientX;
            this.originalEndDate = project.end_date;
            
            event.preventDefault();
            event.stopPropagation();
        },
        
        handleMouseMove(event) {
            if (!this.isDragging || !this.dragProject) return;
            
            const deltaX = event.clientX - this.startX;
            const daysDelta = Math.round(deltaX / 32);
            
            if (this.dragType === 'move') {
                if (this.originalStartDate && this.originalEndDate) {
                    const newStartDate = this.addDays(new Date(this.originalStartDate), daysDelta);
                    const newEndDate = this.addDays(new Date(this.originalEndDate), daysDelta);
                    
                    this.dragProject.start_date = newStartDate.toISOString().split('T')[0];
                    this.dragProject.end_date = newEndDate.toISOString().split('T')[0];
                }
            } else if (this.dragType === 'resize-left') {
                if (this.originalStartDate) {
                    const newStartDate = this.addDays(new Date(this.originalStartDate), daysDelta);
                    if (new Date(newStartDate) < new Date(this.dragProject.end_date)) {
                        this.dragProject.start_date = newStartDate.toISOString().split('T')[0];
                    }
                }
            } else if (this.dragType === 'resize-right') {
                if (this.originalEndDate) {
                    const newEndDate = this.addDays(new Date(this.originalEndDate), daysDelta);
                    if (new Date(newEndDate) > new Date(this.dragProject.start_date)) {
                        this.dragProject.end_date = newEndDate.toISOString().split('T')[0];
                    }
                }
            }
        },
        
        handleMouseUp() {
            if (this.isDragging && this.dragProject) {
                // 변경사항 자동 저장
                this.saveProjectDates(this.dragProject);
            }
            
            this.isDragging = false;
            this.dragProject = null;
            this.dragType = null;
        },
        
        addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        },
        
        // 프로젝트 업데이트 기능
        async updateProgress(progress) {
            if (this.selectedProject) {
                this.selectedProject.progress = parseInt(progress);
            }
        },
        
        async updateStartDate(date) {
            if (this.selectedProject) {
                this.selectedProject.start_date = date;
            }
        },
        
        async updateEndDate(date) {
            if (this.selectedProject) {
                this.selectedProject.end_date = date;
            }
        },
        
        async saveProjectDates(project) {
            try {
                const response = await fetch(`/api/sandbox/storage-sandbox-template/backend/api.php/projects/${project.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        start_date: project.start_date,
                        end_date: project.end_date,
                        progress: project.progress
                    })
                });
                
                const result = await response.json();
                
                if (!result.success) {
                    console.error('프로젝트 업데이트 실패:', result.message);
                    // 롤백
                    await this.loadGanttData();
                }
            } catch (error) {
                console.error('프로젝트 업데이트 오류:', error);
                await this.loadGanttData();
            }
        },
        
        async saveChanges() {
            if (!this.selectedProject) return;
            
            try {
                const response = await fetch(`/api/sandbox/storage-sandbox-template/backend/api.php/projects/${this.selectedProject.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        start_date: this.selectedProject.start_date,
                        end_date: this.selectedProject.end_date,
                        progress: this.selectedProject.progress
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 메인 데이터 업데이트
                    const projectIndex = this.projects.findIndex(p => p.id === this.selectedProject.id);
                    if (projectIndex !== -1) {
                        this.projects[projectIndex] = { ...this.selectedProject };
                    }
                    
                    this.closeSidebar();
                    this.calculateStats();
                } else {
                    alert('저장 실패: ' + (result.message || '알 수 없는 오류'));
                }
            } catch (error) {
                console.error('저장 오류:', error);
                alert('저장 중 오류가 발생했습니다.');
            }
        },
        
        // 위치 및 크기 계산
        getGanttBarStyle(project) {
            if (!project.start_date) return 'display: none;';
            
            const startDate = new Date(project.start_date);
            const monthStart = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const monthEnd = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
            
            let leftPosition = 0;
            let width = 32; // 기본 최소 너비
            
            if (project.end_date) {
                const endDate = new Date(project.end_date);
                
                // 효과적인 시작일과 종료일 계산 (현재 월 범위 내)
                const effectiveStart = startDate < monthStart ? monthStart : startDate;
                const effectiveEnd = endDate > monthEnd ? monthEnd : endDate;
                
                // 프로젝트가 현재 월에 포함되는지 확인
                if (endDate < monthStart || startDate > monthEnd) {
                    return 'display: none;'; // 현재 월에 포함되지 않음
                }
                
                // 시작 위치 계산
                if (startDate >= monthStart) {
                    leftPosition = (startDate.getDate() - 1) * 32;
                } else {
                    leftPosition = 0;
                }
                
                // 너비 계산
                const diffTime = Math.abs(effectiveEnd - effectiveStart);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                width = Math.max(diffDays * 32, 32);
                
            } else {
                // 종료일이 없는 경우 시작일 위치에 원형 표시
                if (startDate < monthStart || startDate > monthEnd) {
                    return 'display: none;';
                }
                leftPosition = (startDate.getDate() - 1) * 32;
                width = 32;
            }
            
            return `left: ${leftPosition}px; width: ${width}px;`;
        },
        
        // 이전 함수들 호환성을 위해 유지
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
            if (!project.start_date || !project.end_date) return 32;
            
            const startDate = new Date(project.start_date);
            const endDate = new Date(project.end_date);
            const monthStart = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const monthEnd = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
            
            const effectiveStart = startDate < monthStart ? monthStart : startDate;
            const effectiveEnd = endDate > monthEnd ? monthEnd : endDate;
            
            if (effectiveEnd <= effectiveStart) {
                return 32;
            }
            
            const diffTime = Math.abs(effectiveEnd - effectiveStart);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            return Math.max(diffDays * 32, 32);
        },
        
        // 스타일링 헬퍼
        getStatusColor(status) {
            const colorMap = {
                'planned': 'bg-gray-500',
                'in-progress': 'bg-blue-500',
                'completed': 'bg-green-500',
                'on-hold': 'bg-yellow-500',
                'cancelled': 'bg-red-500'
            };
            return colorMap[status] || 'bg-gray-500';
        },
        
        getStatusBadgeClass(status) {
            const colorMap = {
                'planned': 'bg-gray-100 text-gray-800',
                'in-progress': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'on-hold': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return colorMap[status] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusText(status) {
            const textMap = {
                'planned': '계획',
                'in-progress': '진행 중',
                'completed': '완료',
                'on-hold': '보류',
                'cancelled': '취소'
            };
            return textMap[status] || status;
        },
        
        getPriorityBadgeClass(priority) {
            const colorMap = {
                'low': 'bg-green-100 text-green-800',
                'medium': 'bg-yellow-100 text-yellow-800',
                'high': 'bg-red-100 text-red-800'
            };
            return colorMap[priority] || 'bg-gray-100 text-gray-800';
        },
        
        getPriorityText(priority) {
            const textMap = {
                'low': '낮음',
                'medium': '보통',
                'high': '높음'
            };
            return textMap[priority] || priority;
        },
        
        formatDateRange(startDate, endDate) {
            if (!startDate && !endDate) return '일정 미정';
            if (!startDate) return `~ ${endDate}`;
            if (!endDate) return `${startDate} ~`;
            return `${startDate} ~ ${endDate}`;
        },
        
        calculateDuration(startDate, endDate) {
            if (!startDate || !endDate) return '기간 미정';
            
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            return `${diffDays}일`;
        },
        
        calculateStats() {
            const now = new Date();
            this.stats.total = this.projects.length;
            this.stats.completed = this.projects.filter(p => p.status === 'completed').length;
            
            // 진행 중인 프로젝트 중 지연/순조 구분
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