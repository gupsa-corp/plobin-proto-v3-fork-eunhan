<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">권한</h1>
        <p class="text-gray-600 mt-1">내 계정의 권한 정보를 확인할 수 있습니다.</p>
        <div class="mt-2">
            <a href="/mypage" class="text-blue-600 hover:text-blue-700 text-sm">← 프로필로 돌아가기</a>
        </div>
    </div>

    <!-- 표시명 설정 섹션 -->
    <div class="mb-6">
        @livewire('profile.display-name-settings')
    </div>

    @if(empty($organizationsPermissions))
    <!-- 조직이 없는 경우 -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">소속된 조직이 없습니다</h3>
        <p class="text-gray-600 mb-4">조직에 가입하거나 새로운 조직을 생성하여 협업을 시작하세요.</p>
        <a href="/organizations" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            조직 둘러보기
        </a>
    </div>
    @else
    <!-- 조직별 권한 정보 -->
    <div class="space-y-6">
        @foreach($organizationsPermissions as $orgPermission)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $orgPermission['organization']['name'] }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            역할:
                            <span class="font-medium
                                @if($orgPermission['role'] === 'organization_owner') text-red-600
                                @elseif($orgPermission['role'] === 'admin') text-orange-600
                                @else text-blue-600
                                @endif
                            ">
                                @if($orgPermission['role'] === 'organization_owner') 소유자
                                @elseif($orgPermission['role'] === 'admin') 관리자
                                @else 멤버
                                @endif
                            </span>
                            @if($orgPermission['joined_at'])
                            | 가입일: {{ \Carbon\Carbon::parse($orgPermission['joined_at'])->format('Y-m-d') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        @if($orgPermission['role'] === 'organization_owner')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-4 4-4-4 4-4 .257-.257A6 6 0 1118 8zm-6-2a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
                            </svg>
                            소유자
                        </span>
                        @elseif($orgPermission['role'] === 'admin')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4z" clip-rule="evenodd"/>
                            </svg>
                            관리자
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            멤버
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($orgPermission['permissions'] as $category => $permissions)
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                            @if($category === '조직 관리')
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a2 2 0 104 0 2 2 0 00-4 0zm8-2a2 2 0 11-4 0 2 2 0 014 0z" clip-rule="evenodd"/>
                            </svg>
                            @elseif($category === '프로젝트')
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @elseif($category === '데이터')
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            @endif
                            {{ $category }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($permissions as $permission)
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mt-0.5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $permission }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
