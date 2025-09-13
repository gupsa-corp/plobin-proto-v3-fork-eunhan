<div class="space-y-6">
    <!-- 알림 메시지 -->
    <div x-data="{ show: false, message: '' }" 
         x-on:project-added.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
         x-show="show" 
         x-transition
         class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50"
         style="display: none;">
        <span x-text="message"></span>
    </div>
    <!-- 헤더 통계 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 text-sm">📋</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">전체</p>
                    <p class="text-lg font-semibold text-blue-600">{{ $stats['total_projects'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <span class="text-yellow-600 text-sm">🔄</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">진행 중</p>
                    <p class="text-lg font-semibold text-yellow-600">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-green-600 text-sm">✅</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">완료</p>
                    <p class="text-lg font-semibold text-green-600">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <span class="text-red-600 text-sm">🚫</span>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">블록</p>
                    <p class="text-lg font-semibold text-red-600">{{ $stats['blocked'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 컨트롤 -->
    <div class="bg-white p-4 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">프로젝트 칸반 보드</h3>
            <button wire:click="refreshData" 
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                🔄 새로고침
            </button>
        </div>
    </div>

    <!-- 칸반 보드 -->
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 min-h-[500px]">
            @foreach($columns as $column)
                <div class="bg-gray-50 rounded-lg p-3">
                    <!-- 칼럼 헤더 -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full 
                                @if($column['color'] === 'gray') bg-gray-400
                                @elseif($column['color'] === 'blue') bg-blue-400
                                @elseif($column['color'] === 'yellow') bg-yellow-400
                                @elseif($column['color'] === 'purple') bg-purple-400
                                @elseif($column['color'] === 'green') bg-green-400
                                @endif"></div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $column['name'] }}</h4>
                        </div>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">
                            {{ count($projects[$column['id']] ?? []) }}
                        </span>
                    </div>

                    <!-- 프로젝트 카드들 -->
                    <div class="space-y-3">
                        @if(isset($projects[$column['id']]))
                            @foreach($projects[$column['id']] as $project)
                                <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 cursor-move hover:shadow-md transition-shadow">
                                    <!-- 프로젝트 제목 -->
                                    <h5 class="text-sm font-medium text-gray-900 mb-1">{{ $project->name }}</h5>
                                    
                                    <!-- 프로젝트 설명 -->
                                    @if($project->description)
                                        <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ $project->description }}</p>
                                    @endif
                                    
                                    <!-- 메타 정보 -->
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <div class="flex items-center space-x-1">
                                            <span>👤</span>
                                            <span>{{ $project->created_by_name ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <span>🏢</span>
                                            <span class="truncate max-w-20">{{ $project->organization_name ?? '-' }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- 생성일 -->
                                    <div class="mt-2 text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($project->created_at)->diffForHumans() }}
                                    </div>
                                    
                                    <!-- 액션 버튼 -->
                                    <div class="mt-2 flex justify-end space-x-1">
                                        <button class="text-xs text-blue-600 hover:text-blue-800">보기</button>
                                        <button class="text-xs text-green-600 hover:text-green-800">편집</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- 빈 상태 -->
                        @if(empty($projects[$column['id']]))
                            <div class="text-center py-8">
                                <div class="text-gray-300 text-2xl mb-2">📋</div>
                                <p class="text-xs text-gray-400">프로젝트가 없습니다</p>
                            </div>
                        @endif

                        <!-- 새 카드 추가 버튼 -->
                        <button wire:click="openAddProjectModal('{{ $column['id'] }}')" 
                                class="w-full py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-400 hover:border-gray-400 hover:text-gray-600 text-sm">
                            + 새 프로젝트 추가
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 드래그 앤 드롭 안내 -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">드래그 앤 드롭</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>프로젝트 카드를 드래그하여 다른 칼럼으로 이동할 수 있습니다. (개발 예정)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 새 프로젝트 추가 모달 -->
    @if($showAddModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeAddProjectModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- 모달 헤더 -->
                    <div class="flex items-center justify-between pb-3 border-b">
                        <h3 class="text-lg font-medium text-gray-900">새 프로젝트 추가</h3>
                        <button wire:click="closeAddProjectModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- 모달 내용 -->
                    <div class="py-4 space-y-4">
                        <!-- 프로젝트 이름 -->
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">프로젝트 이름 *</label>
                            <input type="text" 
                                   wire:model="newProject.name" 
                                   id="project_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="프로젝트 이름을 입력하세요">
                            @error('newProject.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- 프로젝트 설명 -->
                        <div>
                            <label for="project_description" class="block text-sm font-medium text-gray-700 mb-1">프로젝트 설명</label>
                            <textarea wire:model="newProject.description" 
                                      id="project_description"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="프로젝트에 대한 간단한 설명을 입력하세요"></textarea>
                        </div>

                        <!-- 상태 선택 -->
                        <div>
                            <label for="project_status" class="block text-sm font-medium text-gray-700 mb-1">상태</label>
                            <select wire:model="newProject.status" 
                                    id="project_status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($columns as $column)
                                    <option value="{{ $column['id'] }}">{{ $column['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 조직 선택 -->
                        <div>
                            <label for="project_organization" class="block text-sm font-medium text-gray-700 mb-1">조직</label>
                            <input type="text" 
                                   wire:model="newProject.organization_name" 
                                   id="project_organization"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="조직명을 입력하세요">
                        </div>
                    </div>

                    <!-- 모달 푸터 -->
                    <div class="flex items-center justify-end pt-3 border-t space-x-2">
                        <button wire:click="closeAddProjectModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            취소
                        </button>
                        <button wire:click="addProject" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            추가
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>