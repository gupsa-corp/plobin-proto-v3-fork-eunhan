<!-- 템플릿 화면용 드롭다운 네비게이션 -->
<div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
    <div class="flex items-center space-x-4">
        
        <!-- 1. 샌드박스 변경 드롭다운 -->
        <div class="relative" x-data="{ sandboxOpen: false }">
            <button @click="sandboxOpen = !sandboxOpen"
                    class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                <span>Storage Sandbox Template</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="sandboxOpen" @click.away="sandboxOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    <a href="/sandbox/custom-screens" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full mr-2">현재</span>
                        Storage Sandbox Template
                    </a>
                    <a href="/sandbox" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <span class="mr-2">📦</span>
                        다른 샌드박스로 이동
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. 도메인 변경 드롭다운 -->
        <div class="relative" x-data="{
            domainOpen: false,
            domains: [],
            currentDomain: '{{ $customScreen['domain'] ?? '' }}',
            
            async loadDomains() {
                try {
                    const response = await fetch('/api/sandbox/screens?sandbox_folder=storage-sandbox-template');
                    const data = await response.json();
                    const uniqueDomains = [...new Set(data.screens.map(s => s.domain))];
                    this.domains = uniqueDomains.map(domain => {
                        const firstScreen = data.screens.find(s => s.domain === domain);
                        return {
                            id: domain,
                            name: domain.replace(/^\d+-/, '').replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
                        };
                    });
                } catch (error) {
                    console.error('도메인 로딩 오류:', error);
                }
            },
            
            changeDomain(domainId) {
                const response = fetch('/api/sandbox/screens?sandbox_folder=storage-sandbox-template')
                    .then(response => response.json())
                    .then(data => {
                        const firstScreenInDomain = data.screens.find(s => s.domain === domainId);
                        if (firstScreenInDomain) {
                            window.location.href = `/sandbox/storage-sandbox-template/${firstScreenInDomain.domain}/${firstScreenInDomain.screen}`;
                        }
                    });
            }
        }" x-init="loadDomains()">
            <button @click="domainOpen = !domainOpen"
                    class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span x-text="currentDomain.replace(/^\d+-/, '').replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || '도메인 선택'"></span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="domainOpen" @click.away="domainOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    <template x-for="domain in domains" :key="domain.id">
                        <button @click="changeDomain(domain.id); domainOpen = false"
                                class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full mr-2"
                                  x-show="domain.id === currentDomain">현재</span>
                            <span x-text="domain.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- 3. 페이지 변경 드롭다운 -->
        <div class="relative" x-data="{
            screenOpen: false,
            currentDomainScreens: [],
            currentScreen: '{{ $customScreen['screen'] ?? '' }}',
            currentDomain: '{{ $customScreen['domain'] ?? '' }}',
            
            getCurrentScreenTitle() {
                return '{{ $customScreen['title'] ?? '페이지 선택' }}';
            },

            async loadCurrentDomainScreens() {
                try {
                    const response = await fetch('/api/sandbox/screens?sandbox_folder=storage-sandbox-template');
                    const data = await response.json();
                    // 현재 도메인의 화면들만 필터링
                    this.currentDomainScreens = data.screens.filter(s => s.domain === this.currentDomain) || [];
                } catch (error) {
                    console.error('화면 로딩 오류:', error);
                }
            },

            changeScreen(screenId) {
                const screen = this.currentDomainScreens.find(s => s.screen === screenId);
                if (screen) {
                    window.location.href = `/sandbox/storage-sandbox-template/${screen.domain}/${screen.screen}`;
                }
            }
        }" x-init="loadCurrentDomainScreens()">
            <button @click="screenOpen = !screenOpen"
                    class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span x-text="getCurrentScreenTitle()"></span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="screenOpen" @click.away="screenOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1 max-h-64 overflow-y-auto">
                    <template x-for="screen in currentDomainScreens" :key="screen.id">
                        <button @click="changeScreen(screen.screen); screenOpen = false"
                                class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full mr-2"
                                  x-show="screen.screen === currentScreen">현재</span>
                            <div class="flex-1">
                                <span class="font-medium" x-text="screen.title"></span>
                                <p class="text-xs text-gray-500" x-text="screen.description"></p>
                            </div>
                        </button>
                    </template>
                    <div x-show="currentDomainScreens.length === 0" class="px-4 py-2 text-sm text-gray-500">
                        이 도메인에 사용 가능한 페이지가 없습니다.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // Alpine.js 초기화 완료 시 실행할 코드
});
</script>