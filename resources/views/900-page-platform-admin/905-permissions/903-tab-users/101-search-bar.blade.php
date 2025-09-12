{{-- 사용자 검색 바 --}}
<div class="flex items-center space-x-3">
    <div class="relative">
        <input type="text" id="userSearchInput" placeholder="사용자 검색..."
               onkeyup="searchUsers(this.value)"
               class="block w-64 pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
            </svg>
        </div>
    </div>
    <div class="relative">
        <select id="roleFilter" onchange="filterByRole(this.value)" class="block w-48 px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">모든 플랫폼 역할</option>
            <option value="platform_admin">플랫폼 관리자</option>
            <option value="organization_admin">조직 관리자</option>
            <option value="organization_member">조직 멤버</option>
            <option value="no_role">플랫폼 역할 없음</option>
        </select>
    </div>
    <div class="relative">
        <select id="organizationFilter" onchange="filterByOrganization(this.value)" class="block w-48 px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">모든 조직</option>
            @foreach($organizations as $organization)
                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
            @endforeach
            <option value="no_org">조직 소속 없음</option>
        </select>
    </div>
    <div class="relative">
        <select id="permissionFilter" onchange="filterByPermission(this.value)" class="block w-48 px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">모든 조직 권한</option>
            <option value="0">초대됨</option>
            <option value="100">사용자</option>
            <option value="150">고급 사용자</option>
            <option value="200">서비스 매니저</option>
            <option value="250">선임 서비스 매니저</option>
            <option value="300">조직 관리자</option>
            <option value="350">선임 조직 관리자</option>
            <option value="400">조직 소유자</option>
            <option value="450">조직 창립자</option>
            <option value="500">플랫폼 관리자</option>
            <option value="550">최고 관리자</option>
        </select>
    </div>
    <button onclick="clearFilters()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        필터 초기화
    </button>
</div>
