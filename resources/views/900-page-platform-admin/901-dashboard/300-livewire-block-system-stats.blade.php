{{-- 시스템 통계 카드들 --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    {{-- 조직 통계 --}}
    @include('900-page-platform-admin.901-page-dashboard.301-1-livewire-block-org-stats')

    {{-- 사용자 통계 --}}
    @include('900-page-platform-admin.901-page-dashboard.301-2-livewire-block-user-stats')

    {{-- 프로젝트 통계 블록 --}}
    @include('900-page-platform-admin.901-page-dashboard.302-livewire-block-project-stats')
</div>
