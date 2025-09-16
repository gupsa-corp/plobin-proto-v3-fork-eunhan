{{-- 글로벌 샌드박스 네비게이션 드롭다운 --}}
<?php
    use App\Helpers\SandboxHelper;
    $availableScreens = SandboxHelper::getAvailableScreens();

    // getAvailableScreens()가 비어있을 경우 frontend 디렉토리에서 자동으로 스크린 목록 생성
    if (empty($availableScreens)) {
        $frontendPath = __DIR__;
        $availableScreens = [];

        // frontend 디렉토리의 모든 하위 디렉토리 스캔
        if (is_dir($frontendPath)) {
            $directories = scandir($frontendPath);
            foreach ($directories as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($frontendPath . '/' . $dir)) {
                    // xxx-screen 패턴의 디렉토리만 필터링
                    if (preg_match('/^\d+-screen-/', $dir)) {
                        // 디렉토리명에서 제목 추출 (숫자- 접두사 제거하고 하이픈을 공백으로 변경)
                        $title = str_replace('-', ' ', preg_replace('/^\d+-/', '', $dir));
                        $title = ucwords($title);

                        $availableScreens[] = [
                            'value' => $dir,
                            'title' => $title
                        ];
                    }
                }
            }

            // 숫자 순서로 정렬
            usort($availableScreens, function($a, $b) {
                return strcmp($a['value'], $b['value']);
            });
        }
    }
?>
<div class="mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center space-x-4">
            {{-- 샌드박스 선택 드롭다운 --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">샌드박스</label>
                <select id="sandbox-selector" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 min-w-[180px]">
                    @php
                        $sandboxContextService = app(App\Services\SandboxContextService::class);
                        $availableSandboxes = $sandboxContextService->getAvailableSandboxes();
                        $currentSandbox = $sandboxContextService->getCurrentSandbox();
                    @endphp
                    @foreach($availableSandboxes as $sandbox)
                        <option value="{{ $sandbox['name'] }}" {{ $sandbox['is_active'] ? 'selected' : '' }}>
                            {{ $sandbox['display_name'] }}
                        </option>
                    @endforeach
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
        const currentSandbox = urlParts[2] || '{{ $currentSandbox }}';
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
