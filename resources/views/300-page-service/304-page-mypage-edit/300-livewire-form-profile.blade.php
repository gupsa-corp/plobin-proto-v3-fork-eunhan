<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">기본 정보 수정</h3>
    </div>
    <div class="p-6">
        @if (session('message'))
            <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="updateProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">성 <span class="text-red-500">*</span></label>
                    <input type="text"
                           wire:model="first_name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           id="first_name"
                           required>
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">이름 <span class="text-red-500">*</span></label>
                    <input type="text"
                           wire:model="last_name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           id="last_name"
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="nickname" class="block text-sm font-medium text-gray-700 mb-2">닉네임 <span class="text-gray-500 text-sm">(선택)</span></label>
                <input type="text"
                       wire:model="nickname"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       id="nickname"
                       placeholder="닉네임을 입력하세요 (2-20자, 한글/영문/숫자/언더스코어)">
                <div class="text-xs text-gray-500 mt-1">닉네임을 입력하지 않으면 실명으로 표시됩니다.</div>
                @error('nickname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">이메일</label>
                <input type="email"
                       wire:model="email"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       id="email"
                       readonly>
                <p class="text-xs text-gray-500 mt-1">이메일은 변경할 수 없습니다.</p>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">연락처</label>
                <input type="tel"
                       wire:model="phone"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       id="phone"
                       placeholder="010-0000-0000">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">소속</label>
                <input type="text"
                       wire:model="organization"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       id="organization"
                       readonly>
                <p class="text-xs text-gray-500 mt-1">소속은 조직 목록에서 변경할 수 있습니다.</p>
            </div>

            <!-- 마케팅 수신동의 -->
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-md font-medium text-gray-900 mb-3">마케팅 정보 수신</h4>
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="marketing_consent" 
                               type="checkbox" 
                               wire:model="marketing_consent"
                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="marketing_consent" class="font-medium text-gray-700 cursor-pointer">
                            마케팅 정보 수신에 동의합니다
                        </label>
                        <p class="text-gray-500 mt-1">이벤트, 프로모션, 새로운 기능 소식을 이메일과 SMS로 받으실 수 있습니다.</p>
                    </div>
                </div>
            </div>

            <!-- 표시명 설정 -->
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-md font-medium text-gray-900 mb-3">표시명 설정</h4>
                <p class="text-sm text-gray-600 mb-4">다른 사용자들에게 어떤 이름으로 표시될지 선택하세요.</p>
                
                <div class="space-y-3">
                    @foreach($displayNameOptions as $value => $option)
                        <div class="relative">
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors
                                {{ $display_name_preference === $value ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : '' }}">
                                <input
                                    type="radio"
                                    wire:model="display_name_preference"
                                    value="{{ $value }}"
                                    class="mt-0.5 text-blue-600 focus:ring-blue-500 border-gray-300"
                                >
                                <div class="ml-3 flex-1 min-w-0">
                                    <div class="font-medium text-gray-900">{{ $option['label'] }}</div>
                                    <div class="text-sm text-gray-600 mt-1">{{ $option['description'] }}</div>
                                    <div class="mt-2 text-sm">
                                        <span class="text-gray-500">미리보기:</span>
                                        <span class="font-semibold text-gray-900 ml-1">{{ $option['preview'] }}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('display_name_preference')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 flex space-x-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">저장</button>
                <a href="/mypage" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">취소</a>
            </div>
        </form>
    </div>
</div>
