{{-- 대시보드 메인 콘텐츠 --}}
<div class="dashboard-content" style="padding: 24px;" x-data="dashboardMain">
    {{-- 환영 메시지 --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">대시보드</h1>
        <p class="mt-1 text-sm text-gray-600">안녕하세요! 오늘도 좋은 하루 되세요.</p>
    </div>

    {{-- 대시보드 블록들을 그리드로 배치 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- 프로젝트 목록 블록 --}}
        <div>
            @include('300-page-service.301-page-dashboard.250-block-projects-list')
        </div>

        {{-- 조직 목록 블록 --}}
        <div>
            @include('300-page-service.301-page-dashboard.260-block-organizations-list')
        </div>
    </div>

    {{-- 최근 페이지 블록 (전체 너비) --}}
    <div class="mb-8">
        @include('300-page-service.301-page-dashboard.270-block-pages-list')
    </div>
</div>
