{{-- 샌드박스 업로드 파일 리스트 템플릿 --}}
<?php
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-green-600">📋</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">업로드된 파일 목록</h1>
                    <p class="text-gray-600">업로드된 파일들을 검색하고 관리하세요</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="openUploadModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">새 파일 업로드</button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto">

        <!-- 검색 및 필터 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                    <input type="text" id="search-input" placeholder="파일명 검색..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">파일 형식</label>
                    <select id="type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">모든 형식</option>
                        <option value="image">이미지</option>
                        <option value="document">문서</option>
                        <option value="video">비디오</option>
                        <option value="audio">오디오</option>
                        <option value="archive">압축파일</option>
                        <option value="other">기타</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">정렬</label>
                    <select id="sort-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="uploaded_at_desc">최신순</option>
                        <option value="uploaded_at_asc">오래된순</option>
                        <option value="name_asc">이름순 (ㄱ-ㅎ)</option>
                        <option value="name_desc">이름순 (ㅎ-ㄱ)</option>
                        <option value="size_desc">크기순 (큰것부터)</option>
                        <option value="size_asc">크기순 (작은것부터)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">표시 개수</label>
                    <select id="per-page-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="10">10개</option>
                        <option value="25">25개</option>
                        <option value="50">50개</option>
                        <option value="100">100개</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    총 <span id="total-files-count">0</span>개 파일
                </div>
                <button type="button" onclick="clearFilters()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    필터 초기화
                </button>
            </div>
        </div>

        <!-- 파일 목록 -->
        <div class="bg-white rounded-lg shadow-md">
            <!-- 테이블 헤더 -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">파일 목록</span>
                </div>
            </div>

            <!-- 파일 목록 컨테이너 -->
            <div id="files-container" class="divide-y divide-gray-200">
                <!-- 로딩 상태 -->
                <div id="loading-state" class="px-6 py-12 text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p class="text-gray-500">파일 목록을 불러오는 중...</p>
                </div>

                <!-- 빈 상태 -->
                <div id="empty-state" class="px-6 py-12 text-center" style="display: none;">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500 mb-4">업로드된 파일이 없습니다.</p>
                    <a href="<?= getScreenUrl('frontend', '007-screen-multi-file-upload') ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                        파일 업로드하기
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

<!-- 파일 상세 모달 -->
<div id="file-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="modal-title">파일 정보</h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modal-content" class="space-y-4">
            <!-- 파일 정보가 여기에 표시됩니다 -->
        </div>
        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                닫기
            </button>
        </div>
    </div>
</div>

<!-- 파일 업로드 모달 -->
<div id="upload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">파일 업로드</h3>
            <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- 업로드 영역 -->
        <div class="mb-6">
            <!-- 드래그 앤 드롭 영역 -->
            <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors duration-200">
                <div class="space-y-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div>
                        <p class="text-gray-600">파일을 여기로 드래그하거나</p>
                        <button type="button" onclick="document.getElementById('file-input').click()" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            파일 선택
                        </button>
                    </div>
                    <p class="text-sm text-gray-500">최대 10MB, 모든 파일 형식 지원</p>
                </div>
            </div>

            <!-- 파일 입력 -->
            <input type="file" id="file-input" multiple style="display: none;">
        </div>

        <!-- 선택된 파일 목록 -->
        <div id="selected-files" class="mb-6" style="display: none;">
            <h4 class="text-md font-medium text-gray-900 mb-3">선택된 파일</h4>
            <div id="file-list" class="space-y-2 max-h-40 overflow-y-auto">
                <!-- 선택된 파일들이 여기에 표시됩니다 -->
            </div>
        </div>

        <!-- 업로드 진행률 -->
        <div id="upload-progress" class="mb-6" style="display: none;">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">업로드 진행률</span>
                <span id="progress-text" class="text-sm text-gray-500">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- 업로드 결과 메시지 -->
        <div id="upload-messages" class="mb-6">
            <!-- 성공/실패 메시지가 여기에 표시됩니다 -->
        </div>

        <!-- 버튼들 -->
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                취소
            </button>
            <button type="button" id="upload-btn" onclick="uploadFiles()" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700" disabled>
                업로드
            </button>
        </div>
    </div>
</div>

<script>
let currentFiles = [];
let currentPage = 1;
let totalPages = 1;
let perPage = 10;
let searchQuery = '';
let typeFilter = '';
let sortBy = 'uploaded_at_desc';

document.addEventListener('DOMContentLoaded', function() {
    loadFiles();

    // 이벤트 리스너 설정
    document.getElementById('search-input').addEventListener('input', debounce(handleSearch, 300));
    document.getElementById('type-filter').addEventListener('change', handleFilterChange);
    document.getElementById('sort-select').addEventListener('change', handleSortChange);
    document.getElementById('per-page-select').addEventListener('change', handlePerPageChange);
    document.getElementById('prev-page').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('next-page').addEventListener('click', () => changePage(currentPage + 1));
});

async function loadFiles() {
    try {
        // API를 통해 실제 파일 목록 가져오기
        const response = await fetch('/api/sandbox/sandbox-files', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('파일 목록을 불러오는데 실패했습니다.');
        }

        const result = await response.json();
        let sampleFiles = result.success ? result.data : [];

        // API가 실패하거나 데이터가 없으면 로컬 파일 시스템을 확인하는 백업 데이터
        if (!sampleFiles || sampleFiles.length === 0) {
            // downloads 디렉토리의 실제 파일 정보를 표시하기 위한 백업 데이터
            sampleFiles = <?= json_encode(getLocalFilesList()) ?>;
        }

        // 검색 및 필터 적용
        let filteredFiles = sampleFiles;

        if (searchQuery) {
            filteredFiles = filteredFiles.filter(file =>
                file.original_name.toLowerCase().includes(searchQuery.toLowerCase())
            );
        }

        if (typeFilter) {
            filteredFiles = filteredFiles.filter(file => {
                const category = getFileCategory(file.mime_type);
                return category === typeFilter;
            });
        }

        // 정렬 적용
        filteredFiles.sort((a, b) => {
            switch (sortBy) {
                case 'uploaded_at_asc':
                    return new Date(a.uploaded_at) - new Date(b.uploaded_at);
                case 'name_asc':
                    return a.original_name.localeCompare(b.original_name);
                case 'name_desc':
                    return b.original_name.localeCompare(a.original_name);
                case 'size_asc':
                    return a.file_size - b.file_size;
                case 'size_desc':
                    return b.file_size - a.file_size;
                default:
                    return new Date(b.uploaded_at) - new Date(a.uploaded_at);
            }
        });

        // 페이지네이션 적용
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = startIndex + perPage;
        currentFiles = filteredFiles.slice(startIndex, endIndex);

        totalPages = Math.ceil(filteredFiles.length / perPage);

        renderFiles();
        updatePagination();
        updateStats(sampleFiles);
    } catch (error) {
        console.error('Error loading files:', error);
        showNotification('파일 목록을 불러오는데 실패했습니다.', 'error');
    }
}

function renderFiles() {
    const container = document.getElementById('files-container');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const totalCount = document.getElementById('total-files-count');

    if (!container || !loadingState || !emptyState || !totalCount) {
        console.error('Required DOM elements not found');
        return;
    }

    loadingState.style.display = 'none';

    if (currentFiles.length === 0) {
        emptyState.style.display = 'block';
        // 파일 목록만 숨기고 empty state 표시
        const fileListItems = container.querySelectorAll('.px-6.py-4.hover\\:bg-gray-50');
        fileListItems.forEach(item => item.remove());
        totalCount.textContent = '0';
        return;
    }

    emptyState.style.display = 'none';
    totalCount.textContent = currentFiles.length;

    const filesHtml = currentFiles.map(file => `
        <div class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    ${getFileIcon(file.mime_type)}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                        <p class="text-sm font-medium text-gray-900 truncate cursor-pointer hover:text-blue-600"
                           onclick="showFileDetails(${file.id})">
                            ${file.original_name}
                        </p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getFileTypeBadge(file.mime_type)}">
                            ${getFileTypeLabel(file.mime_type)}
                        </span>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>${formatFileSize(file.file_size)}</span>
                        <span>업로드: ${formatDate(file.uploaded_at)}</span>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="requestAnalysis(${file.id})"
                            class="text-purple-600 hover:text-purple-800 p-1"
                            title="분석 요청">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </button>
                    <button type="button" onclick="downloadFile(${file.id})"
                            class="text-blue-600 hover:text-blue-800 p-1"
                            title="다운로드">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </button>
                    <button type="button" onclick="deleteFile(${file.id})"
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

    // 기존 파일 목록 항목들만 제거하고 loadingState, emptyState는 유지
    const fileListItems = container.querySelectorAll('.px-6.py-4.hover\\:bg-gray-50');
    fileListItems.forEach(item => item.remove());

    // 새로운 파일 목록 추가 (loadingState와 emptyState 뒤에)
    container.insertAdjacentHTML('beforeend', filesHtml);
}

function getFileIcon(mimeType) {
    const iconClasses = {
        'image': 'text-green-500',
        'video': 'text-purple-500',
        'audio': 'text-blue-500',
        'pdf': 'text-red-500',
        'document': 'text-blue-500',
        'archive': 'text-yellow-500'
    };

    const category = getFileCategory(mimeType);
    const iconClass = iconClasses[category] || 'text-gray-500';

    return `<svg class="h-8 w-8 ${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>`;
}

function getFileCategory(mimeType) {
    if (mimeType.startsWith('image/')) return 'image';
    if (mimeType.startsWith('video/')) return 'video';
    if (mimeType.startsWith('audio/')) return 'audio';
    if (mimeType === 'application/pdf') return 'pdf';
    if (mimeType.includes('document') || mimeType.includes('text')) return 'document';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return 'archive';
    return 'other';
}

function getFileTypeBadge(mimeType) {
    const category = getFileCategory(mimeType);
    const badges = {
        'image': 'bg-green-100 text-green-800',
        'video': 'bg-purple-100 text-purple-800',
        'audio': 'bg-blue-100 text-blue-800',
        'pdf': 'bg-red-100 text-red-800',
        'document': 'bg-blue-100 text-blue-800',
        'archive': 'bg-yellow-100 text-yellow-800',
        'other': 'bg-gray-100 text-gray-800'
    };
    return badges[category] || badges['other'];
}

function getFileTypeLabel(mimeType) {
    const category = getFileCategory(mimeType);
    const labels = {
        'image': '이미지',
        'video': '비디오',
        'audio': '오디오',
        'pdf': 'PDF',
        'document': '문서',
        'archive': '압축',
        'other': '기타'
    };
    return labels[category] || labels['other'];
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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
    loadFiles();
}

function handleFilterChange() {
    typeFilter = document.getElementById('type-filter').value;
    currentPage = 1;
    loadFiles();
}

function handleSortChange() {
    sortBy = document.getElementById('sort-select').value;
    currentPage = 1;
    loadFiles();
}

function handlePerPageChange() {
    perPage = parseInt(document.getElementById('per-page-select').value);
    currentPage = 1;
    loadFiles();
}


function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('type-filter').value = '';
    document.getElementById('sort-select').value = 'uploaded_at_desc';

    searchQuery = '';
    typeFilter = '';
    sortBy = 'uploaded_at_desc';
    currentPage = 1;

    loadFiles();
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
    pageInfo.textContent = `${(currentPage - 1) * perPage + 1}-${Math.min(currentPage * perPage, currentFiles.length)} / ${currentFiles.length}`;

    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
}

function updateStats(allFiles) {
    // DOM 요소들이 존재하는지 먼저 확인
    const totalFilesStat = document.getElementById('total-files-stat');
    const totalSizeStat = document.getElementById('total-size-stat');
    const imageFilesStat = document.getElementById('image-files-stat');
    const documentFilesStat = document.getElementById('document-files-stat');

    if (totalFilesStat) {
        totalFilesStat.textContent = allFiles.length;
    }

    if (totalSizeStat) {
        const totalSize = allFiles.reduce((sum, file) => sum + file.file_size, 0);
        totalSizeStat.textContent = formatFileSizeShort(totalSize);
    }

    if (imageFilesStat && documentFilesStat) {
        let imageCount = 0;
        let documentCount = 0;

        allFiles.forEach(file => {
            const category = getFileCategory(file.mime_type);
            if (category === 'image') {
                imageCount++;
            } else if (category === 'document' || category === 'pdf') {
                documentCount++;
            }
        });

        imageFilesStat.textContent = imageCount;
        documentFilesStat.textContent = documentCount;
    }
}

function formatFileSizeShort(bytes) {
    if (bytes === 0) return '0B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 10) / 10 + sizes[i];
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadFiles();
}

async function downloadFile(fileId) {
    try {
        // 현재 파일 목록에서 해당 파일 찾기
        const file = currentFiles.find(f => f.id == fileId);
        if (!file) {
            throw new Error('파일을 찾을 수 없습니다.');
        }

        // 직접 다운로드 URL로 이동 (로컬 파일 시스템)
        if (file.download_url) {
            // 새 창에서 다운로드
            const a = document.createElement('a');
            a.href = file.download_url;
            a.download = file.original_name;
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            showNotification('파일 다운로드가 시작되었습니다.', 'success');
        } else {
            // API를 통한 다운로드 시도
            const response = await fetch(`<?= getApiUrl("uploaded-files") ?>/${fileId}/download`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (!response.ok) {
                throw new Error('다운로드에 실패했습니다.');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = file.original_name;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            showNotification('파일 다운로드가 시작되었습니다.', 'success');
        }
    } catch (error) {
        console.error('Error downloading file:', error);
        showNotification('파일 다운로드에 실패했습니다.', 'error');
    }
}

async function deleteFile(fileId) {
    if (!confirm('정말로 이 파일을 삭제하시겠습니까?')) return;

    try {
        const response = await fetch(`<?= getApiUrl("files") ?>/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error('파일 삭제에 실패했습니다.');
        }

        showNotification('파일이 삭제되었습니다.', 'success');
        loadFiles();
    } catch (error) {
        console.error('Error deleting file:', error);
        showNotification('파일 삭제에 실패했습니다.', 'error');
    }
}

async function requestAnalysis(fileId) {
    const file = currentFiles.find(f => f.id == fileId);
    if (!file) {
        showNotification('파일을 찾을 수 없습니다.', 'error');
        return;
    }

    if (!confirm(`${file.original_name} 파일의 분석을 요청하시겠습니까?`)) return;

    try {
        const response = await fetch('/api/sandbox/analysis-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                file_id: fileId,
                file_name: file.original_name,
                file_path: file.file_path,
                mime_type: file.mime_type,
                file_size: file.file_size
            })
        });

        if (!response.ok) {
            throw new Error('분석 요청에 실패했습니다.');
        }

        const result = await response.json();
        
        if (result.success) {
            showNotification('분석 요청이 등록되었습니다.', 'success');
        } else {
            throw new Error(result.message || '분석 요청에 실패했습니다.');
        }
    } catch (error) {
        console.error('Error requesting analysis:', error);
        showNotification('분석 요청에 실패했습니다: ' + error.message, 'error');
    }
}


function showFileDetails(fileId) {
    const file = currentFiles.find(f => f.id == fileId);
    if (!file) return;

    const modal = document.getElementById('file-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');

    modalTitle.textContent = file.original_name;

    modalContent.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">파일명</label>
                <p class="mt-1 text-sm text-gray-900">${file.original_name}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">파일 크기</label>
                <p class="mt-1 text-sm text-gray-900">${formatFileSize(file.file_size)}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">MIME 타입</label>
                <p class="mt-1 text-sm text-gray-900">${file.mime_type}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">업로드 날짜</label>
                <p class="mt-1 text-sm text-gray-900">${formatDate(file.uploaded_at)}</p>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700">저장된 경로</label>
            <p class="mt-1 text-sm text-gray-900 break-all">${file.file_path}</p>
        </div>
    `;

    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('file-modal').style.display = 'none';
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

// 파일 업로드 모달 관련 변수
let selectedFiles = [];
let isUploading = false;

// 파일 업로드 모달 열기
function openUploadModal() {
    document.getElementById('upload-modal').style.display = 'block';
    resetUploadModal();
}

// 파일 업로드 모달 닫기
function closeUploadModal() {
    if (isUploading) {
        if (!confirm('업로드가 진행 중입니다. 정말로 취소하시겠습니까?')) {
            return;
        }
    }
    document.getElementById('upload-modal').style.display = 'none';
    resetUploadModal();
}

// 업로드 모달 초기화
function resetUploadModal() {
    selectedFiles = [];
    isUploading = false;
    document.getElementById('file-input').value = '';
    document.getElementById('selected-files').style.display = 'none';
    document.getElementById('upload-progress').style.display = 'none';
    document.getElementById('upload-messages').innerHTML = '';
    document.getElementById('upload-btn').disabled = true;
    updateProgressBar(0);
}

// 파일 선택 이벤트
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');

    // 파일 입력 변경 이벤트
    fileInput.addEventListener('change', function(e) {
        handleFileSelection(e.target.files);
    });

    // 드래그 앤 드롭 이벤트
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        handleFileSelection(e.dataTransfer.files);
    });
});

// 파일 선택 처리
function handleFileSelection(files) {
    selectedFiles = Array.from(files);
    displaySelectedFiles();
    document.getElementById('upload-btn').disabled = selectedFiles.length === 0;
}

// 선택된 파일 목록 표시
function displaySelectedFiles() {
    const selectedFilesDiv = document.getElementById('selected-files');
    const fileListDiv = document.getElementById('file-list');

    if (selectedFiles.length === 0) {
        selectedFilesDiv.style.display = 'none';
        return;
    }

    selectedFilesDiv.style.display = 'block';

    const fileListHtml = selectedFiles.map((file, index) => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    ${getFileIconByName(file.name)}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">${file.name}</p>
                    <p class="text-sm text-gray-500">${formatFileSize(file.size)}</p>
                </div>
            </div>
            <button type="button" onclick="removeSelectedFile(${index})" class="text-red-600 hover:text-red-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `).join('');

    fileListDiv.innerHTML = fileListHtml;
}

// 선택된 파일 제거
function removeSelectedFile(index) {
    selectedFiles.splice(index, 1);
    displaySelectedFiles();
    document.getElementById('upload-btn').disabled = selectedFiles.length === 0;
}

// 파일명으로 아이콘 생성
function getFileIconByName(fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    const iconClasses = {
        'jpg': 'text-green-500', 'jpeg': 'text-green-500', 'png': 'text-green-500', 'gif': 'text-green-500',
        'pdf': 'text-red-500',
        'doc': 'text-blue-500', 'docx': 'text-blue-500', 'txt': 'text-blue-500',
        'mp4': 'text-purple-500', 'mp3': 'text-blue-500',
        'zip': 'text-yellow-500', 'rar': 'text-yellow-500'
    };

    const iconClass = iconClasses[extension] || 'text-gray-500';

    return `<svg class="h-6 w-6 ${iconClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>`;
}

// 진행률 바 업데이트
function updateProgressBar(percent) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');

    progressBar.style.width = percent + '%';
    progressText.textContent = Math.round(percent) + '%';
}

// 파일 업로드 실행
async function uploadFiles() {
    if (selectedFiles.length === 0 || isUploading) return;

    isUploading = true;
    document.getElementById('upload-btn').disabled = true;
    document.getElementById('upload-progress').style.display = 'block';
    document.getElementById('upload-messages').innerHTML = '';

    const totalFiles = selectedFiles.length;
    let completedFiles = 0;
    const results = [];

    try {
        // 각 파일을 순차적으로 업로드
        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];

            try {
                const result = await uploadSingleFile(file);
                results.push({ file: file.name, success: true, message: '업로드 완료' });
            } catch (error) {
                results.push({ file: file.name, success: false, message: error.message });
            }

            completedFiles++;
            const progress = (completedFiles / totalFiles) * 100;
            updateProgressBar(progress);
        }

        // 결과 표시
        displayUploadResults(results);

        // 성공한 파일이 있으면 파일 목록 새로고침
        if (results.some(r => r.success)) {
            setTimeout(() => {
                loadFiles(); // 파일 목록 새로고침
            }, 1000);
        }

    } catch (error) {
        showNotification('업로드 중 오류가 발생했습니다: ' + error.message, 'error');
    } finally {
        isUploading = false;
        document.getElementById('upload-btn').textContent = '완료';
        document.getElementById('upload-btn').disabled = false;
    }
}

// 단일 파일 업로드
async function uploadSingleFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch('/api/sandbox/file-upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: formData
    });

    if (!response.ok) {
        // 응답 텍스트를 확인하여 디버깅
        const responseText = await response.text();
        console.error('Upload error response:', responseText);
        throw new Error(`업로드 실패: ${response.status} - ${responseText.substring(0, 100)}`);
    }

    const responseText = await response.text();
    console.log('Upload response:', responseText);

    let result;
    try {
        result = JSON.parse(responseText);
    } catch (e) {
        console.error('JSON parsing error:', e);
        console.error('Response was:', responseText);
        throw new Error(`서버 응답 형식 오류: ${responseText.substring(0, 100)}`);
    }

    if (!result.success) {
        throw new Error(result.message || '업로드 실패');
    }

    return result;
}

// 업로드 결과 표시
function displayUploadResults(results) {
    const messagesDiv = document.getElementById('upload-messages');
    const successCount = results.filter(r => r.success).length;
    const failCount = results.length - successCount;

    let html = '';

    if (successCount > 0) {
        html += `<div class="p-3 bg-green-100 border border-green-200 rounded-md">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        ${successCount}개 파일이 성공적으로 업로드되었습니다.
                    </h3>
                </div>
            </div>
        </div>`;
    }

    if (failCount > 0) {
        html += `<div class="p-3 bg-red-100 border border-red-200 rounded-md mt-2">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        ${failCount}개 파일 업로드에 실패했습니다.
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        ${results.filter(r => !r.success).map(r => `• ${r.file}: ${r.message}`).join('<br>')}
                    </div>
                </div>
            </div>
        </div>`;
    }

    messagesDiv.innerHTML = html;
}
</script>
</div>
