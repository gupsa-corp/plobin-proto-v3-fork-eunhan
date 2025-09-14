@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
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
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $sandbox_name ?? 'sandbox' }}</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $domain_name ?? 'domain' }}</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-900 md:ml-2">{{ $screen_name ?? 'dashboard' }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- 헤더 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $page_title ?? 'PMS 대시보드' }}</h1>
            <p class="text-gray-600">동적 라우팅으로 로드된 샌드박스 화면입니다.</p>
        </div>

        <!-- 시스템 정보 카드 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">시스템 정보</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">샌드박스:</span>
                            <span class="text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $sandbox_name ?? 'storage-sandbox-template' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">도메인:</span>
                            <span class="text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $domain_name ?? '100-domain-pms' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">화면:</span>
                            <span class="text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $screen_name ?? '100-screen-dashboard' }}</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">라우트:</span>
                            <span class="text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $current_route ?? '/sandbox/...' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">요청 방식:</span>
                            <span class="text-gray-900 font-mono bg-{{ ($is_post_request ?? false) ? 'yellow' : 'green' }}-100 px-2 py-1 rounded">
                                {{ ($is_post_request ?? false) ? 'POST' : 'GET' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">접속 시간:</span>
                            <span class="text-gray-900">{{ now()->format('Y-m-d H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 기능 카드들 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gray-900">데이터 분석</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">프로젝트 데이터를 분석하고 인사이트를 제공합니다.</p>
                    <div class="text-2xl font-bold text-blue-600">125</div>
                    <div class="text-sm text-gray-500">활성 프로젝트</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a2 2 0 01-2-2v-2a3 3 0 015.356-1.857A7.029 7.029 0 007 20zm4-16a3 3 0 110 6 3 3 0 010-6z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gray-900">팀 관리</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">팀 멤버와 역할을 관리합니다.</p>
                    <div class="text-2xl font-bold text-green-600">48</div>
                    <div class="text-sm text-gray-500">활성 멤버</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gray-900">작업 추적</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">프로젝트 작업 진행상황을 추적합니다.</p>
                    <div class="text-2xl font-bold text-purple-600">892</div>
                    <div class="text-sm text-gray-500">완료된 작업</div>
                </div>
            </div>
        </div>

        <!-- 요청 데이터 -->
        @if(!empty($request_data))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">요청 데이터</h3>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-100 rounded-lg p-4 text-sm text-gray-800 overflow-x-auto">{{ json_encode($request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif

        <!-- 동작 테스트 -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">동적 라우팅 테스트</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">POST 요청 테스트</h4>
                        <form method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">테스트 데이터</label>
                                <input type="text" name="test_data" value="동적 라우팅 테스트" 
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">메모</label>
                                <textarea name="memo" rows="2" 
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">샌드박스에서 POST 요청 처리 테스트</textarea>
                            </div>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                전송
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">네비게이션 테스트</h4>
                        <div class="space-y-2">
                            <a href="{{ route('sandbox.domains', $sandbox_name ?? 'storage-sandbox-template') }}" 
                               class="block w-full px-4 py-2 text-left text-sm text-blue-600 hover:bg-blue-50 rounded-md border border-blue-200">
                                도메인 목록으로 이동
                            </a>
                            <a href="{{ route('sandbox.screens', [$sandbox_name ?? 'storage-sandbox-template', $domain_name ?? '100-domain-pms']) }}" 
                               class="block w-full px-4 py-2 text-left text-sm text-green-600 hover:bg-green-50 rounded-md border border-green-200">
                                화면 목록으로 이동
                            </a>
                            <a href="{{ route('sandbox.index') }}" 
                               class="block w-full px-4 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 rounded-md border border-gray-200">
                                샌드박스 홈으로 이동
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection