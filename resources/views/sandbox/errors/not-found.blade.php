<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('700-page-sandbox.700-common.301-layout-head', ['title' => '404 - 화면을 찾을 수 없습니다'])

<body class="bg-gray-100">
    @include('700-page-sandbox.700-common.400-sandbox-header')

    <div class="min-h-screen">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- 브레드크럼 -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('sandbox.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        샌드박스
                    </a>
                </li>
                @if($sandbox_name ?? null)
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('sandbox.domains', $sandbox_name) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 md:ml-2">{{ $sandbox_name }}</a>
                        </div>
                    </li>
                @endif
                @if($domain_name ?? null)
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('sandbox.screens', [$sandbox_name, $domain_name]) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 md:ml-2">{{ $domain_name }}</a>
                        </div>
                    </li>
                @endif
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">404 오류</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- 404 에러 섹션 -->
        <div class="text-center">
            <!-- 404 아이콘 -->
            <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-red-100 mb-8">
                <svg class="h-16 w-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-bold text-gray-900 mb-4">404 - 화면을 찾을 수 없습니다</h1>
            <p class="text-lg text-gray-600 mb-2">{{ $message ?? '요청한 화면을 찾을 수 없습니다.' }}</p>
            
            <!-- 에러 상세 정보 -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">요청 정보</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    @if($sandbox_name ?? null)
                        <div class="flex">
                            <span class="font-medium w-20">샌드박스:</span>
                            <span class="font-mono bg-gray-200 px-2 py-1 rounded">{{ $sandbox_name }}</span>
                        </div>
                    @endif
                    @if($domain_name ?? null)
                        <div class="flex">
                            <span class="font-medium w-20">도메인:</span>
                            <span class="font-mono bg-gray-200 px-2 py-1 rounded">{{ $domain_name }}</span>
                        </div>
                    @endif
                    @if($screen_name ?? null)
                        <div class="flex">
                            <span class="font-medium w-20">화면:</span>
                            <span class="font-mono bg-gray-200 px-2 py-1 rounded">{{ $screen_name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 제안 -->
            @if(!empty($suggestions))
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">대신 이런 화면은 어떠세요?</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                        @foreach(array_slice($suggestions, 0, 4) as $suggestion)
                            <a href="{{ route('sandbox.screen', [$sandbox_name, $domain_name, $suggestion['name']]) }}" 
                               class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                <div class="font-medium text-gray-900">{{ $suggestion['screen'] }}</div>
                                <div class="text-sm text-gray-500">{{ $suggestion['name'] }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 액션 버튼들 -->
            <div class="space-x-4">
                <button onclick="history.back()" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    이전 페이지
                </button>

                @if($domain_name ?? null)
                    <a href="{{ route('sandbox.screens', [$sandbox_name, $domain_name]) }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        화면 목록
                    </a>
                @elseif($sandbox_name ?? null)
                    <a href="{{ route('sandbox.domains', $sandbox_name) }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        도메인 목록
                    </a>
                @else
                    <a href="{{ route('sandbox.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        </svg>
                        샌드박스 홈
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Filament Scripts -->
    @filamentScripts
</body>
</html>