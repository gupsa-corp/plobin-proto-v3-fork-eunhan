{{-- Enhanced Permission Overview Livewire Component --}}
<div class="permission-overview-container" style="padding: 24px;" x-data="{
    showDetails: {},
    showBulkActions: @entangle('showQuickActions'),
    selectedCount: @entangle('selectedMembers').length,
    toggleDetails(id) {
        this.showDetails[id] = !this.showDetails[id];
    }
}">

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('organization.admin.permissions.overview', ['id' => $organizationId]) }}"
                   class="whitespace-nowrap py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm">
                    권한 개요
                </a>
                <a href="{{ route('organization.admin.permissions.roles', ['id' => $organizationId]) }}"
                   class="whitespace-nowrap py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                    역할 관리
                </a>
                <a href="{{ route('organization.admin.permissions.management', ['id' => $organizationId]) }}"
                   class="whitespace-nowrap py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm">
                    멤버 권한
                </a>
            </nav>
        </div>
    </div>

    {{-- View Toggle --}}
    <div class="mb-6 flex justify-between items-center">
        <div class="flex space-x-2">
            <button wire:click="switchView('overview')" 
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                           {{ $activeView === 'overview' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                📊 개요
            </button>
            <button wire:click="switchView('matrix')" 
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                           {{ $activeView === 'matrix' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                🔐 권한 매트릭스
            </button>
            <button wire:click="switchView('activity')" 
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                           {{ $activeView === 'activity' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                📈 활동 기록
            </button>
        </div>
        
        <div class="flex items-center space-x-2">
            <button wire:click="toggleQuickActions" 
                    class="px-4 py-2 text-sm font-medium bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                ⚡ 빠른 작업
            </button>
            <button wire:click="loadData" 
                    class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                🔄 새로고침
            </button>
        </div>
    </div>

    {{-- Bulk Actions Panel --}}
    @if($showQuickActions)
        <div class="mb-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium text-purple-900">빠른 작업</h4>
                <span class="text-sm text-purple-600" x-show="selectedCount > 0" x-text="`${selectedCount}명 선택됨`"></span>
            </div>
            <div class="flex items-end space-x-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-purple-700 mb-1">작업</label>
                    <select wire:model="bulkAction" class="w-full p-2 text-sm border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">작업 선택...</option>
                        <option value="assign_role">역할 할당</option>
                        <option value="remove_role">역할 제거</option>
                    </select>
                </div>
                @if($bulkAction && in_array($bulkAction, ['assign_role', 'remove_role']))
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-purple-700 mb-1">역할</label>
                        <select wire:model="bulkRole" class="w-full p-2 text-sm border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">역할 선택...</option>
                            @foreach($availableRoles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button wire:click="applyBulkAction" 
                        class="px-6 py-2 bg-purple-500 text-white text-sm font-medium rounded-lg hover:bg-purple-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="selectedCount === 0 || !@this.bulkAction">
                    적용
                </button>
            </div>
        </div>
    @endif

    {{-- Overview View --}}
    @if($activeView === 'overview')
        {{-- Enhanced Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['totalMembers'] }}</div>
                        <div class="text-sm text-gray-600">총 멤버</div>
                        <div class="text-xs text-green-600 mt-1">
                            활성: {{ $stats['activeMembers'] }}명
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['totalRoles'] }}</div>
                        <div class="text-sm text-gray-600">활성 역할</div>
                        <div class="text-xs text-blue-600 mt-1">
                            평균 권한: {{ $stats['averagePermissions'] }}개
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['totalPermissions'] }}</div>
                        <div class="text-sm text-gray-600">사용 가능한 권한</div>
                        <div class="text-xs text-purple-600 mt-1">
                            커버리지: {{ $stats['permissionCoverage'] }}%
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full mr-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['recentActivity'] }}</div>
                        <div class="text-sm text-gray-600">최근 활동</div>
                        <div class="text-xs text-orange-600 mt-1">
                            지난 7일간
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Distribution Chart --}}
        @if(!empty($stats['roleDistribution']))
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">역할 분포</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($stats['roleDistribution'] as $roleName => $roleData)
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto bg-{{ $roleData['color'] }}-100 rounded-full flex items-center justify-center mb-2">
                                <span class="text-2xl font-bold text-{{ $roleData['color'] }}-600">{{ $roleData['count'] }}</span>
                            </div>
                            <div class="text-sm font-medium text-gray-900">{{ $roleData['label'] }}</div>
                            <div class="text-xs text-gray-500">{{ $roleName }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Search and Filter --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-md">
                    <label class="block text-sm font-medium text-gray-700 mb-1">멤버 검색</label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchTerm" 
                           placeholder="이름 또는 이메일로 검색..."
                           class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">역할 필터</label>
                        <select wire:model.live="selectedRole" 
                                class="p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">모든 역할</option>
                            @foreach($availableRoles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enhanced Member List --}}
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">멤버별 권한 현황</h3>
                <div class="text-sm text-gray-500">
                    총 {{ count($members) }}명
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            @if($showQuickActions)
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" 
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           @change="
                                               if($event.target.checked) {
                                                   @this.selectedMembers = {{ json_encode(collect($members)->pluck('id')->toArray()) }};
                                               } else {
                                                   @this.selectedMembers = [];
                                               }
                                           ">
                                </th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">멤버</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">역할</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">권한 수</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">멤버관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">프로젝트관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">결제관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">조직설정</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($members as $member)
                            <tr class="hover:bg-gray-50">
                                @if($showQuickActions)
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" 
                                               wire:click="toggleMemberSelection({{ $member['id'] }})"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                               {{ in_array($member['id'], $selectedMembers) ? 'checked' : '' }}>
                                    </td>
                                @endif
                                <td class="px-4 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-sm font-medium text-gray-700">{{ substr($member['name'], 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $member['email'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $member['last_activity'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                 bg-{{ $member['role_color'] }}-100 text-{{ $member['role_color'] }}-800">
                                        {{ $member['role_label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $member['permission_count'] }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($member['permissions']['member'])
                                        <span class="text-green-600 text-lg">✓</span>
                                    @else
                                        <span class="text-gray-300 text-lg">○</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($member['permissions']['project'])
                                        <span class="text-green-600 text-lg">✓</span>
                                    @else
                                        <span class="text-gray-300 text-lg">○</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($member['permissions']['billing'])
                                        <span class="text-green-600 text-lg">✓</span>
                                    @else
                                        <span class="text-gray-300 text-lg">○</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($member['permissions']['organization'])
                                        <span class="text-green-600 text-lg">✓</span>
                                    @else
                                        <span class="text-gray-300 text-lg">○</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button @click="toggleDetails({{ $member['id'] }})"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                            상세보기
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            {{-- Member Details Row --}}
                            <tr x-show="showDetails[{{ $member['id'] }}]" 
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                style="display: none;">
                                <td colspan="{{ $showQuickActions ? '9' : '8' }}" class="px-4 py-4 bg-gray-50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">할당된 역할</h4>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($member['roles'] as $roleName)
                                                    @php $roleInfo = $this->getRoleDisplayInfo($roleName) @endphp
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                                                                 bg-{{ $roleInfo['color'] }}-100 text-{{ $roleInfo['color'] }}-800">
                                                        {{ $roleInfo['label'] }}
                                                        <button wire:click="removeRoleFromUser({{ $member['id'] }}, '{{ $roleName }}')"
                                                                class="ml-1 text-{{ $roleInfo['color'] }}-600 hover:text-{{ $roleInfo['color'] }}-800">×</button>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">직접 권한</h4>
                                            <div class="text-xs text-gray-600">
                                                @if(count($member['direct_permissions']) > 0)
                                                    {{ implode(', ', $member['direct_permissions']) }}
                                                @else
                                                    직접 권한 없음
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex space-x-2">
                                        @foreach($availableRoles as $role)
                                            @if(!in_array($role->name, $member['roles']))
                                                <button wire:click="quickAssignRole({{ $member['id'] }}, '{{ $role->name }}')"
                                                        class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                    + {{ $role->name }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Permission Matrix View --}}
    @if($activeView === 'matrix')
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">권한 매트릭스</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">역할</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">멤버 관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">프로젝트 관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">결제 관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">조직 설정</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">권한 관리</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">기타</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($permissionMatrix as $roleName => $roleData)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                     bg-{{ $roleData['info']['color'] }}-100 text-{{ $roleData['info']['color'] }}-800">
                                            {{ $roleData['info']['label'] }}
                                        </span>
                                    </div>
                                </td>
                                @foreach(['멤버 관리', '프로젝트 관리', '결제 관리', '조직 설정', '권한 관리', '기타'] as $category)
                                    <td class="px-4 py-4 text-center">
                                        @php $categoryData = $roleData['categories'][$category] ?? ['count' => 0, 'total' => 0, 'percentage' => 0] @endphp
                                        <div class="flex items-center justify-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $categoryData['count'] }}/{{ $categoryData['total'] }}
                                            </div>
                                            @if($categoryData['percentage'] > 0)
                                                <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" 
                                                         style="width: {{ $categoryData['percentage'] }}%"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Activity Timeline View --}}
    @if($activeView === 'activity')
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">최근 활동 기록</h3>
            <div class="space-y-4">
                @foreach($recentActivity as $activity)
                    <div class="flex items-start space-x-4 p-4 
                                {{ $activity['type'] === 'success' ? 'bg-green-50 border-l-4 border-green-400' : 
                                   ($activity['type'] === 'warning' ? 'bg-yellow-50 border-l-4 border-yellow-400' : 
                                   'bg-blue-50 border-l-4 border-blue-400') }} 
                                rounded-lg">
                        <div class="flex-shrink-0">
                            @if($activity['type'] === 'success')
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @elseif($activity['type'] === 'warning')
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">{{ $activity['details'] }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                사용자: {{ $activity['user'] }} • {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Custom Styles --}}
    <style>
    .permission-overview-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }
    
    /* Animation for bulk actions panel */
    .bulk-actions-panel {
        transition: all 0.3s ease-in-out;
    }
    
    /* Hover effects for cards */
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Custom scrollbar for tables */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>

</div>