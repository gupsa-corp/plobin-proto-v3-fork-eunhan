<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">프로필</h1>
        <p class="text-gray-600 mt-1">계정 정보를 확인할 수 있습니다.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 프로필 정보 -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">기본 정보</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">실명</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ $user->full_name ?: '이름 없음' }}</p>
                        </div>

                        @if($user->nickname)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">닉네임</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ $user->nickname }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">표시명</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ $user->display_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">이메일</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ $user->email }}</p>
                        </div>

                        @if($user->phone_number)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">연락처</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ $user->formatted_phone ?: $user->phone_number }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">소속</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                                @if($user->organizations && $user->organizations->isNotEmpty())
                                    {{ $user->organizations->first()->name }}
                                @else
                                    소속 없음
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">가입일</label>
                            <p class="text-base text-gray-900 bg-gray-50 px-3 py-2 rounded-md">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '정보 없음' }}</p>
                        </div>

                        @if($user->roles && $user->roles->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">권한</label>
                            <div class="bg-gray-50 px-3 py-2 rounded-md">
                                @foreach($user->roles as $role)
                                    <span class="inline-block bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded mr-2 mb-1">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    <button wire:click="$dispatch('showPasswordModal')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        수정
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>