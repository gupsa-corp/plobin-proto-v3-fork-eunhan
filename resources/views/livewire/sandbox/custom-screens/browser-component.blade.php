<div class="w-full p-6">
    <!-- 헤더 -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                🎨 템플릿 화면 관리자
            </h1>
            <p class="text-gray-600 mt-1">템플릿 화면들을 관리하고 미리보기할 수 있습니다. 모든 화면은 템플릿 스토리지에 저장됩니다.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 왼쪽: 화면 목록 -->
        <div class="space-y-4">
            <!-- 검색 및 필터 -->
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <!-- 도메인 선택 및 뷰 선택 드롭다운 -->
                <div class="flex items-center space-x-4 mb-4 p-3 bg-gray-50 rounded-lg">
                    <!-- 도메인 선택 드롭다운 -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">도메인</label>
                        <select wire:model.live="filterDomain" 
                                class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[150px]">
                            <option value="">전체 도메인</option>
                            @foreach($availableDomains as $domain)
                                <option value="{{ $domain['value'] }}">{{ $domain['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 구분선 -->
                    <div class="h-8 w-px bg-gray-300"></div>

                    <!-- 테이블뷰 선택 드롭다운 -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">화면 유형</label>
                        <select wire:model.live="filterType" 
                                class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[150px]">
                            <option value="">전체 유형</option>
                            @foreach($availableScreenTypes as $type)
                                <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 구분선 -->
                    <div class="h-8 w-px bg-gray-300"></div>

                    <!-- 현재 필터 상태 표시 -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">현재 필터</label>
                        <div class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600">
                            @if($filterDomain || $filterType)
                                <span>
                                    @if($filterDomain) 도메인: {{ collect($availableDomains)->firstWhere('value', $filterDomain)['label'] ?? $filterDomain }} @endif
                                    @if($filterDomain && $filterType) | @endif
                                    @if($filterType) 유형: {{ collect($availableScreenTypes)->firstWhere('value', $filterType)['label'] ?? $filterType }} @endif
                                </span>
                            @else
                                <span>필터 없음</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 검색 및 추가 필터 -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                        <input wire:model.live="search" type="text" id="search"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               placeholder="화면 제목으로 검색...">
                    </div>
                    <div class="flex items-end">
                        <button wire:click="loadScreens" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm flex items-center space-x-2">
                            <span>🔄</span>
                            <span>새로고침</span>
                        </button>
                        @if($search || $filterDomain || $filterType)
                            <button wire:click="$set('search', ''); $set('filterDomain', ''); $set('filterType', '')" 
                                    class="ml-2 px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 text-sm">
                                초기화
                            </button>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">총 {{ count($screens) }}개 템플릿 화면</span>
                </div>
            </div>

            <!-- 화면 목록 -->
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($screens as $screen)
                    <div wire:click="selectScreen('{{ $screen['id'] }}')"
                         class="bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:shadow-md transition-shadow
                                {{ $selectedScreen && $selectedScreen['id'] == $screen['id'] ? 'border-blue-500 bg-blue-50' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h3 class="font-semibold text-gray-900">{{ $screen['title'] }}</h3>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                        🎨 템플릿
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">{{ $screen['description'] ?? '설명 없음' }}</p>
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                        {{ ucfirst($screen['type']) }}
                                    </span>
                                    <span>{{ $screen['created_at'] }}</span>
                                    @if($screen['file_exists'])
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                            📄 파일 존재
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">
                                            ❌ 파일 없음
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col space-y-1 ml-4">
                                <button wire:click.stop="editScreen('{{ $screen['id'] }}')"
                                        class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-50">
                                    ✏️ 편집
                                </button>
                                <a href="{{ $this->getPreviewUrl($screen['id']) }}"
                                   target="_blank"
                                   onclick="event.stopPropagation()"
                                   class="text-purple-600 hover:text-purple-800 text-xs px-2 py-1 rounded hover:bg-purple-50 inline-block text-center">
                                    🚀 새창보기
                                </a>
                                <button wire:click.stop="duplicateScreen('{{ $screen['id'] }}')"
                                        class="text-green-600 hover:text-green-800 text-xs px-2 py-1 rounded hover:bg-green-50">
                                    📄 복사
                                </button>
                                <button wire:click.stop="deleteScreen('{{ $screen['id'] }}')"
                                        class="text-red-600 hover:text-red-800 text-xs px-2 py-1 rounded hover:bg-red-50"
                                        onclick="return confirm('정말 삭제하시겠습니까? 템플릿 파일이 완전히 제거됩니다.')">
                                    🗑️ 삭제
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>

        <!-- 오른쪽: 미리보기 -->
        <div class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="border-b border-gray-200 px-4 py-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900">미리보기</h3>
                        @if($selectedScreen)
                            <div class="flex space-x-2">
                                <button wire:click="togglePreview"
                                        class="text-sm px-3 py-1 rounded-md {{ $previewMode ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                    {{ $previewMode ? '📝 코드 보기' : '👁️ 미리보기' }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-4">
                    @if($selectedScreen)
                        @if($previewMode)
                            <!-- 실제 렌더링된 화면 미리보기 -->
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="mb-2 text-sm text-gray-600">렌더링 결과:</div>
                                <div class="bg-white border rounded-lg p-4 min-h-[300px]">
                                    @livewire('sandbox.custom-screens.renderer-component', ['screenData' => $selectedScreen], key('renderer-'.$selectedScreen['id']))
                                </div>
                            </div>
                        @else
                            <!-- 코드 보기 -->
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">화면 정보</h4>
                                    <div class="bg-gray-50 rounded p-3 text-sm">
                                        <div><strong>제목:</strong> {{ $selectedScreen['title'] }}</div>
                                        <div><strong>설명:</strong> {{ $selectedScreen['description'] ?? '없음' }}</div>
                                        <div><strong>유형:</strong> {{ $selectedScreen['type'] }}</div>
                                        <div><strong>생성일:</strong> {{ $selectedScreen['created_at'] }}</div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">파일 정보</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm space-y-2">
                                        <div><strong>경로:</strong> {{ $selectedScreen['file_path'] }}</div>
                                        <div><strong>폴더명:</strong> {{ $selectedScreen['folder_name'] }}</div>
                                        @if($selectedScreen['file_exists'])
                                            <div class="text-green-600"><strong>상태:</strong> ✅ 파일 존재</div>
                                            @if(isset($selectedScreen['file_size']))
                                                <div><strong>크기:</strong> {{ $selectedScreen['file_size'] }} bytes</div>
                                            @endif
                                            @if(isset($selectedScreen['file_modified']))
                                                <div><strong>수정일:</strong> {{ $selectedScreen['file_modified'] }}</div>
                                            @endif
                                        @else
                                            <div class="text-red-600"><strong>상태:</strong> ❌ 파일 없음</div>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">템플릿 경로</h4>
                                    @if($selectedScreen['file_exists'])
                                        <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm">
                                            <p class="text-gray-600 mb-2">💡 템플릿 파일 위치:</p>
                                            <code class="text-xs text-gray-800 break-all">{{ $selectedScreen['full_path'] }}</code>
                                        </div>
                                    @else
                                        <div class="bg-red-50 border border-red-200 rounded p-3 text-sm">
                                            <p class="text-red-600">⚠️ 템플릿 파일이 존재하지 않습니다.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">👈</div>
                            <p>템플릿 화면을 선택하여 미리보기를 확인하세요.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 플래시 메시지 -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    <script>
    document.addEventListener('livewire:initialized', () => {
        // 팝업 창 열기 이벤트
        Livewire.on('openPreviewWindow', (event) => {
            console.log('Opening preview window:', event);
            const url = event.url || event[0]?.url;
            if (!url) {
                console.error('No URL provided for preview window');
                return;
            }

            const width = Math.min(1200, screen.width * 0.8);
            const height = Math.min(800, screen.height * 0.8);
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;

            console.log('Opening URL:', url);

            try {
                // 팝업 창 열기 시도
                const newWindow = window.open(url, `preview_${Date.now()}`,
                    `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes,menubar=no,toolbar=no,status=no`);

                if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
                    console.warn('Popup blocked, opening in new tab instead');
                    // 팝업이 차단된 경우 새 탭으로 열기
                    window.open(url, '_blank');
                } else {
                    // 팝업이 성공적으로 열렸으면 포커스 설정
                    newWindow.focus();
                }
            } catch (error) {
                console.error('Error opening window:', error);
                // 오류 발생 시 새 탭으로 열기
                window.open(url, '_blank');
            }
        });

        // URL 업데이트 이벤트
        Livewire.on('update-url', (event) => {
            console.log('Updating URL:', event);
            const params = event[0] || event;

            const url = new URL(window.location);

            // URL 파라미터 업데이트
            if (params.screen) {
                url.searchParams.set('screen', params.screen);
            }

            if (params.previewMode !== undefined) {
                if (params.previewMode === '1' || params.previewMode === true) {
                    url.searchParams.set('previewMode', '1');
                } else {
                    url.searchParams.delete('previewMode');
                }
            }

            // 브라우저 히스토리에 추가하지 않고 URL만 변경
            window.history.replaceState({}, '', url);

            console.log('URL updated to:', url.toString());
        });
    });
    </script>
</div>
