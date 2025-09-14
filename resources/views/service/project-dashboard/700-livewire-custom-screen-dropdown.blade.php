{{-- Custom Screen Folder 선택 드롭다운 Livewire 컴포넌트 --}}
<div>

    @if($page && $hasSandbox)
        <!-- 커스텀 화면용 드롭다운 네비게이션 -->
        <div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div class="flex items-center space-x-4">

                <!-- 1. 샌드박스 정보 표시 -->
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span>{{ $page->project->sandbox_folder ?? '샌드박스' }}</span>
                </div>

                <!-- 2. 도메인 선택 드롭다운 -->
                <div class="relative">
                    <button wire:click="$toggle('domainDropdownOpen')"
                            class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>{{ $currentDomain ? str_replace(['-domain-', '-'], [' ', ' '], ucwords($currentDomain, '-')) : '도메인 선택' }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 {{ $domainDropdownOpen ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- 도메인 드롭다운 메뉴 -->
                    @if($domainDropdownOpen)
                        <div class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                             wire:click.away="closeDomainDropdown">
                            <div class="py-1">
                                @foreach($availableDomains as $domain)
                                    <button wire:click="selectDomain('{{ $domain['id'] }}')"
                                            class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        @if($domain['id'] === $currentDomain)
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full mr-2">현재</span>
                                        @endif
                                        <span>{{ $domain['display_name'] }}</span>
                                    </button>
                                @endforeach
                                @if(count($availableDomains) === 0)
                                    <div class="px-4 py-2 text-sm text-gray-500">
                                        사용 가능한 도메인이 없습니다.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 3. 화면 선택 드롭다운 (현재 도메인의 화면들만) -->
                <div class="relative">
                    <button wire:click="$toggle('dropdownOpen')"
                            class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $currentCustomScreenFolder ?: '화면 선택' }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 {{ $dropdownOpen ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- 화면 드롭다운 메뉴 -->
                    @if($dropdownOpen)
                        <div class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                             wire:click.away="closeDropdown">
                            <div class="py-1 max-h-64 overflow-y-auto">
                                @foreach($currentDomainScreens as $screen)
                                    <button wire:click="selectScreen('{{ $screen['folder_name'] }}')"
                                            class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        @if($screen['folder_name'] === $currentCustomScreenFolder)
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full mr-2">현재</span>
                                        @endif
                                        <div class="flex-1">
                                            <span class="font-medium">{{ $screen['title'] }}</span>
                                            <p class="text-xs text-gray-500 mt-1">{{ $screen['description'] ?? '' }}</p>
                                        </div>
                                    </button>
                                @endforeach
                                @if(count($currentDomainScreens) === 0)
                                    <div class="px-4 py-2 text-sm text-gray-500">
                                        이 도메인에 사용 가능한 화면이 없습니다.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 4. 현재 선택된 화면 정보 -->
                @if($currentCustomScreenFolder)
                    <div class="flex items-center space-x-2 text-sm text-green-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex flex-col">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium">{{ $currentCustomScreenFolder }}</span>
                                @if($currentDomain)
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ str_replace('-', ' ', ucfirst($currentDomain)) }}</span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-500 mt-1">현재 활성화된 커스텀 화면</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if (session()->has('success'))
            <div class="mt-2 text-xs text-green-600 bg-green-50 p-2 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded">
                {{ session('error') }}
            </div>
        @endif
    @endif
</div>
