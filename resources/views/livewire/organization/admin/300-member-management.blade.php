{{--
===========================================
개발 가이드라인 (DEVELOPMENT GUIDELINES)
===========================================

⚠️ 중요: 이 프로젝트에서는 순수 JavaScript 사용을 금지합니다
❌ 사용 금지: Vanilla JS, jQuery, Alpine.js의 복잡한 로직
✅ 사용 필수: Livewire + Filament 조합만 사용

모든 상호작용과 동적 기능은 다음으로만 구현:
- Livewire: 서버사이드 상태관리, 이벤트 처리
- Filament: UI 컴포넌트, 폼, 테이블 등
- 간단한 Alpine.js: 토글, 드롭다운 등 최소한의 UI 상호작용만

JavaScript가 필요한 경우 → Livewire로 재작성 필수
복잡한 UI가 필요한 경우 → Filament 컴포넌트 사용

===========================================
--}}

<div class="member-management-content" style="padding: 24px;">

    {{-- 검색 및 필터 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">멤버 검색</label>
                <input 
                    type="text" 
                    id="search"
                    wire:model.live="searchTerm"
                    placeholder="이름, 이메일로 검색..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="roleFilter" class="block text-sm font-medium text-gray-700 mb-2">역할 필터</label>
                <select 
                    id="roleFilter"
                    wire:model.live="permissionFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">모든 역할</option>
                    @foreach($availableRoles as $role)
                        <option value="{{ $role['name'] }}">{{ $role['display_name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">상태 필터</label>
                <select 
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">모든 상태</option>
                    <option value="active">활성</option>
                    <option value="inactive">비활성</option>
                </select>
            </div>

            <div class="flex items-end">
                <button 
                    wire:click="resetFilters"
                    class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    초기화
                </button>
            </div>
        </div>
    </div>

    {{-- 멤버 목록 테이블 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">조직 멤버 ({{ count($filteredMembers) }}명)</h3>
            <button
                wire:click="openInviteModal"
                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                + 멤버 초대
            </button>
        </div>

        @if(count($filteredMembers) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                멤버
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                역할
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                권한 레벨
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                가입일
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                상태
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                작업
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($filteredMembers as $member)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                <span class="text-white font-medium text-sm">
                                                    {{ strtoupper(substr($member['name'], 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $member['email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @switch($member['permission']['badge_color'])
                                            @case('blue')
                                                bg-blue-100 text-blue-800
                                                @break
                                            @case('green')
                                                bg-green-100 text-green-800
                                                @break
                                            @case('purple')
                                                bg-purple-100 text-purple-800
                                                @break
                                            @case('red')
                                                bg-red-100 text-red-800
                                                @break
                                            @case('gray')
                                                bg-gray-100 text-gray-800
                                                @break
                                            @default
                                                bg-yellow-100 text-yellow-800
                                        @endswitch
                                    ">
                                        {{ $member['permission']['short_label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    레벨 {{ $member['permission']['level'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $member['joined_at'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        활성
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        wire:click="editMember({{ $member['user_id'] }})"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        편집
                                    </button>
                                    @if($member['permission']['level'] < 4)
                                        <button 
                                            wire:click="removeMember({{ $member['id'] }})"
                                            class="text-red-600 hover:text-red-900">
                                            제거
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0122 34c3.292 0 6.16 1.595 7.287 4.286" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-900">멤버가 없습니다</p>
                    <p class="mt-2 text-sm text-gray-500">
                        @if($searchTerm || $permissionFilter || $statusFilter)
                            검색 조건에 맞는 멤버가 없습니다.
                        @else
                            아직 조직에 멤버가 없습니다.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- 페이지네이션 (필요한 경우) --}}
    @if(method_exists($this, 'links') && $this->links())
        <div class="mt-6">
            {{ $this->links() }}
        </div>
    @endif

</div>

{{-- 멤버 편집 모달 --}}
@if($showEditModal && $this->editingMember)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeEditModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white" wire:click.stop>
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">멤버 편집</h3>
            <div class="mt-4 text-left">
                {{-- 멤버 정보 --}}
                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                            <span class="text-white font-medium text-sm">
                                {{ strtoupper(substr($this->editingMember->user->name ?? $this->editingMember->user->email, 0, 1)) }}
                            </span>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ $this->editingMember->user->name ?? $this->editingMember->user->email }}</div>
                            <div class="text-sm text-gray-500">{{ $this->editingMember->user->email }}</div>
                        </div>
                    </div>
                </div>

                {{-- 역할 선택 --}}
                <div class="mb-4">
                    <label for="editRole" class="block text-sm font-medium text-gray-700 mb-2">역할</label>
                    <select 
                        id="editRole"
                        wire:model="editingMemberRole"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($availableRoles as $role)
                            @if($role['name'] !== 'organization_owner' || (auth()->check() && auth()->user()->hasRole('organization_owner')))
                                <option value="{{ $role['name'] }}">{{ $role['display_name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- 권한 관리 --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">권한</label>
                    <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-md p-2">
                        @foreach($availablePermissions as $permission => $label)
                            <label class="flex items-center mb-2">
                                <input 
                                    type="checkbox" 
                                    value="{{ $permission }}"
                                    @if(in_array($permission, $editingMemberPermissions)) checked @endif
                                    wire:click="togglePermission('{{ $permission }}')"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 버튼 --}}
            <div class="flex justify-end space-x-2 mt-4">
                <button 
                    wire:click="closeEditModal"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    취소
                </button>
                <button 
                    wire:click="updateMember"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    저장
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 멤버 초대 모달 --}}
@if($showInviteModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeInviteModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white" wire:click.stop>
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">멤버 초대</h3>
            <div class="mt-4 text-left">
                {{-- 이메일 입력 --}}
                <div class="mb-4">
                    <label for="inviteEmail" class="block text-sm font-medium text-gray-700 mb-2">이메일 주소</label>
                    <input 
                        type="email" 
                        id="inviteEmail"
                        wire:model="inviteEmail"
                        placeholder="초대할 사용자의 이메일 주소"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- 역할 선택 --}}
                <div class="mb-4">
                    <label for="inviteRole" class="block text-sm font-medium text-gray-700 mb-2">역할</label>
                    <select 
                        id="inviteRole"
                        wire:model="inviteRole"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($availableRoles as $role)
                            @if($role['name'] !== 'organization_owner' || (auth()->check() && auth()->user()->hasRole('organization_owner')))
                                <option value="{{ $role['name'] }}">{{ $role['display_name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 버튼 --}}
            <div class="flex justify-end space-x-2 mt-4">
                <button 
                    wire:click="closeInviteModal"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    취소
                </button>
                <button 
                    wire:click="inviteMember"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    초대 전송
                </button>
            </div>
        </div>
    </div>
</div>
@endif