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
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
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
                    <option value="in-progress">진행 중</option>
                    <option value="completed">완료</option>
                    <option value="on-hold">보류</option>
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
                
                // 현재 URL에서 샌드박스 템플릿 추출 (예: /sandbox/storage-sandbox-template/...)
                const pathParts = window.location.pathname.split('/');
                const sandboxIndex = pathParts.indexOf('sandbox');
                const sandboxTemplate = sandboxIndex !== -1 && pathParts[sandboxIndex + 1] ? pathParts[sandboxIndex + 1] : 'storage-sandbox-template';
                
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
            console.log('프로젝트 보기:', project);
            // 프로젝트 상세 화면으로 이동하는 로직 추가
        },
        
        editProject(project) {
            console.log('프로젝트 편집:', project);
            // 프로젝트 편집 화면으로 이동하는 로직 추가
        },
        
        getStatusClass(status) {
            const statusClasses = {
                'pending': 'bg-gray-100 text-gray-800',
                'in-progress': 'bg-blue-100 text-blue-800',
                'completed': 'bg-green-100 text-green-800',
                'on-hold': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },
        
        getStatusText(status) {
            const statusTexts = {
                'pending': '대기',
                'in-progress': '진행 중',
                'completed': '완료',
                'on-hold': '보류',
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