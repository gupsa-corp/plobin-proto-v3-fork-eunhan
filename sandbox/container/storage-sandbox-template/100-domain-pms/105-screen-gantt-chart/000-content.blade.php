{{-- 샌드박스 간트 차트 템플릿 --}}
<?php
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;
    

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
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
                    <button @click="setViewMode('month')"
                            :class="viewMode === 'month' ? 'px-3 py-1 text-sm bg-white shadow-sm rounded-md' : 'px-3 py-1 text-sm text-gray-600'">월</button>
                    <button @click="setViewMode('quarter')"
                            :class="viewMode === 'quarter' ? 'px-3 py-1 text-sm bg-white shadow-sm rounded-md' : 'px-3 py-1 text-sm text-gray-600'">분기</button>
                    <button @click="setViewMode('year')"
                            :class="viewMode === 'year' ? 'px-3 py-1 text-sm bg-white shadow-sm rounded-md' : 'px-3 py-1 text-sm text-gray-600'">년</button>
                </div>
                <button @click="openCreateModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">+ 프로젝트 추가</button>
                <button class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">내보내기</button>
            </div>
        </div>
    </div>

    {{-- 필터 및 검색 --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            {{-- 검색 --}}
            <div class="flex-1 min-w-64">
                <div class="relative">
                    <input type="text"
                           x-model="searchTerm"
                           @input="applyFilters()"
                           placeholder="프로젝트명, 설명, 클라이언트로 검색..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        🔍
                    </div>
                </div>
            </div>

            {{-- 상태 필터 --}}
            <div class="min-w-40">
                <select x-model="statusFilter"
                        @change="applyFilters()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">모든 상태</option>
                    <option value="planned">계획</option>
                    <option value="in-progress">진행 중</option>
                    <option value="completed">완료</option>
                    <option value="on-hold">보류</option>
                    <option value="cancelled">취소</option>
                </select>
            </div>

            {{-- 우선순위 필터 --}}
            <div class="min-w-32">
                <select x-model="priorityFilter"
                        @change="applyFilters()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">모든 우선순위</option>
                    <option value="high">높음</option>
                    <option value="medium">보통</option>
                    <option value="low">낮음</option>
                </select>
            </div>

            {{-- 필터 초기화 --}}
            <button @click="clearFilters()"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                초기화
            </button>

            {{-- 필터된 결과 개수 --}}
            <div class="text-sm text-gray-500">
                <span x-text="filteredProjects.length"></span>개 프로젝트 표시
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

                {{-- 월 뷰 --}}
                <div x-show="viewMode === 'month'" class="flex-1 flex bg-gray-50">
                    <template x-for="day in monthDays" :key="day.date">
                        <div class="w-8 p-2 text-center border-r border-gray-200">
                            <div class="text-xs text-gray-600" x-text="day.day"></div>
                            <div class="text-xs text-gray-400" x-text="day.dayOfWeek"></div>
                        </div>
                    </template>
                </div>

                {{-- 분기 뷰 --}}
                <div x-show="viewMode === 'quarter'" class="flex-1 flex bg-gray-50">
                    <template x-for="week in 12">
                        <div class="flex-1 p-2 text-center border-r border-gray-200 bg-blue-50">
                            <div class="text-xs text-blue-600 font-medium" x-text="week + '주'"></div>
                            <div class="text-xs text-blue-400" x-text="'Q' + Math.ceil(currentDate.getMonth()/3 + 1)"></div>
                        </div>
                    </template>
                </div>

                {{-- 년 뷰 --}}
                <div x-show="viewMode === 'year'" class="flex-1 flex bg-gray-50">
                    <template x-for="month in 12">
                        <div class="flex-1 p-2 text-center border-r border-gray-200 bg-green-50">
                            <div class="text-xs text-green-600 font-medium" x-text="month + '월'"></div>
                            <div class="text-xs text-green-400" x-text="currentDate.getFullYear()"></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 프로젝트 행들 --}}
            <div x-show="filteredProjects.length === 0 && projects.length === 0" class="p-8 text-center text-gray-500">
                <div class="mb-2">📊</div>
                <div class="font-medium mb-1">등록된 프로젝트가 없습니다</div>
                <div class="text-sm text-gray-400">우상단의 '+ 프로젝트 추가' 버튼을 클릭하여 새 프로젝트를 추가하세요</div>
            </div>
            <div x-show="filteredProjects.length === 0 && projects.length > 0" class="p-8 text-center text-gray-500">
                <div class="mb-2">🔍</div>
                <div class="font-medium mb-1">필터 조건에 맞는 프로젝트가 없습니다</div>
                <div class="text-sm text-gray-400">다른 검색어나 필터 조건을 시도해보세요</div>
            </div>
            <template x-for="(project, index) in filteredProjects" :key="project.id">
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

    {{-- 프로젝트 편집 사이드바 --}}
    <div x-show="sidebarOpen" 
         style="display: none;"
         class="fixed inset-0 bg-black bg-opacity-50 z-40" 
         @click="closeSidebar()"></div>

    <div x-show="sidebarOpen"
         class="fixed inset-y-0 right-0 z-50 w-96 bg-white shadow-xl transform transition-transform duration-300"
         :class="{ 'translate-x-0': sidebarOpen, 'translate-x-full': !sidebarOpen }"
        <div class="h-full flex flex-col" x-show="selectedProject">
            {{-- 사이드바 헤더 --}}
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">프로젝트 편집</h3>
                    <button @click="closeSidebar()" 
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- 사이드바 콘텐츠 --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                {{-- 프로젝트 기본 정보 --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">프로젝트명</label>
                    <input type="text"
                           :value="selectedProject?.name || ''"
                           @input="selectedProject ? selectedProject.name = $event.target.value : null"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                    <textarea :value="selectedProject?.description || ''"
                              @input="selectedProject ? selectedProject.description = $event.target.value : null"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                    <select :value="selectedProject?.status || 'planned'"
                            @change="selectedProject ? selectedProject.status = $event.target.value : null"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
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
                        <span x-text="(selectedProject?.progress || 0) + '%'" class="font-medium text-orange-600"></span>
                        <span>100%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                    <select :value="selectedProject?.priority || 'medium'"
                            @change="selectedProject ? selectedProject.priority = $event.target.value : null"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">팀원 수</label>
                    <input type="number"
                           :value="selectedProject?.team_members || 1"
                           @input="selectedProject ? selectedProject.team_members = $event.target.value : null"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                </div>

                {{-- 필수 컬럼들 --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">시작일</label>
                    <input type="date"
                           :value="selectedProject?.start_date || ''"
                           @input="selectedProject ? selectedProject.start_date = $event.target.value : null"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">종료일</label>
                    <input type="date"
                           :value="selectedProject?.end_date || ''"
                           @input="selectedProject ? selectedProject.end_date = $event.target.value : null"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
                </div>

                {{-- 추가 정보 --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">프로젝트 정보</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">프로젝트 ID:</span>
                            <span class="text-gray-900" x-text="selectedProject?.id || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">카테고리:</span>
                            <span class="text-gray-900" x-text="selectedProject?.category || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">기간:</span>
                            <span class="text-gray-900" x-text="calculateDuration(selectedProject?.start_date, selectedProject?.end_date)"></span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- 사이드바 푸터 --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex space-x-3">
                    <button @click="saveChanges()" 
                            class="flex-1 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 focus:ring-2 focus:ring-orange-500">
                        저장
                    </button>
                    <button @click="closeSidebar()" 
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:ring-2 focus:ring-gray-500">
                        취소
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 사이드바 오버레이 --}}
    <div x-show="sidebarOpen"
         style="display: none;"
         class="fixed inset-0 bg-black bg-opacity-25 z-40"
         @click="closeSidebar()"></div>

    {{-- 프로젝트 생성 모달 --}}
    <div x-show="createModalOpen"
         x-cloak
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="closeCreateModal()"
         style="display: none !important;"
         :style="createModalOpen ? 'display: flex !important;' : 'display: none !important;'">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">새 프로젝트 추가</h3>
                    <button @click="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                        <span class="text-xl">×</span>
                    </button>
                </div>

                <form @submit.prevent="createProject()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">프로젝트명 *</label>
                            <input type="text"
                                   x-model="newProject.name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="프로젝트명을 입력하세요">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">설명</label>
                            <textarea x-model="newProject.description"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                      placeholder="프로젝트 설명을 입력하세요"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">시작일 *</label>
                                <input type="date"
                                       x-model="newProject.start_date"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">종료일</label>
                                <input type="date"
                                       x-model="newProject.end_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                                <select x-model="newProject.status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="planned">계획</option>
                                    <option value="in-progress">진행 중</option>
                                    <option value="completed">완료</option>
                                    <option value="on-hold">보류</option>
                                    <option value="cancelled">취소</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">우선순위</label>
                                <select x-model="newProject.priority"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <option value="low">낮음</option>
                                    <option value="medium">보통</option>
                                    <option value="high">높음</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">클라이언트</label>
                            <input type="text"
                                   x-model="newProject.client"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="클라이언트명을 입력하세요">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">진행률 (%)</label>
                            <input type="number"
                                   x-model="newProject.progress"
                                   min="0" max="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                   placeholder="0">
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="submit"
                                class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md font-medium hover:bg-green-700 transition-colors">
                            프로젝트 생성
                        </button>
                        <button type="button"
                                @click="closeCreateModal()"
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
function ganttData() {
    return {
        projects: [],
        filteredProjects: [],
        stats: {
            total: 0,
            onTrack: 0,
            delayed: 0,
            completed: 0
        },
        currentDate: new Date(),
        monthDays: [],
        viewMode: 'month', // 'month', 'quarter', 'year'

        // 필터링 상태
        searchTerm: '',
        statusFilter: '',
        priorityFilter: '',

        // 사이드바 상태
        sidebarOpen: false,
        selectedProject: null,

        // 프로젝트 생성 모달 상태
        createModalOpen: false,
        newProject: {
            name: '',
            description: '',
            start_date: '',
            end_date: '',
            status: 'planned',
            priority: 'medium',
            client: '',
            progress: 0
        },

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
                const response = await fetch('/api/sandbox/storage-sandbox-template/projects');
                const result = await response.json();

                if (result.success && result.data) {
                    this.projects = result.data.projects || [];
                    this.applyFilters();
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

        setViewMode(mode) {
            this.viewMode = mode;
            console.log('View mode changed to:', mode);
        },

        // 필터링 기능
        applyFilters() {
            let filtered = [...this.projects];

            // 검색어 필터
            if (this.searchTerm.trim()) {
                const searchLower = this.searchTerm.toLowerCase();
                filtered = filtered.filter(project =>
                    (project.name && project.name.toLowerCase().includes(searchLower)) ||
                    (project.description && project.description.toLowerCase().includes(searchLower)) ||
                    (project.client && project.client.toLowerCase().includes(searchLower))
                );
            }

            // 상태 필터
            if (this.statusFilter) {
                filtered = filtered.filter(project => project.status === this.statusFilter);
            }

            // 우선순위 필터
            if (this.priorityFilter) {
                filtered = filtered.filter(project => project.priority === this.priorityFilter);
            }

            this.filteredProjects = filtered;
        },

        clearFilters() {
            this.searchTerm = '';
            this.statusFilter = '';
            this.priorityFilter = '';
            this.applyFilters();
        },

        // 프로젝트 생성 모달 관리
        openCreateModal() {
            this.createModalOpen = true;
            // 기본값 설정
            const today = new Date().toISOString().split('T')[0];
            this.newProject = {
                name: '',
                description: '',
                start_date: today,
                end_date: '',
                status: 'planned',
                priority: 'medium',
                client: '',
                progress: 0
            };
        },

        closeCreateModal() {
            this.createModalOpen = false;
            this.newProject = {
                name: '',
                description: '',
                start_date: '',
                end_date: '',
                status: 'planned',
                priority: 'medium',
                client: '',
                progress: 0
            };
        },

        async createProject() {
            if (!this.newProject.name.trim()) {
                alert('프로젝트명을 입력해주세요.');
                return;
            }

            try {
                const response = await fetch('/api/sandbox/storage-sandbox-template/projects', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.newProject)
                });

                const result = await response.json();

                if (result.success && result.data) {
                    // 새 프로젝트를 리스트에 추가
                    this.projects.push(result.data);
                    this.applyFilters();
                    this.calculateStats();
                    this.closeCreateModal();

                    alert('프로젝트가 성공적으로 생성되었습니다.');
                } else {
                    alert('프로젝트 생성에 실패했습니다: ' + (result.message || '알 수 없는 오류'));
                }
            } catch (error) {
                console.error('프로젝트 생성 오류:', error);
                alert('프로젝트 생성 중 오류가 발생했습니다.');
            }
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
                const response = await fetch(`/api/sandbox/storage-sandbox-template/projects/${project.id}`, {
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
                const response = await fetch(`/api/sandbox/storage-sandbox-template/projects/${this.selectedProject.id}`, {
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

            if (this.viewMode === 'month') {
                return this.getMonthViewBarStyle(project, startDate);
            } else if (this.viewMode === 'quarter') {
                return this.getQuarterViewBarStyle(project, startDate);
            } else if (this.viewMode === 'year') {
                return this.getYearViewBarStyle(project, startDate);
            }

            return 'display: none;';
        },

        getMonthViewBarStyle(project, startDate) {
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

                // 시작 위치 계산 (일별 32px)
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

        getQuarterViewBarStyle(project, startDate) {
            const currentYear = this.currentDate.getFullYear();
            const currentQuarter = Math.floor(this.currentDate.getMonth() / 3) + 1;
            const quarterStart = new Date(currentYear, (currentQuarter - 1) * 3, 1);
            const quarterEnd = new Date(currentYear, currentQuarter * 3, 0);

            let leftPosition = 0;
            let width = '8.333%'; // 1주당 100% / 12 = 8.333%

            if (project.end_date) {
                const endDate = new Date(project.end_date);

                // 프로젝트가 현재 분기에 포함되는지 확인
                if (endDate < quarterStart || startDate > quarterEnd) {
                    return 'display: none;';
                }

                // 효과적인 시작일과 종료일 계산
                const effectiveStart = startDate < quarterStart ? quarterStart : startDate;
                const effectiveEnd = endDate > quarterEnd ? quarterEnd : endDate;

                // 시작 위치 계산 (주별, 퍼센트)
                const startWeek = Math.floor((effectiveStart - quarterStart) / (7 * 24 * 60 * 60 * 1000));
                leftPosition = (startWeek / 12) * 100;

                // 너비 계산 (주 단위, 퍼센트)
                const diffTime = Math.abs(effectiveEnd - effectiveStart);
                const diffWeeks = Math.ceil(diffTime / (7 * 24 * 60 * 60 * 1000));
                const widthPercent = Math.max(diffWeeks / 12 * 100, 8.333);
                width = `${widthPercent}%`;

            } else {
                // 종료일이 없는 경우
                if (startDate < quarterStart || startDate > quarterEnd) {
                    return 'display: none;';
                }

                const startWeek = Math.floor((startDate - quarterStart) / (7 * 24 * 60 * 60 * 1000));
                leftPosition = (startWeek / 12) * 100;
                width = '8.333%';
            }

            return `left: ${leftPosition}%; width: ${width};`;
        },

        getYearViewBarStyle(project, startDate) {
            const currentYear = this.currentDate.getFullYear();
            const yearStart = new Date(currentYear, 0, 1);
            const yearEnd = new Date(currentYear, 11, 31);

            let leftPosition = 0;
            let width = '8.333%'; // 1월당 100% / 12 = 8.333%

            if (project.end_date) {
                const endDate = new Date(project.end_date);

                // 프로젝트가 현재 년도에 포함되는지 확인
                if (endDate < yearStart || startDate > yearEnd) {
                    return 'display: none;';
                }

                // 효과적인 시작일과 종료일 계산
                const effectiveStart = startDate < yearStart ? yearStart : startDate;
                const effectiveEnd = endDate > yearEnd ? yearEnd : endDate;

                // 시작 위치 계산 (월별, 퍼센트)
                const startMonth = effectiveStart.getMonth();
                leftPosition = (startMonth / 12) * 100;

                // 너비 계산 (월 단위, 퍼센트)
                const endMonth = effectiveEnd.getMonth();
                const diffMonths = endMonth - startMonth + 1;
                const widthPercent = Math.max(diffMonths / 12 * 100, 8.333);
                width = `${widthPercent}%`;

            } else {
                // 종료일이 없는 경우
                if (startDate < yearStart || startDate > yearEnd) {
                    return 'display: none;';
                }

                const startMonth = startDate.getMonth();
                leftPosition = (startMonth / 12) * 100;
                width = '8.333%';
            }

            return `left: ${leftPosition}%; width: ${width};`;
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
