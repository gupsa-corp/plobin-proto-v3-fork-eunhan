<div class="px-6 py-6">
    <!-- 프로젝트 설정 탭 네비게이션 -->
    @include('300-page-service.314-page-project-settings-name.100-tab-navigation')

    <!-- 알림 메시지 -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- 샌드박스 선택 콘텐츠 -->
    <div class="bg-white rounded-lg border border-gray-200">
        <!-- 헤더 -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">프로젝트 샌드박스 선택</h2>
                    <p class="text-sm text-gray-500 mt-1">이 프로젝트의 모든 페이지에서 사용할 샌드박스를 선택할 수 있습니다.</p>
                </div>
                <a href="{{ route('project.dashboard', ['id' => request()->route('id'), 'projectId' => request()->route('projectId')]) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    프로젝트로 이동
                </a>
            </div>
        </div>

        <!-- 콘텐츠 -->
        <div class="p-6" x-data="sandboxSettingsPage()">
            <form action="{{ route('project.dashboard.project.settings.sandbox.post', ['id' => request()->route('id'), 'projectId' => request()->route('projectId')]) }}" method="POST" class="space-y-6">
                @csrf

                <!-- 샌드박스 목록 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        사용 가능한 샌드박스
                    </label>

                    <div class="space-y-3">
                        <!-- 샌드박스 사용 안함 옵션 -->
                        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                            <input
                                type="radio"
                                id="sandbox_none"
                                name="sandbox"
                                value=""
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                {{ (empty($currentSandboxName)) ? 'checked' : '' }}
                            >
                            <label for="sandbox_none" class="ml-3 flex-1">
                                <div class="font-medium text-gray-900">샌드박스 사용 안함</div>
                                <div class="text-sm text-gray-500">기본 프로젝트 콘텐츠만 표시합니다.</div>
                            </label>
                        </div>

                        <!-- 동적 샌드박스 템플릿 목록 -->
                        <div x-show="loading" class="flex items-center justify-center p-4 border border-gray-200 rounded-lg">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-500">샌드박스 템플릿을 불러오는 중...</span>
                        </div>

                        <div x-show="error && !loading" class="p-4 border border-red-200 rounded-lg bg-red-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">템플릿 로딩 오류</h3>
                                    <p class="text-sm text-red-700 mt-1" x-text="error"></p>
                                </div>
                            </div>
                        </div>

                        <!-- 동적으로 로딩된 샌드박스 템플릿들 -->
                        <template x-for="template in templates" :key="template.name">
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors duration-200">
                                <input
                                    type="radio"
                                    :id="'sandbox_' + template.name"
                                    name="sandbox"
                                    :value="template.name"
                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                    @if(!empty($currentSandboxName))
                                        x-bind:checked="template.name === '{{ $currentSandboxName }}'"
                                    @endif
                                >
                                <label :for="'sandbox_' + template.name" class="ml-3 flex-1 cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900" x-text="template.name"></div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                <span x-text="'파일: ' + template.file_count + '개'"></span>
                                                <span class="mx-2">•</span>
                                                <span x-text="'폴더: ' + template.directory_count + '개'"></span>
                                                <span class="mx-2">•</span>
                                                <span x-text="'생성일: ' + template.created_at"></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                템플릿
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </template>

                        <!-- 템플릿이 없는 경우 -->
                        <div x-show="!loading && !error && templates.length === 0" class="text-center py-6 border border-gray-200 rounded-lg">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">사용 가능한 템플릿이 없습니다</h4>
                            <p class="mt-1 text-xs text-gray-500">서버에서 샌드박스 템플릿을 찾을 수 없습니다.</p>
                        </div>
                    </div>
                </div>

                <!-- 페이지별 개별 샌드박스 허용 설정 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        페이지별 설정
                    </label>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    type="checkbox"
                                    id="allow_individual_sandbox"
                                    name="allow_individual_sandbox_per_page"
                                    value="1"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    {{ (old('allow_individual_sandbox_per_page', $project->allow_individual_sandbox_per_page ?? false)) ? 'checked' : '' }}
                                />
                            </div>
                            <div class="ml-3">
                                <label for="allow_individual_sandbox" class="font-medium text-gray-700">
                                    페이지별로 별개 샌드박스 선택 가능
                                </label>
                                <p class="text-sm text-gray-500 mt-1">
                                    이 옵션을 활성화하면 각 페이지에서 프로젝트 샌드박스와 다른 샌드박스를 개별적으로 선택할 수 있습니다.
                                    비활성화하면 모든 페이지가 프로젝트 샌드박스 설정을 따르게 됩니다.
                                </p>
                                <div class="mt-2 text-xs text-orange-600 bg-orange-50 p-2 rounded">
                                    <strong>주의:</strong> 이 기능을 비활성화하면 기존에 개별 선택된 페이지들이 프로젝트 설정을 따르게 됩니다.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        취소
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        저장
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function sandboxSettingsPage() {
            return {
                templates: [],
                loading: false,
                error: null,

                init() {
                    // 페이지 로드 시 템플릿 목록을 자동으로 불러오기
                    this.loadTemplates();
                },

                async loadTemplates() {
                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch('/api/sandbox/list', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.error) {
                            throw new Error(data.error);
                        }

                        this.templates = data.sandboxes || [];

                    } catch (error) {
                        console.error('템플릿 로딩 에러:', error);
                        this.error = error.message || '템플릿 목록을 불러오는 중 오류가 발생했습니다.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</div>
