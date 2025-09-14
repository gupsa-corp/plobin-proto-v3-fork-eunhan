@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- 헤더 -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">샌드박스 컨테이너</h1>
            <p class="text-gray-600">동적으로 생성된 샌드박스 환경을 탐색하고 관리하세요.</p>
        </div>

        <!-- 통계 카드 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">총 샌드박스</div>
                        <div class="text-2xl font-bold text-gray-900">{{ count($grouped_routes) }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">총 라우트</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $total_routes }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">활성 상태</div>
                        <div class="text-2xl font-bold text-gray-900">정상</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 샌드박스 목록 -->
        @if(!empty($grouped_routes))
            <div class="space-y-6">
                @foreach($grouped_routes as $sandboxName => $routes)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $sandboxName }}</h3>
                                    <p class="text-sm text-gray-500">{{ count($routes) }}개 라우트 • 도메인 기반 구조</p>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="{{ route('sandbox.domains', $sandboxName) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        탐색
                                    </a>
                                    <button type="button" onclick="showRoutes('{{ $sandboxName }}')"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        상세보기
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 라우트 상세 정보 (토글 가능) -->
                        <div id="routes-{{ $sandboxName }}" class="hidden px-6 py-4">
                            <div class="space-y-3">
                                @php
                                    $groupedByDomain = collect($routes)->groupBy('domain');
                                @endphp
                                @foreach($groupedByDomain as $domain => $domainRoutes)
                                    <div class="border-l-4 border-blue-400 pl-4">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ $domain }}</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($domainRoutes as $route)
                                                <a href="{{ $route['path'] }}" 
                                                   class="block px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded hover:bg-gray-100 transition-colors">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-mono">{{ $route['screen'] }}</span>
                                                        @if($route['metadata']['has_blade_file'] ?? false)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                활성
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                                없음
                                                            </span>
                                                        @endif
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- 빈 상태 -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">샌드박스가 없습니다</h3>
                <p class="mt-1 text-sm text-gray-500">아직 생성된 샌드박스 컨테이너가 없습니다.</p>
                <div class="mt-6">
                    <button type="button" onclick="window.location.reload()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        새로고침
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function showRoutes(sandboxName) {
    const element = document.getElementById('routes-' + sandboxName);
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
    } else {
        element.classList.add('hidden');
    }
}
</script>
@endsection