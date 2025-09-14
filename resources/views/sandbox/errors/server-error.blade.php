@extends('layouts.app')

@section('content')
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $sandbox_name }}</span>
                        </div>
                    </li>
                @endif
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">서버 오류</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- 서버 에러 섹션 -->
        <div class="text-center">
            <!-- 에러 아이콘 -->
            <div class="mx-auto flex items-center justify-center h-32 w-32 rounded-full bg-red-100 mb-8">
                <svg class="h-16 w-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $error_code ?? 500 }} - 서버 오류</h1>
            <p class="text-lg text-gray-600 mb-8">{{ $error_message ?? '서버에서 오류가 발생했습니다.' }}</p>
            
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
                    <div class="flex">
                        <span class="font-medium w-20">오류 코드:</span>
                        <span class="font-mono bg-red-100 text-red-800 px-2 py-1 rounded">{{ $error_code ?? 500 }}</span>
                    </div>
                    <div class="flex">
                        <span class="font-medium w-20">발생 시간:</span>
                        <span class="font-mono bg-gray-200 px-2 py-1 rounded">{{ now()->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            </div>

            <!-- 해결 방법 제안 -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 text-left max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    해결 방법
                </h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-400 rounded-full mt-1.5 mr-3 flex-shrink-0"></span>
                        페이지를 새로고침해 보세요.
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-400 rounded-full mt-1.5 mr-3 flex-shrink-0"></span>
                        잠시 후 다시 시도해 보세요.
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-400 rounded-full mt-1.5 mr-3 flex-shrink-0"></span>
                        문제가 지속되면 관리자에게 문의하세요.
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-blue-400 rounded-full mt-1.5 mr-3 flex-shrink-0"></span>
                        브라우저 캐시를 삭제해 보세요.
                    </li>
                </ul>
            </div>

            <!-- 액션 버튼들 -->
            <div class="space-x-4">
                <button onclick="window.location.reload()" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    새로고침
                </button>

                <button onclick="history.back()" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    이전 페이지
                </button>

                <a href="{{ route('sandbox.index') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                    샌드박스 홈
                </a>
            </div>
        </div>
    </div>
</div>
@endsection