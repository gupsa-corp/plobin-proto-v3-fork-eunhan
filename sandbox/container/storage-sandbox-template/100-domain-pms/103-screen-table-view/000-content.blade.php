{{-- 샌드박스 테이블 뷰 템플릿 --}}
<?php
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
?>
<div class="min-h-screen bg-gray-50 p-6" x-data="tableViewData()" x-init="loadProjects()">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="text-blue-600">📋</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">프로젝트 테이블 뷰</h1>
                    <p class="text-gray-600">프로젝트 목록을 테이블 형태로 관리하세요</p>
                </div>
            </div>
            <button @click="showCreateModal = true" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                새 프로젝트 추가
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    📊
                </div>
                <div>
                    <p class="text-2xl font-semibold text-gray-900" x-text="stats.total"></p>
                    <p class="text-gray-600">총 프로젝트</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    🔄
                </div>
                <div>
                    <p class="text-2xl font-semibold text-gray-900" x-text="stats.in_progress"></p>
                    <p class="text-gray-600">진행 중</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    ✅
                </div>
                <div>
                    <p class="text-2xl font-semibold text-gray-900" x-text="stats.completed"></p>
                    <p class="text-gray-600">완료됨</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                    🔥
                </div>
                <div>
                    <p class="text-2xl font-semibold text-gray-900" x-text="stats.high_priority"></p>
                    <p class="text-gray-600">높은 우선순위</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-0">
                <input 
                    type="text" 
                    x-model="filters.search"
                    @input="debounceSearch()"
                    placeholder="프로젝트 검색..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <select 
                x-model="filters.status"
                @change="loadProjects()"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">모든 상태</option>
                <option value="pending">대기</option>
                <option value="in_progress">진행 중</option>
                <option value="on_hold">보류</option>
                <option value="completed">완료</option>
            </select>
            <select 
                x-model="filters.priority"
                @change="loadProjects()"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">모든 우선순위</option>
                <option value="low">낮음</option>
                <option value="medium">보통</option>
                <option value="high">높음</option>
            </select>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div x-show="loading" class="p-8 text-center">
            <div class="text-gray-500">로딩 중...</div>
        </div>
        
        <div x-show="!loading">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">프로젝트</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">우선순위</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">진행률</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">생성일</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="project in projects" :key="project.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="project.name"></div>
                                    <div class="text-sm text-gray-500" x-text="project.description || '설명 없음'"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getStatusClass(project.status)"
                                      x-text="getStatusText(project.status)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getPriorityClass(project.priority)"
                                      x-text="getPriorityText(project.priority)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 h-2 rounded-full" 
                                             :style="`width: ${project.progress || 0}%`"></div>
                                    </div>
                                    <span class="text-sm text-gray-600" x-text="`${project.progress || 0}%`"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="formatDate(project.created_at)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="editProject(project)" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">편집</button>
                                <button @click="deleteProject(project.id)" 
                                        class="text-red-600 hover:text-red-900">삭제</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div x-show="projects.length === 0 && !loading" class="p-8 text-center text-gray-500">
                프로젝트가 없습니다.
            </div>
        </div>

        {{-- Pagination --}}
        <div x-show="!loading && pagination.total > 0" class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="prevPage()" 
                        :disabled="!pagination.hasPrev"
                        :class="pagination.hasPrev ? 'text-blue-600 hover:text-blue-900' : 'text-gray-400 cursor-not-allowed'"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md">
                    이전
                </button>
                <button @click="nextPage()" 
                        :disabled="!pagination.hasNext"
                        :class="pagination.hasNext ? 'text-blue-600 hover:text-blue-900' : 'text-gray-400 cursor-not-allowed'"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md">
                    다음
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        총 <span class="font-medium" x-text="pagination.total"></span>개 중 
                        <span class="font-medium" x-text="pagination.offset + 1"></span>-<span class="font-medium" x-text="Math.min(pagination.offset + pagination.limit, pagination.total)"></span>개 표시
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <button @click="prevPage()" 
                                :disabled="!pagination.hasPrev"
                                :class="pagination.hasPrev ? 'text-blue-600 hover:text-blue-900' : 'text-gray-400 cursor-not-allowed'"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 text-sm font-medium">
                            이전
                        </button>
                        <button @click="nextPage()" 
                                :disabled="!pagination.hasNext"
                                :class="pagination.hasNext ? 'text-blue-600 hover:text-blue-900' : 'text-gray-400 cursor-not-allowed'"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 text-sm font-medium">
                            다음
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit/Create Modal --}}
    <div x-show="showCreateModal || showEditModal" 
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         @click.self="closeModal()">
        
        <div x-show="showCreateModal || showEditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="bg-white rounded-lg shadow-xl w-full max-w-md">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" 
                    x-text="showCreateModal ? '새 프로젝트 추가' : '프로젝트 편집'"></h3>
                <button @click="closeModal()" 
                        class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Content --}}
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">프로젝트 이름</label>
                        <input type="text" 
                               x-model="formData.name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="프로젝트 이름을 입력하세요...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                        <textarea x-model="formData.description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                  placeholder="프로젝트 설명을 입력하세요..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                            <select x-model="formData.status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="pending">대기</option>
                                <option value="in_progress">진행 중</option>
                                <option value="on_hold">보류</option>
                                <option value="completed">완료</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                            <select x-model="formData.priority" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="low">낮음</option>
                                <option value="medium">보통</option>
                                <option value="high">높음</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            진행률 (<span x-text="formData.progress || 0"></span>%)
                        </label>
                        <input type="range" 
                               x-model="formData.progress"
                               min="0" 
                               max="100" 
                               step="5"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button @click="closeModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    취소
                </button>
                <button @click="saveProject()" 
                        :disabled="!formData.name.trim() || saving"
                        :class="formData.name.trim() && !saving ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                        class="px-4 py-2 text-white rounded-lg font-medium transition-colors">
                    <span x-show="!saving" x-text="showCreateModal ? '추가' : '저장'"></span>
                    <span x-show="saving">저장 중...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function tableViewData() {
    return {
        projects: [],
        loading: false,
        filters: {
            search: '',
            status: '',
            priority: ''
        },
        pagination: {
            total: 0,
            offset: 0,
            limit: 20,
            hasNext: false,
            hasPrev: false
        },
        stats: {
            total: 0,
            in_progress: 0,
            completed: 0,
            high_priority: 0
        },
        showCreateModal: false,
        showEditModal: false,
        formData: {
            id: null,
            name: '',
            description: '',
            status: 'pending',
            priority: 'medium',
            progress: 0
        },
        saving: false,
        searchTimeout: null,
        
        async loadProjects() {
            this.loading = true;
            try {
                // Extract sandbox template from URL
                const pathParts = window.location.pathname.split('/');
                const sandboxIndex = pathParts.indexOf('sandbox');
                const sandboxTemplate = sandboxIndex !== -1 && pathParts[sandboxIndex + 1] ? pathParts[sandboxIndex + 1] : 'storage-sandbox-template';
                
                const params = new URLSearchParams({
                    limit: this.pagination.limit,
                    offset: this.pagination.offset,
                    search: this.filters.search,
                    status: this.filters.status,
                    priority: this.filters.priority
                });

                const response = await fetch(`/api/sandbox/${sandboxTemplate}/projects?${params}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    this.projects = result.data.projects;
                    this.pagination = result.data.pagination;
                    this.calculateStats();
                } else {
                    console.error('Projects API 오류:', result.message);
                    this.projects = [];
                }
            } catch (error) {
                console.error('프로젝트 로딩 실패:', error);
                this.projects = [];
            } finally {
                this.loading = false;
            }
        },
        
        calculateStats() {
            this.stats = {
                total: this.pagination.total,
                in_progress: this.projects.filter(p => p.status === 'in_progress').length,
                completed: this.projects.filter(p => p.status === 'completed').length,
                high_priority: this.projects.filter(p => p.priority === 'high').length
            };
        },
        
        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.offset = 0;
                this.loadProjects();
            }, 500);
        },
        
        nextPage() {
            if (this.pagination.hasNext) {
                this.pagination.offset += this.pagination.limit;
                this.loadProjects();
            }
        },
        
        prevPage() {
            if (this.pagination.hasPrev) {
                this.pagination.offset = Math.max(0, this.pagination.offset - this.pagination.limit);
                this.loadProjects();
            }
        },
        
        editProject(project) {
            this.formData = {
                id: project.id,
                name: project.name,
                description: project.description || '',
                status: project.status,
                priority: project.priority,
                progress: project.progress || 0
            };
            this.showEditModal = true;
        },
        
        async saveProject() {
            if (!this.formData.name.trim()) return;
            
            this.saving = true;
            try {
                // Extract sandbox template from URL
                const pathParts = window.location.pathname.split('/');
                const sandboxIndex = pathParts.indexOf('sandbox');
                const sandboxTemplate = sandboxIndex !== -1 && pathParts[sandboxIndex + 1] ? pathParts[sandboxIndex + 1] : 'storage-sandbox-template';
                
                const isEdit = this.showEditModal;
                const url = isEdit 
                    ? `/api/sandbox/${sandboxTemplate}/projects/${this.formData.id}`
                    : `/api/sandbox/${sandboxTemplate}/projects`;
                    
                const method = isEdit ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.formData.name,
                        description: this.formData.description,
                        status: this.formData.status,
                        priority: this.formData.priority,
                        progress: parseInt(this.formData.progress) || 0
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.closeModal();
                    this.loadProjects();
                } else {
                    alert(isEdit ? '프로젝트 수정에 실패했습니다.' : '프로젝트 생성에 실패했습니다.');
                }
            } catch (error) {
                console.error('프로젝트 저장 오류:', error);
                alert('프로젝트 저장 중 오류가 발생했습니다.');
            } finally {
                this.saving = false;
            }
        },
        
        async deleteProject(id) {
            if (!confirm('정말로 이 프로젝트를 삭제하시겠습니까?')) return;
            
            try {
                // Extract sandbox template from URL
                const pathParts = window.location.pathname.split('/');
                const sandboxIndex = pathParts.indexOf('sandbox');
                const sandboxTemplate = sandboxIndex !== -1 && pathParts[sandboxIndex + 1] ? pathParts[sandboxIndex + 1] : 'storage-sandbox-template';
                
                const response = await fetch(`/api/sandbox/${sandboxTemplate}/projects/${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    this.loadProjects();
                } else {
                    alert('프로젝트 삭제에 실패했습니다.');
                }
            } catch (error) {
                console.error('프로젝트 삭제 오류:', error);
                alert('프로젝트 삭제 중 오류가 발생했습니다.');
            }
        },
        
        closeModal() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.formData = {
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
                'in_progress': 'bg-yellow-100 text-yellow-800',
                'on_hold': 'bg-orange-100 text-orange-800',
                'completed': 'bg-green-100 text-green-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusText(status) {
            const statusTexts = {
                'pending': '대기',
                'in_progress': '진행 중',
                'on_hold': '보류',
                'completed': '완료'
            };
            return statusTexts[status] || status;
        },
        
        getPriorityClass(priority) {
            const priorityClasses = {
                'low': 'bg-green-100 text-green-800',
                'medium': 'bg-yellow-100 text-yellow-800',
                'high': 'bg-red-100 text-red-800'
            };
            return priorityClasses[priority] || 'bg-gray-100 text-gray-800';
        },
        
        getPriorityText(priority) {
            const priorityTexts = {
                'low': '낮음',
                'medium': '보통',
                'high': '높음'
            };
            return priorityTexts[priority] || priority;
        },
        
        formatDate(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            return date.toLocaleDateString('ko-KR', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>