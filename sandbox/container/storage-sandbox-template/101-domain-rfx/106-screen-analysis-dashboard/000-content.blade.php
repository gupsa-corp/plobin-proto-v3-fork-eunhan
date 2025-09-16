{{-- 파일 분석 대시보드 --}}
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- 헤더 섹션 --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">파일 분석 대시보드</h1>
            <p class="text-gray-600">문서 분석 현황 및 통계를 한눈에 확인하세요</p>
        </div>

        {{-- 통계 카드 섹션 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- 전체 파일 수 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900">0</div>
                <div class="text-sm text-gray-600 mt-1">전체 업로드 파일</div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600 font-medium">구현필요</span>
                </div>
            </div>

            {{-- 분석 완료 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900">0</div>
                <div class="text-sm text-gray-600 mt-1">분석 완료</div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-gray-500">구현필요</span>
                </div>
            </div>

            {{-- 분석 중 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900">0</div>
                <div class="text-sm text-gray-600 mt-1">분석 진행 중</div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-gray-500">구현필요</span>
                </div>
            </div>

            {{-- 분석 대기 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900">0</div>
                <div class="text-sm text-gray-600 mt-1">분석 대기 중</div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-gray-500">구현필요</span>
                </div>
            </div>
        </div>

        {{-- 차트 섹션 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- 일별 분석 추이 차트 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">일별 분석 추이</h3>
                <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                    <span class="text-gray-500">구현필요</span>
                </div>
            </div>

            {{-- 파일 타입별 분포 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">파일 타입별 분포</h3>
                <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                    <span class="text-gray-500">구현필요</span>
                </div>
            </div>
        </div>

        {{-- 최근 분석 활동 --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">최근 분석 활동</h3>
                    <a href="{{ route('sandbox.screen', ['sandbox' => $sandbox_name ?? 'default', 'domain' => $domain_name ?? 'rfx', 'screen' => '104-screen-analysis-requests']) }}"
                       class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                        전체보기 →
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    {{-- 빈 상태 --}}
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500">최근 분석 활동이 없습니다.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 빠른 작업 섹션 --}}
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('sandbox.screen', ['sandbox' => $sandbox_name ?? 'default', 'domain' => $domain_name ?? 'rfx', 'screen' => '101-screen-multi-file-upload']) }}"
               class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-purple-400 hover:bg-purple-50 transition-all text-center group">
                <svg class="w-8 h-8 text-gray-400 group-hover:text-purple-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span class="text-gray-700 font-medium group-hover:text-purple-700">새 파일 업로드</span>
            </a>

            <a href="{{ route('sandbox.screen', ['sandbox' => $sandbox_name ?? 'default', 'domain' => $domain_name ?? 'rfx', 'screen' => '103-screen-uploaded-files-list']) }}"
               class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-blue-400 hover:bg-blue-50 transition-all text-center group">
                <svg class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <span class="text-gray-700 font-medium group-hover:text-blue-700">파일 목록 보기</span>
            </a>

            <a href="{{ route('sandbox.screen', ['sandbox' => $sandbox_name ?? 'default', 'domain' => $domain_name ?? 'rfx', 'screen' => '105-screen-document-analysis']) }}"
               class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-green-400 hover:bg-green-50 transition-all text-center group">
                <svg class="w-8 h-8 text-gray-400 group-hover:text-green-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-gray-700 font-medium group-hover:text-green-700">분석 결과 보기</span>
            </a>
        </div>
    </div>
</div>