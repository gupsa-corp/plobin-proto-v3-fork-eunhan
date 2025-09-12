{{-- 사용자 권한 테이블 --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">사용자</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">플랫폼 역할</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">조직 권한</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">마지막 로그인</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
            </tr>
        </thead>
        <tbody id="usersTableBody" class="bg-white divide-y divide-gray-200">
            @forelse($users as $user)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ substr($user->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{-- 플랫폼 레벨 역할 표시 --}}
                    @if($user->roles->isNotEmpty())
                        @foreach($user->roles as $role)
                            @php
                                $roleColor = match($role->name) {
                                    'platform_admin' => 'bg-red-100 text-red-800',
                                    'organization_admin' => 'bg-blue-100 text-blue-800',
                                    'organization_member' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $roleText = match($role->name) {
                                    'platform_admin' => '플랫폼 관리자',
                                    'organization_admin' => '조직 관리자',
                                    'organization_member' => '조직 멤버',
                                    default => $role->name
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColor }} mr-1">
                                {{ $roleText }}
                            </span>
                        @endforeach
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            플랫폼 역할 없음
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    {{-- 조직별 권한 표시 --}}
                    <div class="space-y-1">
                        @forelse($user->organizationMemberships as $membership)
                            @php
                                $roleName = $membership->role_name ?? 'user';
                                $badgeColor = match($roleName) {
                                    'user' => 'bg-blue-100 text-blue-800',
                                    'service_manager' => 'bg-green-100 text-green-800',
                                    'organization_admin' => 'bg-purple-100 text-purple-800',
                                    'organization_owner' => 'bg-red-100 text-red-800',
                                    'platform_admin' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $roleLabel = match($roleName) {
                                    'user' => '사용자',
                                    'service_manager' => '서비스 매니저',
                                    'organization_admin' => '조직 관리자',
                                    'organization_owner' => '조직 소유자',
                                    'platform_admin' => '플랫폼 관리자',
                                    default => $roleName
                                };
                            @endphp
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-600 min-w-0 truncate max-w-24" title="{{ $membership->organization->name }}">
                                    {{ $membership->organization->name }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                        @empty
                            <span class="text-xs text-gray-400">조직 소속 없음</span>
                        @endforelse

                        @if($user->organizationMemberships->count() > 2)
                            <div class="text-xs text-gray-400">
                                +{{ $user->organizationMemberships->count() - 2 }}개 더
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->updated_at->format('Y-m-d H:i') }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        활성
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex space-x-2 justify-end">
                        <button onclick="openRoleChangeModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 text-xs px-2 py-1 border border-indigo-300 rounded">
                            플랫폼 역할
                        </button>
                        <button onclick="openTenantPermissionModal({{ $user->id }})" class="text-green-600 hover:text-green-900 text-xs px-2 py-1 border border-green-300 rounded">
                            조직 권한
                        </button>
                        <button onclick="toggleUserStatus({{ $user->id }})" class="text-red-600 hover:text-red-900 text-xs px-2 py-1 border border-red-300 rounded">
                            상태 변경
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                    등록된 사용자가 없습니다.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
