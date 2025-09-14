<!-- 커스텀 화면 콘텐츠 컴포넌트 -->

<!-- 샌드박스 정보 표시 영역 -->
<div class="mb-4 bg-gray-50 border border-gray-200 rounded-lg p-3">
    <div class="flex items-center space-x-4">

        <!-- 1. 샌드박스 정보 표시 -->
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
            </svg>
            <span>{{ $sandboxInfo['sandbox_name']}}</span>
        </div>

        <!-- 2. 도메인 선택 드롭다운 -->
        <div class="relative">
            <button wire:click="$toggle('domainDropdownOpen')" class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>100 Domain Pms</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- 도메인 드롭다운 메뉴 -->
            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- 3. 화면 선택 드롭다운 (현재 도메인의 화면들만) -->
        <div class="relative">
            <button wire:click="$toggle('dropdownOpen')" class="flex items-center space-x-2 text-sm bg-white border border-gray-300 rounded-md px-3 py-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span>102-screen-project-list</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- 화면 드롭다운 메뉴 -->
            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
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
