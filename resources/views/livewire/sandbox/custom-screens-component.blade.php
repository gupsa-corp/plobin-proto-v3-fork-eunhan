<div class="max-w-full mx-auto p-6">
    <!-- 헤더 -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            📱 커스텀 화면 관리
        </h1>
        <p class="text-gray-600 mt-1">화면을 생성하고 관리하여 다른 프로젝트에서 사용할 수 있습니다.</p>
    </div>

    <!-- 탭 네비게이션 -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                @foreach($availableTabs as $key => $tab)
                    <button wire:click="switchTab('{{ $key }}')"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors
                                   {{ $activeTab === $key
                                      ? 'border-blue-500 text-blue-600'
                                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $tab['icon'] }} {{ $tab['name'] }}
                    </button>
                @endforeach
            </nav>
        </div>
    </div>

    @if($activeTab === 'browser')
        <!-- 브라우저 탭 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 왼쪽: 화면 목록 -->
            <div class="space-y-4">
                <!-- 검색 및 필터 -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                            <input wire:model.live="search" type="text" id="search"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                   placeholder="제목이나 설명으로 검색...">
                        </div>
                        <div>
                            <label for="filterType" class="block text-sm font-medium text-gray-700 mb-1">유형</label>
                            <select wire:model.live="filterType" id="filterType"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">전체</option>
                                <option value="dashboard">대시보드</option>
                                <option value="list">목록</option>
                                <option value="form">폼</option>
                                <option value="detail">상세</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 화면 목록 -->
                <div class="space-y-3">
                    @forelse($screens as $screen)
                        <div wire:click="selectScreen({{ $screen['id'] }})"
                             class="screen-card {{ $selectedScreen == $screen['id'] ? 'selected' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">{{ $screen['title'] }}</h3>
                                    <p class="text-sm text-gray-600 mb-2">{{ $screen['description'] }}</p>
                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                            {{ ucfirst($screen['type']) }}
                                        </span>
                                        <span class="px-2 py-1 {{ $screen['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded-full">
                                            {{ $screen['status'] === 'published' ? '발행됨' : '임시저장' }}
                                        </span>
                                        <span>{{ $screen['created_at'] }}</span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @if($screen['status'] !== 'published')
                                        <button wire:click.stop="publishScreen({{ $screen['id'] }})"
                                                class="text-green-600 hover:text-green-800 text-sm">
                                            📤 발행
                                        </button>
                                    @endif
                                    <button wire:click.stop="deleteScreen({{ $screen['id'] }})"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                            onclick="return confirm('정말 삭제하시겠습니까?')">
                                        🗑️ 삭제
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>화면이 없습니다.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 오른쪽: 미리보기 -->
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-4">미리보기</h3>

                    @if($previewScreen)
                        <div class="mb-4">
                            <h4 class="font-medium text-gray-900">{{ $previewScreen['title'] }}</h4>
                            <p class="text-sm text-gray-600">{{ $previewScreen['description'] }}</p>
                        </div>

                        <div class="preview-area">
                            {!! $previewContent !!}
                        </div>

                        @if($previewScreen['status'] === 'published')
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                <p class="text-sm text-green-800">
                                    <strong>발행 URL:</strong>
                                    <code class="bg-green-100 px-2 py-1 rounded text-xs">
                                        {{ $sandbox_url ?? '/sandbox/' . ($current_sandbox ?? app(\App\Services\SandboxContextService::class)->getCurrentSandbox()) }}/100-domain-pms/{{ $previewScreen['folder_name'] ?? '103-screen-table-view' }}
                                    </code>
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>화면을 선택하여 미리보기를 확인하세요.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    @elseif($activeTab === 'creator')
        <!-- 생성 탭 -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="font-semibold text-gray-900 mb-4">새 화면 생성</h3>
            <p class="text-gray-600">화면 생성 기능은 곧 추가될 예정입니다.</p>
        </div>

    @elseif($activeTab === 'publisher')
        <!-- 발행 관리 탭 -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="font-semibold text-gray-900 mb-4">발행 관리</h3>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">발행된 화면</h4>
                        <p class="text-blue-800 text-2xl font-bold">
                            {{ collect($screens)->where('status', 'published')->count() }}개
                        </p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-medium text-yellow-900 mb-2">임시저장</h4>
                        <p class="text-yellow-800 text-2xl font-bold">
                            {{ collect($screens)->where('status', 'draft')->count() }}개
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h5 class="font-medium text-gray-900 mb-3">발행된 화면 목록</h5>
                    @php $publishedScreens = collect($screens)->where('status', 'published'); @endphp

                    @if($publishedScreens->count() > 0)
                        <div class="space-y-2">
                            @foreach($publishedScreens as $screen)
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded">
                                    <div>
                                        <span class="font-medium">{{ $screen['title'] }}</span>
                                        <span class="text-sm text-gray-600 ml-2">({{ $screen['type'] }})</span>
                                    </div>
                                    <code class="text-xs bg-white px-2 py-1 rounded border">
                                        {{ $sandbox_url ?? '/sandbox/' . ($current_sandbox ?? app(\App\Services\SandboxContextService::class)->getCurrentSandbox()) }}/100-domain-pms/{{ $screen['folder_name'] ?? '103-screen-table-view' }}
                                    </code>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">발행된 화면이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

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
</div>
