<div class="space-y-6">
    <!-- 커스텀 화면 설정 -->
    <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-900">커스텀 화면 설정</h3>
        
        <!-- 커스텀 화면 사용 안함 -->
        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
            <input
                type="radio"
                id="custom_screen_disabled"
                name="custom_screen_mode"
                value="disabled"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                x-model="customScreenMode"
                @change="onCustomScreenModeChange()"
                {{ empty($currentCustomScreenId) ? 'checked' : '' }}
            >
            <label for="custom_screen_disabled" class="ml-3 flex-1">
                <div class="font-medium text-gray-900">사용 안함</div>
                <div class="text-sm text-gray-500">기본 페이지 레이아웃을 사용합니다.</div>
            </label>
        </div>

        <!-- 커스텀 화면 사용함 -->
        <div class="flex items-center p-4 border border-gray-200 rounded-lg">
            <input
                type="radio"
                id="custom_screen_enabled"
                name="custom_screen_mode"
                value="enabled"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                x-model="customScreenMode"
                @change="onCustomScreenModeChange()"
                {{ !empty($currentCustomScreenId) ? 'checked' : '' }}
            >
            <label for="custom_screen_enabled" class="ml-3 flex-1">
                <div class="font-medium text-gray-900">사용함</div>
                <div class="text-sm text-gray-500">커스텀 화면을 선택하여 사용합니다.</div>
            </label>
        </div>
    </div>

    <!-- 커스텀 화면 선택 UI (사용함일 때만 표시) -->
    <div x-show="customScreenMode === 'enabled'" class="border border-gray-200 rounded-lg p-6 space-y-4">
        <h4 class="text-md font-medium text-gray-900">커스텀 화면 선택</h4>
        
        <!-- 1단계: 샌드박스 모드 선택 -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">1. 샌드박스 모드 선택</label>
            <div class="space-y-3">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            id="sandbox-mode-project"
                            name="sandbox-mode"
                            value="project"
                            x-model="sandboxMode"
                            @change="onSandboxModeChange()"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="sandbox-mode-project" class="font-medium text-gray-700">프로젝트를 따름</label>
                        <p class="text-gray-500">프로젝트에서 설정한 샌드박스를 사용합니다.</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            type="radio"
                            id="sandbox-mode-individual"
                            name="sandbox-mode"
                            value="individual"
                            x-model="sandboxMode"
                            @change="onSandboxModeChange()"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                            :disabled="!projectAllowsIndividualSandbox"
                        />
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="sandbox-mode-individual" class="font-medium" :class="projectAllowsIndividualSandbox ? 'text-gray-700' : 'text-gray-400'">
                            페이지별 개별 선택
                        </label>
                        <p class="text-gray-500">
                            <span x-show="projectAllowsIndividualSandbox">이 페이지만의 고유한 샌드박스를 선택합니다.</span>
                            <span x-show="!projectAllowsIndividualSandbox" class="text-red-500">프로젝트 설정에서 개별 선택을 허용해야 사용할 수 있습니다.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2단계: 샌드박스 선택 (개별 선택 모드일 때만) -->
        <div x-show="sandboxMode === 'individual'">
            <label class="block text-sm font-medium text-gray-700 mb-2">2. 샌드박스 선택</label>
            <select
                x-model="selectedSandbox"
                @change="loadDomains()"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">샌드박스를 선택하세요</option>
                <template x-for="sandbox in sandboxes" :key="sandbox.name">
                    <option :value="sandbox.name" x-text="sandbox.title"></option>
                </template>
            </select>
        </div>

        <!-- 3단계: 도메인 선택 -->
        <div x-show="shouldShowDomainSelection()">
            <label class="block text-sm font-medium text-gray-700 mb-2">3. 도메인 선택</label>
            <select
                x-model="selectedDomain"
                @change="loadScreens()"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">도메인을 선택하세요</option>
                <template x-for="domain in domains" :key="domain.folder">
                    <option :value="domain.folder" x-text="domain.title"></option>
                </template>
            </select>
        </div>

        <!-- 4단계: 화면 선택 -->
        <div x-show="selectedDomain">
            <label class="block text-sm font-medium text-gray-700 mb-2">4. 화면 선택</label>
            <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto">
                <template x-for="screen in filteredScreens" :key="screen.id">
                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <input
                            type="radio"
                            :id="'custom_screen_' + screen.id"
                            name="custom_screen"
                            :value="screen.id"
                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                            x-model="selectedCustomScreen"
                        >
                        <label :for="'custom_screen_' + screen.id" class="ml-3 flex-1">
                            <div class="font-medium text-gray-900" x-text="screen.title"></div>
                            <div class="text-sm text-gray-500" x-text="screen.description"></div>
                            <div class="flex items-center space-x-2 text-xs text-gray-400 mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-blue-100 text-blue-800': screen.type === 'screen'
                                      }">
                                    <span x-text="screen.type.toUpperCase()"></span>
                                </span>
                                <button
                                    type="button"
                                    @click="previewScreen(screen.id)"
                                    class="text-blue-600 hover:text-blue-800 underline"
                                >
                                    미리보기
                                </button>
                            </div>
                        </label>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 로딩 상태 -->
    <div x-show="loading" class="flex items-center p-4 border border-blue-200 rounded-lg bg-blue-50">
        <div class="flex-shrink-0">
            <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="ml-3">
            <div class="text-sm font-medium text-blue-800">화면 목록을 로드하고 있습니다...</div>
            <div class="text-sm text-blue-600">잠시만 기다려주세요.</div>
        </div>
    </div>

    <!-- 에러 상태 -->
    <div x-show="error" class="flex items-center p-4 border border-red-200 rounded-lg bg-red-50">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <div class="text-sm font-medium text-red-800">화면 목록을 로드할 수 없습니다</div>
            <div class="text-sm text-red-600" x-text="error"></div>
        </div>
    </div>

    <!-- 화면이 없을 때 메시지 -->
    <div x-show="selectedDomain && filteredScreens.length === 0" class="flex items-center p-4 border border-yellow-200 rounded-lg bg-yellow-50">
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="ml-3">
            <div class="text-sm font-medium text-yellow-800">해당 도메인에 사용 가능한 화면이 없습니다</div>
            <div class="text-sm text-yellow-600">선택된 도메인에 화면 파일이 없습니다.</div>
        </div>
    </div>
</div>