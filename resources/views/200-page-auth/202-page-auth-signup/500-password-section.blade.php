{{-- ========================================
     비밀번호 섹션 (비밀번호 + 확인)
     ======================================== --}}
<div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-600 border-b border-gray-200 pb-2">비밀번호 설정</h3>

    <!-- 비밀번호 -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            비밀번호
        </label>
        <input
            type="password"
            id="password"
            wire:model="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="비밀번호를 입력하세요 (영문+숫자 8자 이상)"
        />
        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- 비밀번호 확인 -->
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
            비밀번호 확인
        </label>
        <input
            type="password"
            id="password_confirmation"
            wire:model="password_confirmation"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="비밀번호를 다시 입력하세요"
        />
        @error('password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>