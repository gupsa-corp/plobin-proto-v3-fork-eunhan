<!-- 내 페이지 목록 블록 -->
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">최근 페이지</h3>
            <a href="/organizations" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                전체 보기
            </a>
        </div>
    </div>
    
    <div class="px-6 py-4">
        @if(isset($pages) && $pages->count() > 0)
            <div class="space-y-4">
                @foreach($pages as $page)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $page->title }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $page->project_name }} • {{ $page->organization_name }}
                                    </p>
                                    @if($page->content)
                                        <p class="text-xs text-gray-400 mt-1 truncate">
                                            {{ Str::limit(strip_tags($page->content), 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="text-right">
                                <p class="text-xs text-gray-400">
                                    {{ $page->updated_at->diffForHumans() }}
                                </p>
                            </div>
                            @php
                                // 페이지의 샌드박스 설정 확인
                                $pageModel = \App\Models\ProjectPage::find($page->id);
                                $sandboxCustomScreen = $pageModel ? $pageModel->sandbox_custom_screen_folder : null;
                                
                                // 새로운 샌드박스 템플릿 URL 생성 로직
                                $sandboxContextService = app(App\Services\SandboxContextService::class);
                                $currentSandbox = $sandboxContextService->getCurrentSandbox();
                                if ($sandboxCustomScreen && $pageModel && $pageModel->project && $pageModel->project->sandbox_folder === $currentSandbox) {
                                    // 003-screen-table-view -> 100-domain-pms/103-screen-table-view 형태로 매핑
                                    // 실제 API에서 매칭되는 화면을 찾아야 함
                                    $newUrl = "/sandbox/{$currentSandbox}/100-domain-pms/103-screen-table-view";
                                } else {
                                    $newUrl = route('project.dashboard.page', ['id' => $page->organization_id, 'projectId' => $page->project_id, 'pageId' => $page->id]);
                                }
                            @endphp
                            <a href="{{ $newUrl }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                열기
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">페이지가 없습니다</h3>
                <p class="mt-1 text-sm text-gray-500">프로젝트에서 새로운 페이지를 만들어보세요.</p>
                <div class="mt-6">
                    <a href="/organizations" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        프로젝트 보기
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>