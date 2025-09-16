{{-- PMS 요약 요청 목록 화면 --}}
<?php
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;
    

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-emerald-50 to-teal-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <span class="text-emerald-600">📋</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">PMS 요약 요청 목록</h1>
                    <p class="text-gray-600">프로젝트 관리 시스템 요약 요청 현황을 확인하고 관리하세요</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshSummaryRequests()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">새로고침</button>
                <button onclick="createNewSummaryRequest()" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">새 요약 요청</button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto">

        <!-- 통계 카드 -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">대기 중</p>
                        <p class="text-lg font-semibold text-gray-900" id="pending-count">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">처리 중</p>
                        <p class="text-lg font-semibold text-gray-900" id="processing-count">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">완료</p>
                        <p class="text-lg font-semibold text-gray-900" id="completed-count">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.866 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">실패</p>
                        <p class="text-lg font-semibold text-gray-900" id="failed-count">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 검색 및 필터 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                    <input type="text" id="search-input" placeholder="제목 또는 설명 검색..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                    <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">모든 상태</option>
                        <option value="pending">대기</option>
                        <option value="processing">처리중</option>
                        <option value="completed">완료</option>
                        <option value="failed">실패</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                    <select id="priority-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">모든 우선순위</option>
                        <option value="low">낮음</option>
                        <option value="medium">보통</option>
                        <option value="high">높음</option>
                        <option value="urgent">긴급</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">정렬</label>
                    <select id="sort-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="requested_at_desc">최신순</option>
                        <option value="requested_at_asc">오래된순</option>
                        <option value="title_asc">제목순 (ㄱ-ㅎ)</option>
                        <option value="title_desc">제목순 (ㅎ-ㄱ)</option>
                        <option value="priority_desc">우선순위순</option>
                        <option value="status_asc">상태순</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">표시 개수</label>
                    <select id="per-page-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="10">10개</option>
                        <option value="25">25개</option>
                        <option value="50">50개</option>
                        <option value="100">100개</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    총 <span id="total-requests-count">0</span>개 요청
                </div>
                <button type="button" onclick="clearFilters()" class="text-emerald-600 hover:text-emerald-800 text-sm font-medium">
                    필터 초기화
                </button>
            </div>
        </div>

        <!-- PMS 요약 요청 목록 -->
        <div class="bg-white rounded-lg shadow-md">
            <!-- 테이블 헤더 -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">PMS 요약 요청 목록</span>
                </div>
            </div>

            <!-- 요청 목록 컨테이너 -->
            <div id="requests-container" class="divide-y divide-gray-200">
                <!-- 로딩 상태 -->
                <div id="loading-state" class="px-6 py-12 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500 mx-auto mb-4"></div>
                    <p class="text-gray-500">PMS 요약 요청 목록을 불러오는 중...</p>
                </div>

                <!-- 빈 상태 -->
                <div id="empty-state" class="px-6 py-12 text-center" style="display: none;">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 mb-4">PMS 요약 요청이 없습니다.</p>
                    <button onclick="createNewSummaryRequest()" class="text-emerald-600 hover:text-emerald-800 font-medium">
                        새 요약 요청 만들기
                    </button>
                </div>
            </div>

            <!-- 페이지네이션 -->
            <div id="pagination-container" class="px-6 py-4 border-t border-gray-200 bg-gray-50" style="display: none;">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        <span id="page-info">1-10 / 0</span>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" id="prev-page" class="px-3 py-1 text-sm border border-gray-300 rounded-md bg-white hover:bg-gray-50 disabled:opacity-50" disabled>
                            이전
                        </button>
                        <button type="button" id="next-page" class="px-3 py-1 text-sm border border-gray-300 rounded-md bg-white hover:bg-gray-50 disabled:opacity-50" disabled>
                            다음
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PMS 요약 결과 상세 모달 -->
<div id="summary-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modal-title">PMS 요약 결과</h3>
            <button type="button" onclick="closeSummaryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modal-content" class="space-y-4">
            <!-- 요약 결과가 여기에 표시됩니다 -->
        </div>
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeSummaryModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                닫기
            </button>
        </div>
    </div>
</div>

<!-- 새 요약 요청 생성 모달 -->
<div id="create-request-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">새 PMS 요약 요청</h3>
            <button type="button" onclick="closeCreateRequestModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="create-request-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">제목 *</label>
                <input type="text" id="request-title" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                       placeholder="PMS 요약 요청 제목을 입력하세요">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                <textarea id="request-description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                          placeholder="요약이 필요한 내용에 대한 상세한 설명을 입력하세요"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                    <select id="request-priority" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="low">낮음</option>
                        <option value="medium" selected>보통</option>
                        <option value="high">높음</option>
                        <option value="urgent">긴급</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">요청 유형</label>
                    <select id="request-type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="project_summary">프로젝트 요약</option>
                        <option value="task_summary">업무 요약</option>
                        <option value="progress_summary">진행상황 요약</option>
                        <option value="issue_summary">이슈 요약</option>
                        <option value="report_summary">보고서 요약</option>
                        <option value="meeting_summary">회의 요약</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">기간 설정</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">시작일</label>
                        <input type="date" id="request-start-date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">종료일</label>
                        <input type="date" id="request-end-date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>
        </form>
        
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeCreateRequestModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                취소
            </button>
            <button type="button" onclick="submitSummaryRequest()" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md hover:bg-emerald-700">
                요청 생성
            </button>
        </div>
    </div>
</div>

<script>
let currentRequests = [];
let currentPage = 1;
let totalPages = 1;
let perPage = 10;
let searchQuery = '';
let statusFilter = '';
let priorityFilter = '';
let sortBy = 'requested_at_desc';

document.addEventListener('DOMContentLoaded', function() {
    loadSummaryRequests();
    loadStatistics();

    // 이벤트 리스너 설정
    document.getElementById('search-input').addEventListener('input', debounce(handleSearch, 300));
    document.getElementById('status-filter').addEventListener('change', handleFilterChange);
    document.getElementById('priority-filter').addEventListener('change', handleFilterChange);
    document.getElementById('sort-select').addEventListener('change', handleSortChange);
    document.getElementById('per-page-select').addEventListener('change', handlePerPageChange);
    document.getElementById('prev-page').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('next-page').addEventListener('click', () => changePage(currentPage + 1));
});

async function loadSummaryRequests() {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            search: searchQuery,
            status: statusFilter,
            priority: priorityFilter,
            sort: sortBy,
            limit: perPage,
            offset: (currentPage - 1) * perPage
        });

        const response = await fetch(`/api/sandbox/pms-summary-requests?${params}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('PMS 요약 요청 목록을 불러오는데 실패했습니다.');
        }

        const result = await response.json();
        currentRequests = result.success ? result.data : [];

        totalPages = result.total ? Math.ceil(result.total / perPage) : 1;

        renderSummaryRequests();
        updatePagination();
        updateStats(result.total || 0);
    } catch (error) {
        console.error('Error loading summary requests:', error);
        showNotification('PMS 요약 요청 목록을 불러오는데 실패했습니다.', 'error');
        showEmpty();
    }
}

async function loadStatistics() {
    try {
        const response = await fetch('/api/sandbox/pms-summary-statistics', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                const stats = result.data;
                document.getElementById('pending-count').textContent = stats.pending || 0;
                document.getElementById('processing-count').textContent = stats.processing || 0;
                document.getElementById('completed-count').textContent = stats.completed || 0;
                document.getElementById('failed-count').textContent = stats.failed || 0;
            }
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

function showLoading() {
    document.getElementById('loading-state').style.display = 'block';
    document.getElementById('empty-state').style.display = 'none';
}

function showEmpty() {
    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('empty-state').style.display = 'block';
}

function renderSummaryRequests() {
    const container = document.getElementById('requests-container');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    loadingState.style.display = 'none';

    if (currentRequests.length === 0) {
        emptyState.style.display = 'block';
        const requestListItems = container.querySelectorAll('.px-6.py-4.hover\\:bg-gray-50');
        requestListItems.forEach(item => item.remove());
        return;
    }

    emptyState.style.display = 'none';

    const requestsHtml = currentRequests.map(request => `
        <div class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    ${getPriorityIcon(request.priority)}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3 mb-2">
                        <h3 class="text-sm font-medium text-gray-900 truncate cursor-pointer hover:text-emerald-600"
                           onclick="showSummaryDetails(${request.id})">
                            ${request.title}
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadge(request.status)}">
                            ${getStatusLabel(request.status)}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPriorityBadge(request.priority)}">
                            ${getPriorityLabel(request.priority)}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-2">
                        ${request.description ? request.description.substring(0, 100) + (request.description.length > 100 ? '...' : '') : '설명 없음'}
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>유형: ${getTypeLabel(request.request_type)}</span>
                        <span>요청: ${formatDate(request.requested_at)}</span>
                        ${request.completed_at ? `<span>완료: ${formatDate(request.completed_at)}</span>` : ''}
                        ${request.start_date && request.end_date ? `<span>기간: ${formatDate(request.start_date)} ~ ${formatDate(request.end_date)}</span>` : ''}
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    ${request.status === 'completed' ? `
                        <button type="button" onclick="showSummaryDetails(${request.id})"
                                class="text-green-600 hover:text-green-800 p-1"
                                title="요약 결과 보기">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    ` : ''}
                    <button type="button" onclick="editSummaryRequest(${request.id})"
                            class="text-blue-600 hover:text-blue-800 p-1"
                            title="수정">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button type="button" onclick="deleteSummaryRequest(${request.id})"
                            class="text-red-600 hover:text-red-800 p-1"
                            title="삭제">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    // 기존 요청 목록 항목들만 제거
    const requestListItems = container.querySelectorAll('.px-6.py-4.hover\\:bg-gray-50');
    requestListItems.forEach(item => item.remove());

    // 새로운 요청 목록 추가
    container.insertAdjacentHTML('beforeend', requestsHtml);
}

function getPriorityIcon(priority) {
    const icons = {
        'urgent': '<svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.866 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>',
        'high': '<svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
        'medium': '<svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'low': '<svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
    };
    return icons[priority] || icons['medium'];
}

function getStatusBadge(status) {
    const badges = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
}

function getPriorityBadge(priority) {
    const badges = {
        'urgent': 'bg-red-100 text-red-800',
        'high': 'bg-orange-100 text-orange-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-green-100 text-green-800'
    };
    return badges[priority] || 'bg-gray-100 text-gray-800';
}

function getStatusLabel(status) {
    const labels = {
        'pending': '대기',
        'processing': '처리중',
        'completed': '완료',
        'failed': '실패'
    };
    return labels[status] || status;
}

function getPriorityLabel(priority) {
    const labels = {
        'urgent': '긴급',
        'high': '높음',
        'medium': '보통',
        'low': '낮음'
    };
    return labels[priority] || priority;
}

function getTypeLabel(type) {
    const labels = {
        'project_summary': '프로젝트 요약',
        'task_summary': '업무 요약',
        'progress_summary': '진행상황 요약',
        'issue_summary': '이슈 요약',
        'report_summary': '보고서 요약',
        'meeting_summary': '회의 요약'
    };
    return labels[type] || type;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ko-KR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function handleSearch() {
    searchQuery = document.getElementById('search-input').value.trim();
    currentPage = 1;
    loadSummaryRequests();
}

function handleFilterChange() {
    statusFilter = document.getElementById('status-filter').value;
    priorityFilter = document.getElementById('priority-filter').value;
    currentPage = 1;
    loadSummaryRequests();
}

function handleSortChange() {
    sortBy = document.getElementById('sort-select').value;
    currentPage = 1;
    loadSummaryRequests();
}

function handlePerPageChange() {
    perPage = parseInt(document.getElementById('per-page-select').value);
    currentPage = 1;
    loadSummaryRequests();
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('priority-filter').value = '';
    document.getElementById('sort-select').value = 'requested_at_desc';

    searchQuery = '';
    statusFilter = '';
    priorityFilter = '';
    sortBy = 'requested_at_desc';
    currentPage = 1;

    loadSummaryRequests();
}

function updatePagination() {
    const paginationContainer = document.getElementById('pagination-container');
    const pageInfo = document.getElementById('page-info');
    const prevBtn = document.getElementById('prev-page');
    const nextBtn = document.getElementById('next-page');

    if (totalPages <= 1) {
        paginationContainer.style.display = 'none';
        return;
    }

    paginationContainer.style.display = 'block';
    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, currentRequests.length);
    pageInfo.textContent = `${start}-${end} / ${currentRequests.length}`;

    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
}

function updateStats(total) {
    const totalCount = document.getElementById('total-requests-count');
    if (totalCount) {
        totalCount.textContent = total;
    }
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadSummaryRequests();
}

// 모달 함수들
function createNewSummaryRequest() {
    document.getElementById('create-request-modal').style.display = 'block';
    // 오늘 날짜를 기본값으로 설정
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('request-start-date').value = today;
}

function closeCreateRequestModal() {
    document.getElementById('create-request-modal').style.display = 'none';
    document.getElementById('create-request-form').reset();
}

async function submitSummaryRequest() {
    try {
        const title = document.getElementById('request-title').value.trim();
        const description = document.getElementById('request-description').value.trim();
        const priority = document.getElementById('request-priority').value;
        const type = document.getElementById('request-type').value;
        const startDate = document.getElementById('request-start-date').value;
        const endDate = document.getElementById('request-end-date').value;

        if (!title) {
            showNotification('제목을 입력해주세요.', 'warning');
            return;
        }

        const requestData = {
            title,
            description,
            priority,
            request_type: type,
            start_date: startDate || null,
            end_date: endDate || null
        };

        const response = await fetch('/api/sandbox/pms-summary-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            throw new Error('PMS 요약 요청 생성에 실패했습니다.');
        }

        const result = await response.json();
        
        if (result.success) {
            showNotification('PMS 요약 요청이 생성되었습니다.', 'success');
            closeCreateRequestModal();
            loadSummaryRequests();
            loadStatistics();
        } else {
            throw new Error(result.message || 'PMS 요약 요청 생성에 실패했습니다.');
        }
    } catch (error) {
        console.error('Error creating summary request:', error);
        showNotification('PMS 요약 요청 생성에 실패했습니다: ' + error.message, 'error');
    }
}

async function showSummaryDetails(requestId) {
    try {
        const response = await fetch(`/api/sandbox/pms-summary-requests/${requestId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('PMS 요약 결과를 불러오는데 실패했습니다.');
        }

        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'PMS 요약 결과를 불러오는데 실패했습니다.');
        }

        const request = result.data;
        
        const modal = document.getElementById('summary-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');

        modalTitle.textContent = `PMS 요약 결과 - ${request.title}`;

        modalContent.innerHTML = `
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">제목</label>
                    <p class="mt-1 text-sm text-gray-900">${request.title}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">상태</label>
                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadge(request.status)}">
                        ${getStatusLabel(request.status)}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">우선순위</label>
                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPriorityBadge(request.priority)}">
                        ${getPriorityLabel(request.priority)}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">유형</label>
                    <p class="mt-1 text-sm text-gray-900">${getTypeLabel(request.request_type)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">요청 시간</label>
                    <p class="mt-1 text-sm text-gray-900">${formatDate(request.requested_at)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">완료 시간</label>
                    <p class="mt-1 text-sm text-gray-900">${request.completed_at ? formatDate(request.completed_at) : '처리 중'}</p>
                </div>
            </div>
            ${request.description ? `
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                    <p class="text-sm text-gray-900">${request.description}</p>
                </div>
            ` : ''}
            ${request.start_date && request.end_date ? `
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">대상 기간</label>
                    <p class="text-sm text-gray-900">${formatDate(request.start_date)} ~ ${formatDate(request.end_date)}</p>
                </div>
            ` : ''}
            ${request.summary_result ? `
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">PMS 요약 결과</label>
                    <div class="bg-gray-50 rounded-md p-4 max-h-96 overflow-y-auto">
                        <pre class="text-sm text-gray-900 whitespace-pre-wrap">${request.summary_result}</pre>
                    </div>
                </div>
            ` : ''}
            ${request.error_message ? `
                <div class="mt-6">
                    <label class="block text-sm font-medium text-red-700 mb-2">오류 메시지</label>
                    <div class="bg-red-50 rounded-md p-4">
                        <p class="text-sm text-red-900">${request.error_message}</p>
                    </div>
                </div>
            ` : ''}
        `;

        modal.style.display = 'block';
    } catch (error) {
        console.error('Error loading summary details:', error);
        showNotification('PMS 요약 결과를 불러오는데 실패했습니다.', 'error');
    }
}

function closeSummaryModal() {
    document.getElementById('summary-modal').style.display = 'none';
}

async function deleteSummaryRequest(requestId) {
    if (!confirm('정말로 이 PMS 요약 요청을 삭제하시겠습니까?')) return;

    try {
        const response = await fetch(`/api/sandbox/pms-summary-requests/${requestId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('PMS 요약 요청 삭제에 실패했습니다.');
        }

        showNotification('PMS 요약 요청이 삭제되었습니다.', 'success');
        loadSummaryRequests();
        loadStatistics();
    } catch (error) {
        console.error('Error deleting summary request:', error);
        showNotification('PMS 요약 요청 삭제에 실패했습니다.', 'error');
    }
}

function editSummaryRequest(requestId) {
    showNotification('요청 수정 기능은 곧 구현될 예정입니다.', 'info');
}

function refreshSummaryRequests() {
    currentPage = 1;
    loadSummaryRequests();
    loadStatistics();
    showNotification('PMS 요약 요청 목록이 새로고침되었습니다.', 'success');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    } text-white`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
</div>