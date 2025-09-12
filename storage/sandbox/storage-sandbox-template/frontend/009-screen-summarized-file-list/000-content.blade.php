{{-- 샌드박스 AI 요약 파일 리스트 템플릿 --}}
<?php
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-purple-600">🤖</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">AI 요약 파일 목록</h1>
                    <p class="text-gray-600">AI로 요약된 파일들을 확인하고 관리하세요</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button class="px-3 py-1 text-sm bg-white shadow-sm rounded-md text-purple-600">요약 목록</button>
                    <a href="<?= getScreenUrl('frontend', '008-screen-uploaded-files-list') ?>"
                       class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-200 rounded-md">
                        파일 목록
                    </a>
                </div>
                <button onclick="refreshSummaries()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">새로고침</button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">

        <!-- 검색 및 필터 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                    <input type="text" id="search-input" placeholder="파일명 또는 설명 검색..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                    <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">모든 상태</option>
                        <option value="pending">대기중</option>
                        <option value="processing">처리중</option>
                        <option value="completed">완료</option>
                        <option value="failed">실패</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">정렬</label>
                    <select id="sort-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="requested_at_desc">최신 요청순</option>
                        <option value="requested_at_asc">오래된 요청순</option>
                        <option value="file_name_asc">파일명순 (ㄱ-ㅎ)</option>
                        <option value="file_name_desc">파일명순 (ㅎ-ㄱ)</option>
                        <option value="completed_at_desc">완료순</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">표시 개수</label>
                    <select id="per-page-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="10">10개</option>
                        <option value="25">25개</option>
                        <option value="50">50개</option>
                        <option value="100">100개</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    총 <span id="total-summaries-count">0</span>개 요약
                </div>
                <button type="button" onclick="clearFilters()" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                    필터 초기화
                </button>
            </div>
        </div>

        <!-- 요약 목록 -->
        <div class="bg-white rounded-lg shadow-md">
            <!-- 테이블 헤더 -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">AI 요약 목록</span>
                    <div class="ml-auto space-x-2">
                        <button type="button" onclick="bulkRefresh()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-1 px-3 rounded">
                            선택 항목 새로고침
                        </button>
                    </div>
                </div>
            </div>

            <!-- 요약 목록 컨테이너 -->
            <div id="summaries-container" class="divide-y divide-gray-200">
                <!-- 로딩 상태 -->
                <div id="loading-state" class="px-6 py-12 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500 mx-auto mb-4"></div>
                    <p class="text-gray-500">요약 목록을 불러오는 중...</p>
                </div>

                <!-- 빈 상태 -->
                <div id="empty-state" class="px-6 py-12 text-center" style="display: none;">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    <p class="text-gray-500 mb-4">AI 요약된 파일이 없습니다.</p>
                    <a href="<?= getScreenUrl('frontend', '008-screen-uploaded-files-list') ?>" class="text-purple-600 hover:text-purple-800 font-medium">
                        파일 목록에서 요약 요청하기
                    </a>
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

<!-- 요약 상세 모달 -->
<div id="summary-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modal-title">요약 상세 정보</h3>
            <button type="button" onclick="closeSummaryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modal-content" class="space-y-4">
            <!-- 요약 정보가 여기에 표시됩니다 -->
        </div>
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeSummaryModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                닫기
            </button>
        </div>
    </div>
</div>

<!-- 새 버전 추가 모달 -->
<div id="add-version-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">새 버전 추가</h3>
            <button type="button" onclick="closeAddVersionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">파일명</label>
                <p id="version-file-name" class="text-sm text-gray-900 bg-gray-50 p-2 rounded-md"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">새 요약 내용</label>
                <textarea id="new-summary-content" rows="8" placeholder="새로운 요약 내용을 입력하세요..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeAddVersionModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                취소
            </button>
            <button type="button" id="add-version-btn" onclick="submitNewVersion()" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700">
                버전 추가
            </button>
        </div>
    </div>
</div>

<script>
let currentSummaries = [];
let currentPage = 1;
let totalPages = 1;
let perPage = 10;
let searchQuery = '';
let statusFilter = '';
let sortBy = 'requested_at_desc';
let currentRequestId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadSummaries();

    // 이벤트 리스너 설정
    document.getElementById('search-input').addEventListener('input', debounce(handleSearch, 300));
    document.getElementById('status-filter').addEventListener('change', handleFilterChange);
    document.getElementById('sort-select').addEventListener('change', handleSortChange);
    document.getElementById('per-page-select').addEventListener('change', handlePerPageChange);
    document.getElementById('prev-page').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('next-page').addEventListener('click', () => changePage(currentPage + 1));
});

async function loadSummaries() {
    try {
        // API를 통해 실제 요약 목록 가져오기
        const response = await fetch('<?= getApiUrl("ai-summary-requests") ?>', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        let summaries = [];
        
        if (response.ok) {
            const result = await response.json();
            summaries = result.success ? result.data : [];
        } else {
            // API가 실패하거나 테이블이 없는 경우 로컬 스토리지에서 데이터 읽기
            console.warn('AI 요약 API를 사용할 수 없습니다. 로컬 스토리지에서 데이터를 읽습니다.');
            const localData = JSON.parse(localStorage.getItem('ai_summary_requests') || '[]');
            summaries = localData;
        }

        // 검색 및 필터 적용
        let filteredSummaries = summaries;

        if (searchQuery) {
            filteredSummaries = filteredSummaries.filter(summary =>
                summary.file_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                (summary.description && summary.description.toLowerCase().includes(searchQuery.toLowerCase()))
            );
        }

        if (statusFilter) {
            filteredSummaries = filteredSummaries.filter(summary => summary.status === statusFilter);
        }

        // 정렬 적용
        filteredSummaries.sort((a, b) => {
            switch (sortBy) {
                case 'requested_at_asc':
                    return new Date(a.requested_at) - new Date(b.requested_at);
                case 'file_name_asc':
                    return a.file_name.localeCompare(b.file_name);
                case 'file_name_desc':
                    return b.file_name.localeCompare(a.file_name);
                case 'completed_at_desc':
                    return new Date(b.completed_at || 0) - new Date(a.completed_at || 0);
                default:
                    return new Date(b.requested_at) - new Date(a.requested_at);
            }
        });

        // 페이지네이션 적용
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = startIndex + perPage;
        currentSummaries = filteredSummaries.slice(startIndex, endIndex);

        totalPages = Math.ceil(filteredSummaries.length / perPage);

        renderSummaries();
        updatePagination();
        updateStats(summaries);
    } catch (error) {
        console.error('Error loading summaries:', error);
        // 오류가 발생해도 빈 목록으로 처리하여 사용자에게 빈 상태 화면을 보여줌
        currentSummaries = [];
        renderSummaries();
        updatePagination();
        updateStats([]);
    }
}

function renderSummaries() {
    const container = document.getElementById('summaries-container');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const totalCount = document.getElementById('total-summaries-count');

    if (!container || !loadingState || !emptyState || !totalCount) {
        console.error('Required DOM elements not found');
        return;
    }

    loadingState.style.display = 'none';

    if (currentSummaries.length === 0) {
        emptyState.style.display = 'block';
        const summaryItems = container.querySelectorAll('.px-6.py-4.hover\\:bg-gray-50');
        summaryItems.forEach(item => item.remove());
        totalCount.textContent = '0';
        return;
    }

    emptyState.style.display = 'none';
    totalCount.textContent = currentSummaries.length;

    const summariesHtml = currentSummaries.map(summary => `
        <div class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    ${getStatusIcon(summary.status)}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                        <p class="text-sm font-medium text-gray-900 truncate cursor-pointer hover:text-purple-600"
                           onclick="showSummaryDetails(${summary.id})">
                            ${summary.file_name}
                        </p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadge(summary.status)}">
                            ${getStatusLabel(summary.status)}
                        </span>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>요청: ${formatDate(summary.requested_at)}</span>
                        ${summary.completed_at ? `<span>완료: ${formatDate(summary.completed_at)}</span>` : ''}
                        ${summary.description ? `<span>설명: ${summary.description.substring(0, 50)}${summary.description.length > 50 ? '...' : ''}</span>` : ''}
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="refreshSummary(${summary.id})"
                            class="text-blue-600 hover:text-blue-800 p-1" title="새로고침">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                    <button type="button" onclick="addNewVersion(${summary.id}, '${summary.file_name}')"
                            class="text-green-600 hover:text-green-800 p-1" title="새 버전 추가">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </button>
                    <button type="button" onclick="showSummaryDetails(${summary.id})"
                            class="text-purple-600 hover:text-purple-800 p-1" title="상세 보기">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    // 기존 요약 목록 항목들만 제거하고 loadingState, emptyState는 유지
    const summaryItems = container.querySelectorAll('.px-6.py-4.hover\\:bg-gray-50');
    summaryItems.forEach(item => item.remove());

    // 새로운 요약 목록 추가 (loadingState와 emptyState 뒤에)
    container.insertAdjacentHTML('beforeend', summariesHtml);
}

function getStatusIcon(status) {
    const iconClasses = {
        'pending': 'text-yellow-500',
        'processing': 'text-blue-500',
        'completed': 'text-green-500',
        'failed': 'text-red-500'
    };

    const iconClass = iconClasses[status] || 'text-gray-500';

    return `<svg class="h-8 w-8 ${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
    </svg>`;
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

function getStatusLabel(status) {
    const labels = {
        'pending': '대기중',
        'processing': '처리중',
        'completed': '완료',
        'failed': '실패'
    };
    return labels[status] || '알 수 없음';
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
    loadSummaries();
}

function handleFilterChange() {
    statusFilter = document.getElementById('status-filter').value;
    currentPage = 1;
    loadSummaries();
}

function handleSortChange() {
    sortBy = document.getElementById('sort-select').value;
    currentPage = 1;
    loadSummaries();
}

function handlePerPageChange() {
    perPage = parseInt(document.getElementById('per-page-select').value);
    currentPage = 1;
    loadSummaries();
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('sort-select').value = 'requested_at_desc';

    searchQuery = '';
    statusFilter = '';
    sortBy = 'requested_at_desc';
    currentPage = 1;

    loadSummaries();
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
    pageInfo.textContent = `${(currentPage - 1) * perPage + 1}-${Math.min(currentPage * perPage, currentSummaries.length)} / ${currentSummaries.length}`;

    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
}

function updateStats(allSummaries) {
    // 통계 업데이트 (필요시)
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadSummaries();
}

async function refreshSummaries() {
    showNotification('요약 목록을 새로고침하는 중...', 'info');
    await loadSummaries();
    showNotification('요약 목록이 새로고침되었습니다.', 'success');
}

async function refreshSummary(summaryId) {
    try {
        showNotification('요약을 새로고침하는 중...', 'info');
        
        const response = await fetch(`<?= getApiUrl("ai-summary-requests") ?>/${summaryId}/refresh`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (response.ok) {
            showNotification('요약이 새로고침되었습니다.', 'success');
            loadSummaries();
        } else {
            showNotification('요약 새로고침에 실패했습니다. API를 사용할 수 없습니다.', 'warning');
        }
    } catch (error) {
        console.error('Error refreshing summary:', error);
        showNotification('요약 새로고침에 실패했습니다.', 'error');
    }
}

async function showSummaryDetails(summaryId) {
    try {
        const summary = currentSummaries.find(s => s.id == summaryId);
        if (!summary) {
            showNotification('요약을 찾을 수 없습니다.', 'error');
            return;
        }

        // 요약 결과 조회
        let summaryResults = [];
        try {
            const response = await fetch(`<?= getApiUrl("ai-summary-results") ?>?request_id=${summary.request_id}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (response.ok) {
                const result = await response.json();
                summaryResults = result.success ? result.data : [];
            } else {
                console.warn('요약 결과 API를 사용할 수 없습니다.');
                summaryResults = [];
            }
        } catch (error) {
            console.warn('요약 결과 조회 중 오류:', error);
            // 로컬 스토리지에서 요약 결과 읽기
            const localResults = JSON.parse(localStorage.getItem('ai_summary_results') || '[]');
            summaryResults = localResults.filter(result => result.request_id == summary.request_id);
        }

        const modal = document.getElementById('summary-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');

        modalTitle.textContent = summary.file_name;

        let versionsHtml = '';
        if (summaryResults.length > 0) {
            versionsHtml = summaryResults.map(version => `
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-sm font-medium text-gray-900">버전 ${version.version}</h4>
                        <span class="text-xs text-gray-500">${formatDate(version.created_at)}</span>
                    </div>
                    <div class="text-sm text-gray-700 whitespace-pre-wrap">${version.summary_content}</div>
                </div>
            `).join('');
        } else {
            versionsHtml = '<p class="text-gray-500 text-center py-4">아직 요약 결과가 없습니다.</p>';
        }

        modalContent.innerHTML = `
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">파일명</label>
                    <p class="mt-1 text-sm text-gray-900">${summary.file_name}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">상태</label>
                    <p class="mt-1 text-sm text-gray-900">${getStatusLabel(summary.status)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">요청 시간</label>
                    <p class="mt-1 text-sm text-gray-900">${formatDate(summary.requested_at)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">완료 시간</label>
                    <p class="mt-1 text-sm text-gray-900">${summary.completed_at ? formatDate(summary.completed_at) : '미완료'}</p>
                </div>
            </div>
            ${summary.description ? `
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">설명</label>
                <p class="mt-1 text-sm text-gray-900">${summary.description}</p>
            </div>
            ` : ''}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">요약 버전들</label>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    ${versionsHtml}
                </div>
            </div>
        `;

        modal.style.display = 'block';
    } catch (error) {
        console.error('Error showing summary details:', error);
        showNotification('요약 상세 정보를 불러오는데 실패했습니다.', 'error');
    }
}

function closeSummaryModal() {
    document.getElementById('summary-modal').style.display = 'none';
}

function addNewVersion(requestId, fileName) {
    currentRequestId = requestId;
    document.getElementById('version-file-name').textContent = fileName;
    document.getElementById('new-summary-content').value = '';
    document.getElementById('add-version-modal').style.display = 'block';
}

function closeAddVersionModal() {
    document.getElementById('add-version-modal').style.display = 'none';
    currentRequestId = null;
}

async function submitNewVersion() {
    if (!currentRequestId) {
        showNotification('요청 ID를 찾을 수 없습니다.', 'error');
        return;
    }

    const content = document.getElementById('new-summary-content').value.trim();
    if (!content) {
        showNotification('요약 내용을 입력해주세요.', 'warning');
        return;
    }

    try {
        const version = generateVersionString();
        
        const requestData = {
            request_id: currentRequestId,
            version: version,
            summary_content: content
        };

        const response = await fetch('<?= getApiUrl("ai-summary-results") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(requestData)
        });

        if (response.ok) {
            showNotification('새 버전이 추가되었습니다.', 'success');
            closeAddVersionModal();
            loadSummaries();
        } else {
            // API가 실패한 경우 로컬 스토리지에 저장
            console.warn('새 버전 저장 API를 사용할 수 없습니다. 로컬 스토리지에 저장합니다.');
            const localResult = {
                id: Date.now(),
                request_id: currentRequestId,
                version: version,
                summary_content: content,
                status: 'success',
                created_at: new Date().toISOString()
            };
            
            const existingResults = JSON.parse(localStorage.getItem('ai_summary_results') || '[]');
            existingResults.push(localResult);
            localStorage.setItem('ai_summary_results', JSON.stringify(existingResults));
            
            showNotification('새 버전이 로컬에 저장되었습니다.', 'success');
            closeAddVersionModal();
            loadSummaries();
        }
    } catch (error) {
        console.error('Error adding new version:', error);
        showNotification('새 버전 추가에 실패했습니다.', 'error');
    }
}

function generateVersionString() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hour = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');
    const second = String(now.getSeconds()).padStart(2, '0');
    
    return `v${year}${month}${day}${hour}${minute}${second}`;
}

function bulkRefresh() {
    // 선택된 항목들 새로고침 (구현 필요)
    showNotification('선택된 항목들을 새로고침합니다.', 'info');
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
