{{-- 프로젝트 대시보드 통일 헤더 --}}
@php
    $orgId = request()->route('id');
    $projectId = request()->route('projectId');
    $pageId = request()->route('pageId');
    $organization = \App\Models\Organization::find($orgId);
    $project = \App\Models\Project::find($projectId);
    $page = \App\Models\ProjectPage::find($pageId);

    // 컨트롤러에서 전달받은 샌드박스 정보 사용
    $sandboxInfo = $sandboxInfo ?? null;
    $hasSandbox = $sandboxInfo['has_sandbox'] ?? false;
    $hasCustomScreen = $sandboxInfo['has_custom_screen'] ?? false;
    $sandboxName = $sandboxInfo['sandbox_name'] ?? null;
    $sandboxLevel = $sandboxInfo['sandbox_level'] ?? null;
    $customScreen = $customScreen ?? null;
    $customScreenFolder = $sandboxInfo['custom_screen_folder'] ?? ($page->custom_screen_folder ?? null);
@endphp

<div class="bg-white border-b border-gray-200">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            {{-- 페이지 타이틀과 브레드크럼 --}}
            <div>
                {{-- 페이지 타이틀 --}}
                @if($project)
                    <h1 class="text-xl font-semibold text-gray-900">{{ $project->name }} 대시보드</h1>
                @else
                    <h1 class="text-xl font-semibold text-gray-900">프로젝트 대시보드</h1>
                @endif

                {{-- 브레드크럼 --}}
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                    <a href="/organizations" class="hover:text-gray-700 transition-colors">조직</a>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if($organization)
                        <a href="/organizations/{{ $orgId }}/dashboard" class="hover:text-gray-700 transition-colors">{{ $organization->name }}</a>
                    @else
                        <span class="text-gray-400">조직</span>
                    @endif
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="/organizations/{{ $orgId }}/projects" class="hover:text-gray-700 transition-colors">프로젝트</a>
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if($project)
                        <span class="text-gray-900 font-medium">{{ $project->name }}</span>
                    @else
                        <span class="text-gray-900 font-medium">프로젝트 대시보드</span>
                    @endif
                </nav>
            </div>

            {{-- 헤더 우측 메뉴 --}}
            <div class="flex items-center gap-4">
                {{-- Livewire 사용자 드롭다운 컴포넌트 --}}
                <livewire:service.header.user-dropdown-livewire />
            </div>
        </div>
    </div>

</div>
