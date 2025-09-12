{{-- 권한 관리 모던 인터페이스 --}}
<div class="permissions-management-container">
    {{-- Livewire 스코프 선택 컴포넌트 --}}
    @livewire('platform-admin.organization-selector')

    {{-- 상단 컨트롤 패널 --}}
    <div class="mb-6 bg-white rounded-lg shadow p-4" id="control-panel" style="display: none;">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            {{-- 검색 및 필터 --}}
            <div class="flex flex-1 space-x-4">
                <div class="relative flex-1 max-w-md">
                    <input type="text" id="permission-search" placeholder="권한 검색..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">모든 카테고리</option>
                </select>
            </div>
            
            {{-- 액션 버튼들 --}}
            <div class="flex space-x-3">
                <button onclick="toggleBulkMode()" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    일괄 관리
                </button>
                <button onclick="openCreatePermissionModal()" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    권한 생성
                </button>
                <button onclick="exportPermissions()" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    내보내기
                </button>
            </div>
        </div>
        
        {{-- 통계 정보 --}}
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-3 rounded-lg">
                <div class="text-sm font-medium text-blue-900">총 권한</div>
                <div class="text-2xl font-bold text-blue-700" id="total-permissions">0</div>
            </div>
            <div class="bg-green-50 p-3 rounded-lg">
                <div class="text-sm font-medium text-green-900">활성 역할</div>
                <div class="text-2xl font-bold text-green-700" id="active-roles">0</div>
            </div>
            <div class="bg-purple-50 p-3 rounded-lg">
                <div class="text-sm font-medium text-purple-900">카테고리</div>
                <div class="text-2xl font-bold text-purple-700" id="total-categories">0</div>
            </div>
        </div>
    </div>

    {{-- 권한 매트릭스 테이블 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden" id="permissions-matrix" style="display: none;">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">권한 매트릭스</h3>
            <p class="mt-1 text-sm text-gray-500">역할별 권한을 관리합니다. 체크박스를 클릭하여 권한을 할당하거나 제거할 수 있습니다.</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="permissions-matrix-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="sticky left-0 z-10 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                            권한
                        </th>
                        {{-- 역할 헤더는 JavaScript로 동적 생성 --}}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="permissions-matrix-body">
                    {{-- 권한 행들은 JavaScript로 동적 생성 --}}
                </tbody>
            </table>
        </div>
        
        {{-- 로딩 상태 --}}
        <div id="matrix-loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">권한 매트릭스 로딩 중...</span>
        </div>
        
        {{-- 빈 상태 --}}
        <div id="matrix-empty" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">권한이 없습니다</h3>
            <p class="mt-1 text-sm text-gray-500">새로운 권한을 생성해보세요.</p>
        </div>
    </div>

    {{-- 일괄 작업 패널 (숨김 상태) --}}
    <div id="bulk-actions-panel" class="hidden mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-blue-900">
                    <span id="selected-count">0</span>개 항목 선택됨
                </span>
            </div>
            <div class="flex space-x-3">
                <button onclick="bulkAssignToRole()" class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                    역할에 할당
                </button>
                <button onclick="bulkRemoveFromRole()" class="text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                    역할에서 제거
                </button>
                <button onclick="cancelBulkMode()" class="text-sm text-blue-600 hover:text-blue-800">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 스타일링 --}}
<style>
.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}

.btn-secondary {
    @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}

.permission-row {
    @apply hover:bg-gray-50 transition-colors duration-150;
}

.permission-checkbox {
    @apply h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded;
}

.category-header {
    @apply bg-gray-100 font-semibold text-gray-900;
}

#permissions-matrix-table th {
    @apply min-w-32;
}
</style>