{{-- 사용자 관리 메인 콘텐츠 --}}
<div class="users-content" style="padding: 24px;" x-data="usersManagement">

    {{-- 검색 및 필터 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- 검색 --}}
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                <input type="text" 
                       id="search"
                       x-model="searchTerm"
                       @input="filterUsers()"
                       placeholder="이름 또는 이메일로 검색..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            {{-- 상태 필터 --}}
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                <select x-model="statusFilter" 
                        @change="filterUsers()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <option value="active">활성</option>
                    <option value="suspended">일시중단</option>
                    <option value="inactive">비활성</option>
                </select>
            </div>
            
        </div>
        
        {{-- 필터 리셋 버튼 --}}
        <div class="mt-4 flex justify-between items-center">
            <button @click="resetFilters()" 
                    class="text-sm text-gray-600 hover:text-gray-800">
                필터 초기화
            </button>
            <button @click="refreshData()" 
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm">
                새로고침
            </button>
        </div>
    </div>

    {{-- 사용자 목록 테이블 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">전체 사용자 목록</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            사용자 정보
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            조직 소속
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            상태
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            최근 접속
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            액션
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700" x-text="user.name.charAt(0)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="user.name"></div>
                                        <div class="text-sm text-gray-500" x-text="user.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span x-text="user.organizations_count || '0'"></span>개 조직
                                </div>
                                <div class="text-sm text-gray-500" x-text="user.primary_organization || '소속없음'"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="{
                                          'bg-green-100 text-green-800': user.status === 'active',
                                          'bg-yellow-100 text-yellow-800': user.status === 'suspended',
                                          'bg-red-100 text-red-800': user.status === 'inactive'
                                      }"
                                      x-text="user.status === 'active' ? '활성' : user.status === 'suspended' ? '일시중단' : '비활성'">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div x-text="user.last_login || '접속 기록 없음'"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="viewUser(user.id)"
                                            class="text-blue-600 hover:text-blue-900">상세</button>
                                    <button @click="editUser(user.id)"
                                            class="text-gray-600 hover:text-gray-900">편집</button>
                                    <button @click="toggleUserStatus(user.id)"
                                            class="text-red-600 hover:text-red-900">
                                        <span x-text="user.status === 'active' ? '비활성화' : '활성화'"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- 페이지네이션 --}}
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    총 <span x-text="filteredUsers.length"></span>명의 사용자
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">이전</button>
                    <span class="px-3 py-1 text-sm bg-blue-500 text-white rounded">1</span>
                    <button class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">다음</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('usersManagement', () => ({
        searchTerm: '',
        statusFilter: '',
        users: @json($users->items() ?? []),
        filteredUsers: [],

        init() {
            this.filteredUsers = this.users;
            console.log('Users management initialized');
        },

        filterUsers() {
            this.filteredUsers = this.users.filter(user => {
                const matchesSearch = !this.searchTerm ||
                    user.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    user.email.toLowerCase().includes(this.searchTerm.toLowerCase());

                const matchesStatus = !this.statusFilter || user.status === this.statusFilter;

                return matchesSearch && matchesStatus;
            });
        },

        resetFilters() {
            this.searchTerm = '';
            this.statusFilter = '';
            this.filteredUsers = this.users;
        },

        refreshData() {
            console.log('Refreshing users data...');
            // 실제 구현시 API 호출로 페이지 새로고침 또는 AJAX 요청
            window.location.reload();
        },

        viewUser(userId) {
            console.log('Viewing user:', userId);
            // 상세 페이지로 이동 또는 모달 표시
        },

        editUser(userId) {
            console.log('Editing user:', userId);
            // 편집 모달 표시
        },

        toggleUserStatus(userId) {
            const user = this.users.find(u => u.id === userId);
            if (user) {
                user.status = user.status === 'active' ? 'inactive' : 'active';
                this.filterUsers();
            }
        }
    }));
});
</script>
