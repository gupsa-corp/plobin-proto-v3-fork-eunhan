<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include($common . '.301-layout-head', ['title' => '샌드박스'])
<body class="bg-gray-100">
    @include('700-page-sandbox.700-common.400-sandbox-header')

    <div class="min-h-screen sandbox-container">
        <div class="sandbox-card">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">샌드박스</h1>
            <p class="text-gray-600 mb-8">개발 도구 및 유틸리티 모음</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="/sandbox/dashboard" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">대시보드</h3>
                    <p class="text-gray-600">전체 시스템 현황 및 관리</p>
                </a>

                <a href="/sandbox/sql-executor" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">SQL 실행기</h3>
                    <p class="text-gray-600">데이터베이스 쿼리 실행</p>
                </a>

                <a href="/sandbox/file-list" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">파일 목록</h3>
                    <p class="text-gray-600">프로젝트 파일 탐색</p>
                </a>

                <a href="/sandbox/file-editor" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">파일 에디터</h3>
                    <p class="text-gray-600">코드 파일 편집</p>
                </a>

                <a href="/sandbox/function-browser" class="block bg-white border border-orange-200 rounded-lg p-6 hover:bg-orange-50 transition-colors bg-orange-50">
                    <h3 class="text-lg font-semibold text-orange-900 mb-2">함수 브라우저</h3>
                    <p class="text-orange-700">마이크로서비스 함수 관리 및 테스트</p>
                </a>

                <a href="/sandbox/database-manager" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">데이터베이스 매니저</h3>
                    <p class="text-gray-600">데이터베이스 관리</p>
                </a>

                <a href="{{ route('sandbox.using-projects') }}" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-yellow-200 bg-yellow-50">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-2">📦 샌드박스 사용 프로젝트</h3>
                    <p class="text-yellow-700">현재 샌드박스를 사용하고 있는 프로젝트 목록 확인</p>
                </a>

                <a href="/sandbox/git-version-control" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Git 버전 관리</h3>
                    <p class="text-gray-600">로컬 Git 저장소 관리</p>
                </a>


                <a href="/sandbox/api-list" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">API 목록</h3>
                    <p class="text-gray-600">생성된 API 목록 관리</p>
                </a>

                <a href="/sandbox/blade-creator" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Blade 생성기</h3>
                    <p class="text-gray-600">Blade 템플릿 생성 및 미리보기</p>
                </a>

                <a href="/sandbox/blade-list" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Blade 목록</h3>
                    <p class="text-gray-600">생성된 Blade 템플릿 목록 관리</p>
                </a>


                <a href="/sandbox/file-editor-integrated" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-blue-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">통합 파일 에디터</h3>
                    <p class="text-blue-700">파일 매니저 + Monaco 에디터 통합 개발환경</p>
                </a>

                <a href="/sandbox/form-creator" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-green-200 bg-green-50">
                    <h3 class="text-lg font-semibold text-green-900 mb-2">Form Creator</h3>
                    <p class="text-green-700">비주얼 폼 빌더 - 드래그 앤 드롭으로 폼 생성</p>
                </a>

                <a href="/sandbox/form-publisher" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-purple-200 bg-purple-50">
                    <h3 class="text-lg font-semibold text-purple-900 mb-2">Form Publisher</h3>
                    <p class="text-purple-700">JSON 기반 폼 생성기 - 코드로 폼 구조 정의</p>
                </a>

                <a href="/sandbox/documentation-manager" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-indigo-200 bg-indigo-50">
                    <h3 class="text-lg font-semibold text-indigo-900 mb-2">📚 개발자 문서 관리</h3>
                    <p class="text-indigo-700">프로젝트 문서 작성, 편집 및 관리 - GlobalFunctions, API 가이드 등</p>
                </a>

                <a href="/sandbox/scenario-manager" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-blue-200 bg-blue-50">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">📋 시나리오 관리자</h3>
                    <p class="text-blue-700">개발 시나리오 및 요구사항을 체계적으로 관리하고 추적합니다</p>
                </a>

                <a href="/sandbox/downloads" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-green-200 bg-green-50">
                    <h3 class="text-lg font-semibold text-green-900 mb-2">📥 자료 다운로드</h3>
                    <p class="text-green-700">E2E 테스트용 자료와 샘플 파일을 다운로드할 수 있습니다</p>
                </a>

                <a href="/sandbox/cron-manager" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-orange-200 bg-orange-50">
                    <h3 class="text-lg font-semibold text-orange-900 mb-2">⏰ Cron 관리자</h3>
                    <p class="text-orange-700">스케줄 작업 생성, 관리 및 모니터링</p>
                </a>

                <a href="/sandbox/callback-manager" class="block bg-white border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors border-purple-200 bg-purple-50">
                    <h3 class="text-lg font-semibold text-purple-900 mb-2">🔗 Callback 관리자</h3>
                    <p class="text-purple-700">웹훅과 콜백 URL 관리 및 테스트</p>
                </a>
            </div>
        </div>
    </div>
    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Filament Scripts -->
    @filamentScripts
</body>
</html>
