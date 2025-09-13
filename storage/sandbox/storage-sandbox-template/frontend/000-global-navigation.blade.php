{{-- 글로벌 샌드박스 네비게이션 드롭다운 --}}
<?php 
    $availableScreens = getAvailableScreens();
    
    // getAvailableScreens()가 비어있을 경우 수동으로 추가
    if (empty($availableScreens)) {
        $availableScreens = [
            ['value' => '001-screen-dashboard', 'title' => '대시보드'],
            ['value' => '002-screen-project-list', 'title' => '프로젝트 목록'],
            ['value' => '003-screen-table-view', 'title' => '테이블 뷰'],
            ['value' => '004-screen-kanban-board', 'title' => '칸반 보드'],
            ['value' => '005-screen-gantt-chart', 'title' => '간트 차트'],
            ['value' => '006-screen-calendar-view', 'title' => '캘린더 뷰'],
            ['value' => '007-screen-file-upload', 'title' => '파일 업로드'],
            ['value' => '007-screen-multi-file-upload', 'title' => '다중 파일 업로드'],
            ['value' => '008-screen-file-list', 'title' => '파일 목록'],
            ['value' => '008-screen-uploaded-files-list', 'title' => '업로드된 파일 목록'],
            ['value' => '009-screen-analysis-requests', 'title' => '분석 요청'],
            ['value' => '010-screen-pms-summary-requests', 'title' => 'PMS 요약 요청'],
            ['value' => '011-screen-form-execution', 'title' => '폼 실행'],
            ['value' => '012-screen-form-history', 'title' => '폼 이력'],
            ['value' => '013-screen-document-analysis', 'title' => '문서 분석'],
            ['value' => '100-pms-common', 'title' => 'PMS 공통']
        ];
    }
?>
<div class="mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center space-x-4">
            {{-- 샌드박스 선택 드롭다운 --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">샌드박스</label>
                <select id="sandbox-selector" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[180px]">
                    <option value="storage-sandbox-template" selected>storage-sandbox-template</option>
                </select>
            </div>

            {{-- 구분선 --}}
            <div class="h-8 w-px bg-gray-300"></div>

            {{-- 뷰 선택 드롭다운 (동적 생성) --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">화면 뷰</label>
                <select id="view-selector" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[220px]">
                    @foreach($availableScreens as $screen)
                        <option value="{{ $screen['value'] }}" title="{{ $screen['title'] }}">
                            {{ $screen['value'] }} - {{ $screen['title'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 구분선 --}}
            <div class="h-8 w-px bg-gray-300"></div>

            {{-- 현재 경로 표시 --}}
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">현재 경로</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
                    <span id="current-path"></span>
                </div>
            </div>

            {{-- 새 탭에서 열기 버튼 --}}
            <div class="flex items-end">
                <button id="open-new-tab" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-1">
                    <span>🔗</span>
                    <span>새 탭</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 글로벌 네비게이션 JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sandboxSelector = document.getElementById('sandbox-selector');
        const viewSelector = document.getElementById('view-selector');
        const currentPath = document.getElementById('current-path');
        const openNewTabBtn = document.getElementById('open-new-tab');

        // 현재 URL에서 sandbox와 view 추출
        const currentUrl = window.location.pathname;
        const urlParts = currentUrl.split('/');
        const currentSandbox = urlParts[2] || 'storage-sandbox-template';
        const currentView = urlParts[3] || '';

        // 현재 선택된 값으로 드롭다운 설정
        if (sandboxSelector) {
            sandboxSelector.value = currentSandbox;
        }
        if (viewSelector && currentView) {
            viewSelector.value = currentView;
        }

        // 경로 업데이트 함수
        function updateCurrentPath() {
            const sandbox = sandboxSelector?.value || currentSandbox;
            const view = viewSelector?.value || currentView;
            const path = `/sandbox/${sandbox}/${view}`;
            if (currentPath) {
                currentPath.textContent = path;
            }
        }

        // 페이지 이동 함수
        function navigateToView() {
            const sandbox = sandboxSelector?.value || currentSandbox;
            const view = viewSelector?.value || currentView;
            const url = `/sandbox/${sandbox}/${view}`;
            window.location.href = url;
        }

        // 새 탭에서 열기 함수
        function openInNewTab() {
            const sandbox = sandboxSelector?.value || currentSandbox;
            const view = viewSelector?.value || currentView;
            const url = `/sandbox/${sandbox}/${view}`;
            window.open(url, '_blank');
        }

        // 이벤트 리스너
        if (sandboxSelector) {
            sandboxSelector.addEventListener('change', function() {
                updateCurrentPath();
                navigateToView();
            });
        }

        if (viewSelector) {
            viewSelector.addEventListener('change', function() {
                updateCurrentPath();
                navigateToView();
            });
        }

        if (openNewTabBtn) {
            openNewTabBtn.addEventListener('click', openInNewTab);
        }

        // 초기 경로 설정
        updateCurrentPath();
    });
</script>