<div>
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-900">표시명 설정</h3>
            <p class="mt-1 text-sm text-gray-600">다른 사용자들에게 어떤 이름으로 표시될지 선택하세요.</p>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="updateDisplayNamePreference">
            <div class="space-y-4">
                @foreach($availableOptions as $value => $option)
                    <div class="relative">
                        <label class="flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors
                            {{ $display_name_preference === $value ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : '' }}">
                            <input
                                type="radio"
                                wire:model="display_name_preference"
                                value="{{ $value }}"
                                class="mt-0.5 text-blue-600 focus:ring-blue-500 border-gray-300"
                            >
                            <div class="ml-3 flex-1">
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

            <div class="mt-6 pt-4 border-t border-gray-200">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                >
                    <span wire:loading.remove>설정 저장</span>
                    <span wire:loading>
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        저장 중...
                    </span>
                </button>
            </div>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded-md">
            <h4 class="text-sm font-medium text-gray-900 mb-2">현재 정보</h4>
            <div class="text-sm space-y-1">
                <div><span class="text-gray-600">실명:</span> <span class="font-medium">{{ $user->full_name ?: '설정되지 않음' }}</span></div>
                <div><span class="text-gray-600">닉네임:</span> <span class="font-medium">{{ $user->nickname ?: '설정되지 않음' }}</span></div>
                <div><span class="text-gray-600">이메일:</span> <span class="font-medium">{{ explode('@', $user->email)[0] }}</span></div>
                <div><span class="text-gray-600">현재 표시명:</span> <span class="font-semibold text-blue-600">{{ $user->display_name }}</span></div>
            </div>
        </div>
    </div>
</div>