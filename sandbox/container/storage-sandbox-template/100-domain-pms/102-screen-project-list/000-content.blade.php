{{-- 샌드박스 프로젝트 목록 템플릿 --}}
<?php 
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;
    

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
?><div class="min-h-screen bg-gray-50 p-6" 
     x-data="projectListData()" 
     x-init="loadProjects()"
     x-cloak>
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-green-600">📝</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">프로젝트 목록</h1>
                    <p class="text-gray-600">모든 프로젝트를 관리하고 추적하세요</p>
                </div>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                새 프로젝트 추가
            </button>
        </div>
    </div>

    {{-- 필터 및 검색 --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                <div class="relative">
                    <input type="text" placeholder="프로젝트 검색..." 
                           x-model="searchQuery"
                           @input="handleSearch"
                           class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg">
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                </div>
                <select x-model="statusFilter" @change="loadProjects()" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">모든 상태</option>
                    <option value="pending">대기</option>
                    <option value="in_progress">진행 중</option>
                    <option value="completed">완료</option>
                    <option value="on_hold">보류</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500" x-text="pagination.total + '개 프로젝트'">로딩 중...</span>
            </div>
        </div>
    </div>

    {{-- 프로젝트 카드들 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div x-show="loading" class="col-span-full text-center py-12">
            <div class="text-gray-500">프로젝트를 로딩 중...</div>
        </div>
        
        <div x-show="!loading && projects.length === 0" class="col-span-full text-center py-12">
            <div class="text-gray-500">검색 결과가 없습니다.</div>
        </div>
        
        <template x-for="project in projects" :key="project.id">
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="project.name"></h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full" 
                          :class="getStatusClass(project.status)" 
                          x-text="getStatusText(project.status)">
                    </span>
                </div>
                
                <p class="text-gray-600 text-sm mb-4" x-text="project.description || '설명이 없습니다.'">
                </p>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">진행률</span>
                        <span class="text-gray-500" x-text="project.progress + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" :style="`width: ${project.progress}%`"></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-gray-300 rounded-full"></div>
                        <span class="text-sm text-gray-600" x-text="'우선순위: ' + (project.priority || 'normal')"></span>
                    </div>
                    <div class="flex space-x-2">
                        <button @click="viewProject(project)" class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded">보기</button>
                        <button @click="editProject(project)" class="px-3 py-1 text-sm text-green-600 hover:bg-green-50 rounded">편집</button>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="text-xs text-gray-500">
                        생성일: <span x-text="formatDate(project.created_at)"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- 페이지네이션 --}}
    <div class="mt-8 flex justify-center" x-show="pagination.total > 0">
        <div class="flex space-x-2">
            <button @click="loadPreviousPage()" 
                    :disabled="!pagination.hasPrev"
                    :class="pagination.hasPrev ? 'hover:bg-gray-50' : 'opacity-50 cursor-not-allowed'"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                이전
            </button>
            
            <div class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg">
                <span x-text="currentPage"></span>
            </div>
            
            <button @click="loadNextPage()" 
                    :disabled="!pagination.hasNext"
                    :class="pagination.hasNext ? 'hover:bg-gray-50' : 'opacity-50 cursor-not-allowed'"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                다음
            </button>
        </div>
    </div>

    {{-- 새 프로젝트 추가 모달 --}}
    <div x-show="showAddModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="showAddModal = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">새 프로젝트 추가</h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="text-xl">&times;</span>
                </button>
            </div>
            <form @submit.prevent="addProject()">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">프로젝트 이름</label>
                    <input type="text" x-model="newProject.name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                    <textarea x-model="newProject.description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                    <select x-model="newProject.priority" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="low">낮음</option>
                        <option value="medium">보통</option>
                        <option value="high">높음</option>
                    </select>
                </div>
                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        프로젝트 추가
                    </button>
                    <button type="button" @click="showAddModal = false" 
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        취소
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 프로젝트 보기 모달 --}}
    <div x-show="showViewModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="showViewModal = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">프로젝트 상세 정보</h3>
                <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="text-xl">&times;</span>
                </button>
            </div>
            <div x-show="selectedProject">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">프로젝트 이름</label>
                        <p class="text-gray-900" x-text="selectedProject?.name"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full" 
                              :class="getStatusClass(selectedProject?.status)" 
                              x-text="getStatusText(selectedProject?.status)"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">우선순위</label>
                        <p class="text-gray-900" x-text="selectedProject?.priority || 'normal'"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">생성일</label>
                        <p class="text-gray-900" x-text="formatDate(selectedProject?.created_at)"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">설명</label>
                    <p class="text-gray-900" x-text="selectedProject?.description || '설명이 없습니다.'"></p>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">진행률</label>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" :style="`width: ${selectedProject?.progress || 0}%`"></div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1" x-text="`${selectedProject?.progress || 0}% 완료`"></p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button @click="editProject(selectedProject)" 
                        class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                    편집하기
                </button>
                <button @click="showViewModal = false" 
                        class="bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                    닫기
                </button>
            </div>
        </div>
    </div>

    {{-- 프로젝트 편집 사이드바 --}}
    {{-- 배경 오버레이 --}}
    <div x-show="showEditModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-20 z-40"
         @click="showEditModal = false">
    </div>
    
    {{-- 편집 사이드바 --}}
    <div x-show="showEditModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform translate-x-full"
         class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-xl z-50 overflow-y-auto">
        <div class="p-6">
            {{-- 헤더 --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="text-green-600 text-sm">✏️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">프로젝트 편집</h3>
                </div>
                <button @click="showEditModal = false" 
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- 편집 폼 --}}
            <form @submit.prevent="updateProject()" x-show="editingProject.id" class="space-y-6">
                {{-- 프로젝트 이름 --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">프로젝트 이름</label>
                    <input type="text" x-model="editingProject.name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                {{-- 설명 --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">프로젝트 설명</label>
                    <textarea x-model="editingProject.description" rows="4"
                              placeholder="프로젝트에 대한 자세한 설명을 입력하세요..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                </div>

                {{-- 상태와 우선순위 (2열 레이아웃) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">상태</label>
                        <select x-model="editingProject.status" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="pending">대기</option>
                            <option value="in_progress">진행 중</option>
                            <option value="completed">완료</option>
                            <option value="on_hold">보류</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">우선순위</label>
                        <select x-model="editingProject.priority" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="low">낮음</option>
                            <option value="medium">보통</option>
                            <option value="high">높음</option>
                        </select>
                    </div>
                </div>

                {{-- 진행률 슬라이더 --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">진행률</label>
                    <div class="space-y-3">
                        <input type="range" x-model="editingProject.progress" min="0" max="100" 
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">0%</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium" 
                                  x-text="editingProject.progress + '%'"></span>
                            <span class="text-sm text-gray-500">100%</span>
                        </div>
                    </div>
                </div>

                {{-- 진행률 바 미리보기 --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">진행 상황 미리보기</label>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full transition-all duration-300" 
                             :style="`width: ${editingProject.progress}%`"></div>
                    </div>
                </div>

                {{-- 생성일 (읽기 전용) --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">생성일</label>
                    <input type="text" :value="formatDate(editingProject.created_at)" readonly
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-600">
                </div>

                {{-- 액션 버튼들 --}}
                <div class="flex flex-col space-y-3 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        프로젝트 저장
                    </button>
                    <button type="button" @click="showEditModal = false" 
                            class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        취소
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function projectListData() {
    return {
        projects: [],
        pagination: {
            total: 0,
            limit: 20,
            offset: 0,
            hasNext: false,
            hasPrev: false
        },
        searchQuery: '',
        statusFilter: '',
        loading: false,
        searchTimeout: null,
        
        // 모달 상태
        showAddModal: false,
        showViewModal: false,
        showEditModal: false,
        
        // 모달 데이터
        newProject: {
            name: '',
            description: '',
            priority: 'medium'
        },
        selectedProject: null,
        editingProject: {
            id: null,
            name: '',
            description: '',
            status: 'pending',
            priority: 'medium',
            progress: 0
        },
        
        get currentPage() {
            return Math.floor(this.pagination.offset / this.pagination.limit) + 1;
        },
        
        async loadProjects() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    limit: this.pagination.limit,
                    offset: this.pagination.offset
                });
                
                if (this.searchQuery.trim()) {
                    params.append('search', this.searchQuery.trim());
                }
                
                if (this.statusFilter) {
                    params.append('status', this.statusFilter);
                }
                
                // 현재 URL에서 샌드박스 템플릿 추출 (예: /sandbox/{sandbox}/...)
                const pathParts = window.location.pathname.split('/');
                const sandboxIndex = pathParts.indexOf('sandbox');
                const sandboxTemplate = pathParts[sandboxIndex + 1];
                
                const response = await fetch(`/api/sandbox/${sandboxTemplate}/projects?${params}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    this.projects = result.data.projects;
                    this.pagination = result.data.pagination;
                } else {
                    console.error('프로젝트 API 응답 오류:', result.message || result);
                    this.projects = [];
                }
            } catch (error) {
                console.error('프로젝트 목록 로딩 실패:', error);
                this.projects = [];
            } finally {
                this.loading = false;
            }
        },
        
        handleSearch() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            this.searchTimeout = setTimeout(() => {
                this.pagination.offset = 0; // 검색 시 첫 페이지로 이동
                this.loadProjects();
            }, 500); // 500ms 디바운스
        },
        
        loadNextPage() {
            if (this.pagination.hasNext) {
                this.pagination.offset += this.pagination.limit;
                this.loadProjects();
            }
        },
        
        loadPreviousPage() {
            if (this.pagination.hasPrev) {
                this.pagination.offset = Math.max(0, this.pagination.offset - this.pagination.limit);
                this.loadProjects();
            }
        },
        
        viewProject(project) {
            this.selectedProject = project;
            this.showViewModal = true;
            this.showEditModal = false;
        },
        
        editProject(project) {
            this.editingProject = { ...project }; // 복사본 생성
            this.showEditModal = true;
            this.showViewModal = false;
        },

        addProject() {
            // 새 프로젝트 추가 API 호출 (실제로는 서버에 저장)
            const newProject = {
                id: Date.now(), // 임시 ID
                name: this.newProject.name,
                description: this.newProject.description,
                priority: this.newProject.priority,
                status: 'pending',
                progress: 0,
                created_at: new Date().toISOString()
            };
            
            // 로컬 프로젝트 목록에 추가
            this.projects.unshift(newProject);
            this.pagination.total += 1;
            
            // 폼 초기화 및 모달 닫기
            this.newProject = { name: '', description: '', priority: 'medium' };
            this.showAddModal = false;
            
            console.log('새 프로젝트 추가됨:', newProject);
        },

        updateProject() {
            if (!this.editingProject.id) return;
            
            // 프로젝트 업데이트 API 호출 (실제로는 서버에 저장)
            const projectIndex = this.projects.findIndex(p => p.id === this.editingProject.id);
            if (projectIndex !== -1) {
                // 보기 모달의 선택된 프로젝트도 업데이트
                if (this.selectedProject && this.selectedProject.id === this.editingProject.id) {
                    this.selectedProject = { ...this.editingProject };
                }
                
                this.projects[projectIndex] = { ...this.editingProject };
                console.log('프로젝트 업데이트됨:', this.editingProject);
            }
            
            // 모달 닫기 및 폼 초기화
            this.showEditModal = false;
            this.editingProject = {
                id: null,
                name: '',
                description: '',
                status: 'pending',
                priority: 'medium',
                progress: 0
            };
        },
        
        getStatusClass(status) {
            const statusClasses = {
                'pending': 'bg-gray-100 text-gray-800',
                'in_progress': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'on_hold': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusText(status) {
            const statusTexts = {
                'pending': '대기',
                'in_progress': '진행 중',
                'completed': '완료',
                'on_hold': '보류',
                'cancelled': '취소'
            };
            return statusTexts[status] || status;
        },
        
        formatDate(datetime) {
            if (!datetime) return '알 수 없음';
            const date = new Date(datetime);
            return date.toLocaleDateString('ko-KR');
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>