{{-- 업로드된 파일 목록 템플릿 --}}
<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-100 p-6">
    {{-- 헤더 --}}
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl">📋</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">파일 목록</h1>
                        <p class="text-gray-600">업로드된 모든 파일을 관리하세요</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="window.location.href='{{ url('/file-upload') }}'" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                        새 파일 업로드
                    </button>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">총 파일 수</div>
                        <div class="text-lg font-semibold text-gray-900" id="total-files">{{ count($files ?? []) ?: 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 검색 및 필터 --}}
    <div class="max-w-7xl mx-auto mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">🔍</span>
                        <input type="text" id="search-input" placeholder="파일명으로 검색..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <select id="filter-type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="all">모든 파일</option>
                        <option value="image">이미지</option>
                        <option value="document">문서</option>
                        <option value="spreadsheet">스프레드시트</option>
                        <option value="archive">압축파일</option>
                        <option value="other">기타</option>
                    </select>
                    <select id="sort-by" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="date-desc">최근 업로드순</option>
                        <option value="date-asc">오래된 순</option>
                        <option value="name-asc">이름 (A-Z)</option>
                        <option value="name-desc">이름 (Z-A)</option>
                        <option value="size-desc">크기 (큰 순)</option>
                        <option value="size-asc">크기 (작은 순)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- 파일 목록 --}}
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            {{-- 뷰 옵션 --}}
            <div class="p-6 border-b bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-700">보기 옵션:</span>
                        <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                            <button type="button" id="grid-view" class="px-3 py-1 bg-purple-500 text-white text-sm">
                                📱 그리드
                            </button>
                            <button type="button" id="list-view" class="px-3 py-1 bg-white text-gray-700 text-sm border-l border-gray-300">
                                📄 목록
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" id="select-all" class="px-3 py-1 text-sm text-purple-600 hover:bg-purple-50 rounded">
                            전체 선택
                        </button>
                        <button type="button" id="download-selected" class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 disabled:bg-gray-300" disabled>
                            선택된 파일 다운로드
                        </button>
                        <button type="button" id="delete-selected" class="px-4 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 disabled:bg-gray-300" disabled>
                            선택된 파일 삭제
                        </button>
                    </div>
                </div>
            </div>

            {{-- 그리드 뷰 --}}
            <div id="grid-container" class="p-6">
                <div id="file-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    {{-- 샘플 파일들 --}}
                    @php
                        $sampleFiles = [
                            ['name' => 'project-presentation.pdf', 'size' => 2048000, 'type' => 'document', 'date' => '2024-09-10 14:30'],
                            ['name' => 'budget-report.xlsx', 'size' => 512000, 'type' => 'spreadsheet', 'date' => '2024-09-10 13:20'],
                            ['name' => 'team-photo.jpg', 'size' => 3072000, 'type' => 'image', 'date' => '2024-09-10 12:10'],
                            ['name' => 'source-code.zip', 'size' => 10240000, 'type' => 'archive', 'date' => '2024-09-10 11:00'],
                            ['name' => 'meeting-notes.txt', 'size' => 51200, 'type' => 'document', 'date' => '2024-09-10 10:45'],
                            ['name' => 'logo-design.png', 'size' => 1536000, 'type' => 'image', 'date' => '2024-09-10 09:30'],
                            ['name' => 'contract.docx', 'size' => 256000, 'type' => 'document', 'date' => '2024-09-09 16:20'],
                            ['name' => 'sales-data.csv', 'size' => 128000, 'type' => 'spreadsheet', 'date' => '2024-09-09 15:15'],
                            ['name' => 'video-demo.mp4', 'size' => 25600000, 'type' => 'other', 'date' => '2024-09-09 14:10'],
                            ['name' => 'backup.7z', 'size' => 51200000, 'type' => 'archive', 'date' => '2024-09-09 13:05']
                        ];
                    @endphp
                    
                    @foreach($sampleFiles as $index => $file)
                    <div class="file-item bg-gray-50 rounded-lg p-4 hover:bg-gray-100 cursor-pointer transition-colors" 
                         data-name="{{ $file['name'] }}" 
                         data-type="{{ $file['type'] }}" 
                         data-size="{{ $file['size'] }}" 
                         data-date="{{ $file['date'] }}">
                        <input type="checkbox" class="file-checkbox absolute top-2 left-2 hidden">
                        <div class="text-center">
                            <div class="text-3xl mb-2">{{ getFileIcon($file['name'], $file['type']) }}</div>
                            <p class="text-sm font-medium text-gray-900 truncate mb-1">{{ $file['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ formatFileSize($file['size']) }}</p>
                            <p class="text-xs text-gray-400">{{ date('m/d H:i', strtotime($file['date'])) }}</p>
                        </div>
                        <div class="mt-3 flex justify-center space-x-1">
                            <button type="button" class="download-btn text-xs px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200" title="다운로드">
                                ⬇️
                            </button>
                            <button type="button" class="share-btn text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200" title="공유">
                                🔗
                            </button>
                            <button type="button" class="delete-btn text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" title="삭제">
                                🗑️
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 리스트 뷰 --}}
            <div id="list-container" class="hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="select-all-list" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">파일명</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">크기</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">타입</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">업로드 날짜</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작업</th>
                            </tr>
                        </thead>
                        <tbody id="file-table-body" class="bg-white divide-y divide-gray-200">
                            @foreach($sampleFiles as $index => $file)
                            <tr class="file-row hover:bg-gray-50" 
                                data-name="{{ $file['name'] }}" 
                                data-type="{{ $file['type'] }}" 
                                data-size="{{ $file['size'] }}" 
                                data-date="{{ $file['date'] }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="file-checkbox rounded">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="mr-3 text-xl">{{ getFileIcon($file['name'], $file['type']) }}</span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ formatFileSize($file['size']) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ getFileTypeName($file['type']) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ date('Y-m-d H:i', strtotime($file['date'])) }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <button type="button" class="download-btn text-green-600 hover:text-green-800" title="다운로드">⬇️</button>
                                    <button type="button" class="share-btn text-blue-600 hover:text-blue-800" title="공유">🔗</button>
                                    <button type="button" class="delete-btn text-red-600 hover:text-red-800" title="삭제">🗑️</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 페이지네이션 --}}
            <div class="px-6 py-4 border-t bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        총 <span id="showing-count">{{ count($sampleFiles) }}</span>개 파일 중 1-{{ count($sampleFiles) }} 표시
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" class="px-3 py-1 text-sm border border-gray-300 rounded disabled:opacity-50" disabled>이전</button>
                        <button type="button" class="px-3 py-1 text-sm bg-purple-500 text-white rounded">1</button>
                        <button type="button" class="px-3 py-1 text-sm border border-gray-300 rounded disabled:opacity-50" disabled>다음</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 파일 상세 모달 --}}
    <div id="file-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">파일 상세 정보</h3>
                <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">×</span>
                </button>
            </div>
            <div id="modal-content">
                {{-- 모달 내용이 여기에 동적으로 추가됩니다 --}}
            </div>
        </div>
        
        @php
        function getFileIcon($fileName, $type) {
            if ($type === 'image') return '🖼️';
            if ($type === 'document') return '📄';
            if ($type === 'spreadsheet') return '📊';
            if ($type === 'archive') return '📦';
            
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $icons = [
                'pdf' => '📄', 'doc' => '📝', 'docx' => '📝', 'txt' => '📄',
                'xls' => '📊', 'xlsx' => '📊', 'csv' => '📊',
                'zip' => '📦', 'rar' => '📦', '7z' => '📦',
                'mp4' => '🎥', 'avi' => '🎥', 'mov' => '🎥',
                'mp3' => '🎵', 'wav' => '🎵', 'flac' => '🎵',
                'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️'
            ];
            return $icons[$ext] ?? '📄';
        }

        function formatFileSize($bytes) {
            if ($bytes == 0) return '0 B';
            $k = 1024;
            $sizes = ['B', 'KB', 'MB', 'GB'];
            $i = floor(log($bytes) / log($k));
            return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
        }

        function getFileTypeName($type) {
            $types = [
                'image' => '이미지',
                'document' => '문서',
                'spreadsheet' => '스프레드시트',
                'archive' => '압축파일',
                'other' => '기타'
            ];
            return $types[$type] ?? '알 수 없음';
        }
        @endphp

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridContainer = document.getElementById('grid-container');
    const listContainer = document.getElementById('list-container');
    const searchInput = document.getElementById('search-input');
    const filterType = document.getElementById('filter-type');
    const sortBy = document.getElementById('sort-by');
    const selectAllBtn = document.getElementById('select-all');
    const selectAllList = document.getElementById('select-all-list');
    const downloadSelectedBtn = document.getElementById('download-selected');
    const deleteSelectedBtn = document.getElementById('delete-selected');
    const fileModal = document.getElementById('file-modal');
    const closeModal = document.getElementById('close-modal');

    let currentView = 'grid';
    let selectedFiles = [];

    // 뷰 전환
    gridView.addEventListener('click', () => switchView('grid'));
    listView.addEventListener('click', () => switchView('list'));

    function switchView(view) {
        currentView = view;
        if (view === 'grid') {
            gridView.classList.add('bg-purple-500', 'text-white');
            gridView.classList.remove('bg-white', 'text-gray-700');
            listView.classList.add('bg-white', 'text-gray-700');
            listView.classList.remove('bg-purple-500', 'text-white');
            gridContainer.classList.remove('hidden');
            listContainer.classList.add('hidden');
        } else {
            listView.classList.add('bg-purple-500', 'text-white');
            listView.classList.remove('bg-white', 'text-gray-700');
            gridView.classList.add('bg-white', 'text-gray-700');
            gridView.classList.remove('bg-purple-500', 'text-white');
            listContainer.classList.remove('hidden');
            gridContainer.classList.add('hidden');
        }
    }

    // 검색 및 필터링
    searchInput.addEventListener('input', filterFiles);
    filterType.addEventListener('change', filterFiles);
    sortBy.addEventListener('change', filterFiles);

    function filterFiles() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeFilter = filterType.value;
        const sortOption = sortBy.value;
        
        const fileItems = currentView === 'grid' 
            ? document.querySelectorAll('.file-item') 
            : document.querySelectorAll('.file-row');
        
        let visibleItems = [];
        
        fileItems.forEach(item => {
            const name = item.dataset.name.toLowerCase();
            const type = item.dataset.type;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesType = typeFilter === 'all' || type === typeFilter;
            
            if (matchesSearch && matchesType) {
                item.style.display = '';
                visibleItems.push(item);
            } else {
                item.style.display = 'none';
            }
        });
        
        // 정렬
        visibleItems.sort((a, b) => {
            const aName = a.dataset.name.toLowerCase();
            const bName = b.dataset.name.toLowerCase();
            const aSize = parseInt(a.dataset.size);
            const bSize = parseInt(b.dataset.size);
            const aDate = new Date(a.dataset.date);
            const bDate = new Date(b.dataset.date);
            
            switch(sortOption) {
                case 'name-asc': return aName.localeCompare(bName);
                case 'name-desc': return bName.localeCompare(aName);
                case 'size-asc': return aSize - bSize;
                case 'size-desc': return bSize - aSize;
                case 'date-asc': return aDate - bDate;
                case 'date-desc': return bDate - aDate;
                default: return bDate - aDate;
            }
        });
        
        // DOM 재정렬
        const container = currentView === 'grid' 
            ? document.getElementById('file-grid')
            : document.getElementById('file-table-body');
        
        visibleItems.forEach(item => container.appendChild(item));
        
        // 표시 개수 업데이트
        document.getElementById('showing-count').textContent = visibleItems.length;
    }

    // 체크박스 관리
    selectAllBtn.addEventListener('click', toggleSelectAll);
    selectAllList.addEventListener('change', toggleSelectAll);

    function toggleSelectAll() {
        const checkboxes = document.querySelectorAll('.file-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        updateSelectedFiles();
    }

    // 개별 체크박스 이벤트
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('file-checkbox')) {
            updateSelectedFiles();
        }
    });

    function updateSelectedFiles() {
        const checkboxes = document.querySelectorAll('.file-checkbox:checked');
        selectedFiles = Array.from(checkboxes).map(cb => {
            const item = cb.closest('.file-item, .file-row');
            return item.dataset.name;
        });
        
        downloadSelectedBtn.disabled = selectedFiles.length === 0;
        deleteSelectedBtn.disabled = selectedFiles.length === 0;
        
        if (selectedFiles.length > 0) {
            downloadSelectedBtn.classList.remove('disabled:bg-gray-300');
            deleteSelectedBtn.classList.remove('disabled:bg-gray-300');
        }
    }

    // 파일 작업 버튼들
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('download-btn')) {
            const item = e.target.closest('.file-item, .file-row');
            downloadFile(item.dataset.name);
        } else if (e.target.classList.contains('delete-btn')) {
            const item = e.target.closest('.file-item, .file-row');
            deleteFile(item.dataset.name);
        } else if (e.target.classList.contains('share-btn')) {
            const item = e.target.closest('.file-item, .file-row');
            shareFile(item.dataset.name);
        }
    });

    // 파일 다운로드
    function downloadFile(fileName) {
        alert(`${fileName} 다운로드를 시작합니다.`);
        // 실제 구현에서는 다운로드 API 호출
    }

    // 파일 삭제
    function deleteFile(fileName) {
        if (confirm(`${fileName}을(를) 삭제하시겠습니까?`)) {
            alert(`${fileName}이(가) 삭제되었습니다.`);
            // 실제 구현에서는 삭제 API 호출 후 UI 업데이트
        }
    }

    // 파일 공유
    function shareFile(fileName) {
        const shareUrl = `${window.location.origin}/share/${encodeURIComponent(fileName)}`;
        navigator.clipboard.writeText(shareUrl).then(() => {
            alert('공유 링크가 클립보드에 복사되었습니다!');
        });
    }

    // 선택된 파일들 다운로드
    downloadSelectedBtn.addEventListener('click', () => {
        if (selectedFiles.length > 0) {
            alert(`선택된 ${selectedFiles.length}개 파일의 다운로드를 시작합니다.`);
        }
    });

    // 선택된 파일들 삭제
    deleteSelectedBtn.addEventListener('click', () => {
        if (selectedFiles.length > 0 && confirm(`선택된 ${selectedFiles.length}개 파일을 삭제하시겠습니까?`)) {
            alert(`선택된 ${selectedFiles.length}개 파일이 삭제되었습니다.`);
        }
    });

    // 파일 상세 모달
    document.addEventListener('click', function(e) {
        if (e.target.closest('.file-item') && !e.target.closest('button') && !e.target.classList.contains('file-checkbox')) {
            const item = e.target.closest('.file-item');
            showFileDetails(item);
        }
    });

    function showFileDetails(item) {
        const modalContent = document.getElementById('modal-content');
        const fileName = item.dataset.name;
        const fileSize = formatFileSize(parseInt(item.dataset.size));
        const fileType = item.dataset.type;
        const fileDate = new Date(item.dataset.date).toLocaleString('ko-KR');
        
        modalContent.innerHTML = `
            <div class="text-center mb-4">
                <div class="text-6xl mb-2">${getFileIcon(fileName, fileType)}</div>
                <h4 class="text-lg font-semibold text-gray-900">${fileName}</h4>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">크기:</span>
                    <span class="font-medium">${fileSize}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">타입:</span>
                    <span class="font-medium">${getFileTypeName(fileType)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">업로드 날짜:</span>
                    <span class="font-medium">${fileDate}</span>
                </div>
            </div>
            <div class="mt-6 flex space-x-3">
                <button type="button" class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600" onclick="downloadFile('${fileName}')">
                    다운로드
                </button>
                <button type="button" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600" onclick="shareFile('${fileName}')">
                    공유
                </button>
                <button type="button" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600" onclick="deleteFile('${fileName}')">
                    삭제
                </button>
            </div>
        `;
        
        fileModal.classList.remove('hidden');
    }

    // 모달 닫기
    closeModal.addEventListener('click', () => {
        fileModal.classList.add('hidden');
    });

    fileModal.addEventListener('click', (e) => {
        if (e.target === fileModal) {
            fileModal.classList.add('hidden');
        }
    });

    // 유틸리티 함수들
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getFileIcon(fileName, type) {
        if (type === 'image') return '🖼️';
        if (type === 'document') return '📄';
        if (type === 'spreadsheet') return '📊';
        if (type === 'archive') return '📦';
        return '📄';
    }

    function getFileTypeName(type) {
        const types = {
            'image': '이미지',
            'document': '문서',
            'spreadsheet': '스프레드시트',
            'archive': '압축파일',
            'other': '기타'
        };
        return types[type] || '알 수 없음';
    }
});
    </script>
</div>