<div class="min-h-screen bg-gray-50">
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <button wire:click="backToCustomScreens" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        커스텀 화면으로 돌아가기
                    </button>
                </div>

                @if ($screenData)
                <div class="flex items-center space-x-6">
                    <div class="text-sm text-gray-500">
                        <span class="font-medium">Domain:</span> {{ $screenData['domain_title'] }}
                    </div>
                    <div class="text-sm text-gray-500">
                        <span class="font-medium">Screen:</span> {{ $screenData['screen_title'] }}
                    </div>
                    <div class="text-sm text-gray-500">
                        <span class="font-medium">Modified:</span> {{ $screenData['last_modified'] }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($error)
            <div class="bg-red-50 border border-red-200 rounded-md p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">오류</h3>
                        <p class="mt-2 text-sm text-red-700">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @elseif ($screenData)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $screenData['screen_title'] }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $screenData['domain_title'] }} 도메인의 {{ $screenData['screen_type'] }} 화면
                            </p>
                        </div>
                        <div class="text-right text-sm text-gray-500">
                            <div>파일 크기: {{ number_format($screenData['file_size']) }} bytes</div>
                            <div>경로: {{ str_replace(base_path(), '', $screenData['file_path']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="prose max-w-none">
                        <!-- 실제 Blade 템플릿 렌더링 -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                            @if ($screenData['content'])
                                @php
                                    // 안전하게 Blade 템플릿 내용 렌더링
                                    try {
                                        $currentSandbox = app(App\Services\SandboxContextService::class)->getCurrentSandbox();\n                                        echo view()->make('sandbox.container.' . $currentSandbox . '.' . str_replace('/', '.', $domain . '.' . $screen . '.000-content'))->render();
                                    } catch (\Exception $e) {
                                        echo '<div class="bg-yellow-50 border border-yellow-200 rounded p-4">';
                                        echo '<h4 class="text-yellow-800 font-medium">템플릿 렌더링 오류</h4>';
                                        echo '<p class="text-yellow-700 mt-1">직접 렌더링할 수 없습니다. 원본 파일 내용을 표시합니다.</p>';
                                        echo '</div>';
                                        echo '<div class="mt-4 bg-gray-100 p-4 rounded overflow-x-auto">';
                                        echo '<pre class="text-sm text-gray-800">' . htmlspecialchars($screenData['content']) . '</pre>';
                                        echo '</div>';
                                    }
                                @endphp
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>