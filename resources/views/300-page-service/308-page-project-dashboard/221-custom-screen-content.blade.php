<!-- 커스텀 화면 콘텐츠 컴포넌트 -->

<!-- 샌드박스 정보 표시 영역 -->
<div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3" x-data="{ domainDropdownOpen: false, screenDropdownOpen: false, availableScreens: [] }">
    <div class="flex items-center space-x-4">

        <!-- 1. 샌드박스 정보 표시 -->
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
            </svg>
            <span>{{ $sandboxInfo['sandbox_name']}}</span>
        </div>

        <!-- 2. 도메인 선택 드롭다운 -->
        <div class="relative" x-data="{ selectedDomain: '{{ $availableDomains[0]['display_name'] ?? 'No Domain' }}' }">
            <button @click="domainDropdownOpen = !domainDropdownOpen" class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span x-text="selectedDomain"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- 도메인 드롭다운 메뉴 -->
            <div x-show="domainDropdownOpen" @click.away="domainDropdownOpen = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 top-full mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                @if(count($availableDomains) > 0)
                    @foreach($availableDomains as $domain)
                        <a href="#"
                           @click="selectedDomain = '{{ $domain['display_name'] }}'; domainDropdownOpen = false; window.loadScreensByDomain('{{ $domain['id'] }}', $el)"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="flex justify-between items-center">
                                <span>{{ $domain['display_name'] }}</span>
                                <span class="text-xs text-gray-500">{{ $domain['screen_count'] }} screens</span>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="px-4 py-2 text-sm text-gray-500">
                        사용 가능한 도메인이 없습니다
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. 화면 선택 드롭다운 (현재 도메인의 화면들만) -->
        <div class="relative" x-data="{ selectedScreen: '{{ $customScreen['screen'] ?? ($customScreens[0]['folder_name'] ?? 'No Screen') }}' }" id="screenSelector">
            <button @click="screenDropdownOpen = !screenDropdownOpen" class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span x-text="selectedScreen"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- 화면 드롭다운 메뉴 -->
            <div x-show="screenDropdownOpen" @click.away="screenDropdownOpen = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 top-full mt-2 w-64 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                <div id="screenDropdownItems">
                    @if(count($customScreens) > 0)
                        @foreach($customScreens as $screen)
                            <a href="#"
                               @click="selectedScreen = '{{ $screen['folder_name'] }}'; screenDropdownOpen = false; window.updateSelectedScreen('{{ $screen['folder_name'] }}', window.getCurrentSelectedDomain())"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $screen['folder_name'] === ($customScreen['screen'] ?? '') ? 'bg-blue-50 text-blue-800' : '' }}">
                                <div class="flex justify-between items-center">
                                    <span>{{ $screen['folder_name'] }}</span>
                                    @if($screen['folder_name'] === ($customScreen['screen'] ?? ''))
                                        <span class="text-xs text-blue-600">(현재)</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $screen['title'] }}</div>
                            </a>
                        @endforeach
                    @else
                        <div class="px-4 py-2 text-sm text-gray-500">
                            사용 가능한 화면이 없습니다
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 5. 페이지 설정 드롭다운 (Livewire 컴포넌트) -->
        <div class="relative">
            {{-- 페이지 설정 드롭다운 (Livewire 컴포넌트) --}}
            <livewire:service.project-dashboard.page-settings-dropdown
                :org-id="$organizationId"
                :project-id="$projectId"
                :page-id="$pageId" />
        </div>
    </div>
</div>

<!-- 커스텀 화면 헤더 -->
<div class="mb-6 border-b border-gray-200 pb-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $customScreen['title'] ?? '커스텀 화면' }}</h1>
            <p class="text-gray-600 mt-1">{{ $customScreen['description'] ?? '' }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ ucfirst($customScreen['type'] ?? 'custom') }}
            </span>
            <span class="text-xs text-gray-500">{{ $customScreen['created_at'] ?? '' }}</span>
        </div>
    </div>
</div>

<!-- 커스텀 화면 컨텐츠 렌더링 -->
<div class="custom-screen-content">
    @if(!empty($customScreen['content']))
        {!! $customScreen['content'] !!}
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-800">커스텀 화면 콘텐츠를 렌더링할 수 없습니다.</p>
        </div>
    @endif
</div>

<script>
window.loadScreensByDomain = function(domainId, element) {
    // 로딩 상태 표시
    const screenDropdownItems = document.getElementById('screenDropdownItems');
    if (!screenDropdownItems) return;

    screenDropdownItems.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500 flex items-center"><svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>로딩 중...</div>';

    // API 호출
    fetch(`/api/sandbox/screens-by-domain?domain=${encodeURIComponent(domainId)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.screens.length > 0) {
                let html = '';
                data.screens.forEach(screen => {
                    html += `
                        <a href="#" onclick="updateSelectedScreen('${screen.folder_name}', '${domainId}')"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="flex justify-between items-center">
                                <span>${screen.folder_name}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">${screen.title}</div>
                        </a>
                    `;
                });
                screenDropdownItems.innerHTML = html;

                // 첫 번째 화면으로 선택 업데이트
                if (data.screens[0]) {
                    updateSelectedScreen(data.screens[0].folder_name, domainId);
                }
            } else {
                screenDropdownItems.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">사용 가능한 화면이 없습니다</div>';
            }
        })
        .catch(error => {
            console.error('화면 목록 로드 오류:', error);
            screenDropdownItems.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500 text-red-600">화면 목록 로드 실패</div>';
        });
};

window.updateSelectedScreen = function(screenName, domainName) {
    // Alpine.js 컨텍스트에서 selectedScreen 업데이트
    const screenSelector = document.getElementById('screenSelector');
    if (screenSelector && screenSelector._x_dataStack) {
        screenSelector._x_dataStack[0].selectedScreen = screenName;
        screenSelector._x_dataStack[0].screenDropdownOpen = false;
    }

    // 화면 콘텐츠 로드
    loadScreenContent(domainName || getCurrentSelectedDomain(), screenName);

    // 서버에 커스텀 화면 선택 저장
    saveCustomScreenSelection(domainName || getCurrentSelectedDomain(), screenName);
};

window.loadScreenContent = function(domainName, screenName) {
    const contentArea = document.querySelector('.custom-screen-content');
    if (!contentArea) return;

    // 로딩 상태 표시
    contentArea.innerHTML = `
        <div class="flex items-center justify-center p-8">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 animate-spin text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span class="text-gray-600">화면 콘텐츠 로딩 중...</span>
            </div>
        </div>
    `;

    // API 호출하여 화면 콘텐츠 로드
    fetch(`/api/sandbox/load-screen-content?domain=${encodeURIComponent(domainName)}&screen=${encodeURIComponent(screenName)}&sandbox=storage-sandbox-template`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.content) {
                // 헤더 정보 업데이트
                updateScreenHeader(data.content);

                // 콘텐츠 영역 업데이트
                if (data.content.has_content && data.content.content) {
                    contentArea.innerHTML = data.content.content;
                } else {
                    contentArea.innerHTML = `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-yellow-800">이 화면에는 표시할 콘텐츠가 없습니다.</p>
                            <p class="text-xs text-yellow-600 mt-1">파일: ${screenName}/000-content.blade.php</p>
                        </div>
                    `;
                }
            } else {
                contentArea.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-red-800">화면 콘텐츠를 로드할 수 없습니다.</p>
                        <p class="text-xs text-red-600 mt-1">${data.message || '알 수 없는 오류'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('화면 콘텐츠 로드 오류:', error);
            contentArea.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800">화면 콘텐츠 로드 중 네트워크 오류가 발생했습니다.</p>
                    <p class="text-xs text-red-600 mt-1">네트워크 연결을 확인해주세요.</p>
                </div>
            `;
        });
};

window.updateScreenHeader = function(screenData) {
    // 헤더 제목 업데이트
    const headerTitle = document.querySelector('.custom-screen-content').previousElementSibling.querySelector('h1');
    const headerDesc = document.querySelector('.custom-screen-content').previousElementSibling.querySelector('p');
    const headerType = document.querySelector('.custom-screen-content').previousElementSibling.querySelector('.bg-blue-100');
    const headerTime = document.querySelector('.custom-screen-content').previousElementSibling.querySelector('.text-xs.text-gray-500');

    if (headerTitle) headerTitle.textContent = screenData.title || '커스텀 화면';
    if (headerDesc) headerDesc.textContent = screenData.description || '';
    if (headerType) headerType.textContent = screenData.type ? screenData.type.toUpperCase() : 'CUSTOM';
    if (headerTime) headerTime.textContent = screenData.created_at || '';
};

window.getCurrentSelectedDomain = function() {
    // 현재 선택된 도메인 가져오기
    const domainDropdown = document.querySelector('[x-data*="selectedDomain"]');
    if (domainDropdown && domainDropdown._x_dataStack) {
        const selectedDisplayName = domainDropdown._x_dataStack[0].selectedDomain;
        // display_name에서 실제 도메인 ID 추출 (예: "100 Domain Pms" -> "100-domain-pms")
        return selectedDisplayName.toLowerCase().replace(/\s+/g, '-');
    }
    return '100-domain-pms'; // 기본값
};

window.saveCustomScreenSelection = function(domainName, screenName) {
    console.log('saveCustomScreenSelection called:', { domainName, screenName });

    // 페이지 정보 추출 (현재 URL에서)
    const urlParts = window.location.pathname.split('/');
    const organizationId = urlParts[2];
    const projectId = urlParts[4];
    const pageId = urlParts[6];

    // CSRF 토큰 가져오기
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        console.error('CSRF 토큰을 찾을 수 없습니다.');
        return;
    }

    console.log('Screen name check:', screenName, 'Is dashboard?', screenName === '106-screen-analysis-dashboard');

    // 분석 대시보드 선택 시 확인 다이얼로그 표시
    if (screenName === '106-screen-analysis-dashboard') {
        console.log('Dashboard detected, showing confirm dialog');
        if (confirm('문서 분석 대시보드를 선택하셨습니다.\n\n관련 하위 페이지들을 자동으로 추가하시겠습니까?\n- 업로드 파일 목록 (103-screen-uploaded-files-list)\n- 분석 요청 (104-screen-analysis-requests)\n- 문서 분석 (105-screen-document-analysis)')) {
            // 하위 페이지 자동 생성 요청 포함
            console.log('User selected YES - creating sub pages');
            saveWithSubPages(organizationId, projectId, pageId, domainName, screenName, csrfToken);
        } else {
            // 대시보드만 저장
            console.log('User selected NO - saving dashboard only');
            saveScreenOnly(organizationId, projectId, pageId, domainName, screenName, csrfToken);
        }
    } else {
        // 일반 화면 저장
        console.log('Regular screen - saving normally');
        saveScreenOnly(organizationId, projectId, pageId, domainName, screenName, csrfToken);
    }
};

window.saveWithSubPages = function(organizationId, projectId, pageId, domainName, screenName, csrfToken) {
    fetch('/api/sandbox/save-custom-screen', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            organization_id: organizationId,
            project_id: projectId,
            page_id: pageId,
            domain: domainName,
            screen: screenName,
            create_sub_pages: true,
            sub_pages: [
                {
                    title: '업로드 파일 목록',
                    screen: '103-screen-uploaded-files-list'
                },
                {
                    title: '분석 요청',
                    screen: '104-screen-analysis-requests'
                },
                {
                    title: '문서 분석',
                    screen: '105-screen-document-analysis'
                }
            ]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToastMessage('대시보드와 하위 페이지들이 생성되었습니다.', 'success');
            // 페이지 새로고침하여 새로운 페이지들 표시
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            console.error('커스텀 화면 저장 실패:', data.message);
            showToastMessage(data.message || '저장에 실패했습니다.', 'error');
        }
    })
    .catch(error => {
        console.error('커스텀 화면 저장 오류:', error);
        showToastMessage('네트워크 오류가 발생했습니다.', 'error');
    });
};

window.saveScreenOnly = function(organizationId, projectId, pageId, domainName, screenName, csrfToken) {
    fetch('/api/sandbox/save-custom-screen', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            organization_id: organizationId,
            project_id: projectId,
            page_id: pageId,
            domain: domainName,
            screen: screenName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToastMessage('커스텀 화면이 저장되었습니다.', 'success');
        } else {
            console.error('커스텀 화면 저장 실패:', data.message);
            showToastMessage(data.message || '저장에 실패했습니다.', 'error');
        }
    })
    .catch(error => {
        console.error('커스텀 화면 저장 오류:', error);
        showToastMessage('네트워크 오류가 발생했습니다.', 'error');
    });
};

window.showToastMessage = function(message, type = 'info') {
    // 토스트 메시지 생성 및 표시
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-md transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;

    document.body.appendChild(toast);

    // 3초 후 자동 제거
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
};
</script>