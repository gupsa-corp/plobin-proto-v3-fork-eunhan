{{-- 조직 목록 Livewire 컴포넌트 --}}
<div class="organizations-list-content" style="padding: 24px;">
    {{-- 헤더 --}}
    <div class="flex justify-between items-center mb-8">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">조직 목록</h1>
            <p class="text-lg text-gray-500">소속된 조직을 관리하고 새로운 조직을 생성할 수 있습니다.</p>
        </div>
        <button wire:click="$dispatch('openCreateModal')" class="flex items-center justify-center gap-2 px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-bold text-sm rounded-lg">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M15.625 9.375H10.625V4.375C10.625 4.02982 10.3452 3.75 10 3.75C9.65482 3.75 9.375 4.02982 9.375 4.375V9.375H4.375C4.02982 9.375 3.75 9.65482 3.75 10C3.75 10.3452 4.02982 10.625 4.375 10.625H9.375V15.625C9.375 15.9702 9.65482 16.25 10 16.25C10.3452 16.25 10.625 15.9702 10.625 15.625V10.625H15.625C15.9702 10.625 16.25 10.3452 16.25 10C16.25 9.65482 15.9702 9.375 15.625 9.375Z" fill="white"/>
            </svg>
            새 조직 생성
        </button>
    </div>

    {{-- 조직 목록 --}}
    @if($isLoading)
        {{-- 로딩 상태 --}}
        <div class="flex justify-center py-12">
            <div class="inline-flex items-center gap-2 text-gray-500">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                조직 목록을 불러오는 중...
            </div>
        </div>
    @elseif(count($organizations) === 0)
        {{-- 빈 상태 --}}
        <div class="flex justify-center py-12">
            <div class="flex flex-col items-center justify-center">
                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">아직 조직이 없습니다</h3>
                <p class="text-gray-500 mb-4">새로운 조직을 생성해서 시작해보세요.</p>
                <button wire:click="$dispatch('openCreateModal')" class="px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white font-medium rounded-lg">
                    첫 번째 조직 생성하기
                </button>
            </div>
        </div>
    @else
        {{-- 조직 카드 그리드 --}}
        <div class="grid grid-cols-4 gap-6">
            @foreach($organizations as $org)
                <a href="/organizations/{{ $org->id }}/dashboard" class="block bg-white border border-gray-200 rounded-lg p-5 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                    {{-- 카드 헤더 --}}
                    <div class="flex justify-between items-start mb-4">
                        {{-- 조직 아바타 --}}
                        <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-xl">
                                {{ $org->name ? mb_substr($org->name, 0, 1) : 'G' }}
                            </span>
                        </div>

                        {{-- 옵션 버튼 --}}
                        <button onclick="event.preventDefault(); event.stopPropagation();" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 rounded-lg">
                            <div class="flex flex-col gap-1">
                                <div class="w-1 h-1 bg-gray-900 rounded-full"></div>
                                <div class="w-1 h-1 bg-gray-900 rounded-full"></div>
                                <div class="w-1 h-1 bg-gray-900 rounded-full"></div>
                            </div>
                        </button>
                    </div>

                    {{-- 조직 정보 --}}
                    <div class="mb-4">
                        <div class="mb-2">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-lg text-gray-900 leading-tight capitalize">
                                    {{ $org->name ?? '이름 없음' }}
                                </h3>
                                {{-- 사용자 역할 배지 --}}
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($org->user_role === '소유자') bg-purple-100 text-purple-800
                                    @elseif($org->user_role === '관리자') bg-blue-100 text-blue-800
                                    @elseif($org->user_role === 'PM') bg-green-100 text-green-800
                                    @elseif($org->user_role === '사용자') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $org->user_role }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center text-gray-600">
                            {{-- 복사 아이콘 --}}
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2" stroke-width="1.5"></rect>
                                <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" stroke-width="1.5"></path>
                            </svg>
                            <span class="text-base">{{ $org->description ?? '설명 없음' }}</span>
                        </div>

                        {{-- 멤버 수 표시 --}}
                        <div class="flex items-center text-gray-500 mt-2">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197V9a3 3 0 00-6 0v2m6 0V9a3 3 0 00-6 0v2"></path>
                            </svg>
                            <span class="text-sm">멤버 {{ $org->members_count ?? 0 }}명</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
