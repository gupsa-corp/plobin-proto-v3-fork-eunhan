{{-- ========================================
     기본 정보 섹션 (이름, 성, 이메일)
     ======================================== --}}
<div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-600 border-b border-gray-200 pb-2">기본 정보</h3>

    <!-- 이름 + 성 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                성
            </label>
            <input
                type="text"
                id="first_name"
                wire:model="first_name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="성을 입력하세요"
            />
            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                이름
            </label>
            <input
                type="text"
                id="last_name"
                wire:model="last_name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="이름을 입력하세요"
            />
            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <!-- 이메일 -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            이메일
            @if($email_verified)
                <span class="text-green-600 text-sm ml-2">✓ 인증 완료</span>
            @endif
        </label>
        <input
            type="email"
            id="email"
            wire:model.live="email"
            @if($email_verified) disabled @endif
            @class([
                'w-full px-3 py-2 border rounded-md focus:outline-none transition duration-200',
                'border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500' => !$email_verified,
                'border-green-300 bg-green-50 text-green-800 cursor-not-allowed' => $email_verified
            ])
            placeholder="이메일을 입력하세요"
        />
        @if($email_verified)
            <div class="text-xs text-green-600 mt-1">인증이 완료된 이메일입니다. 변경할 수 없습니다.</div>
        @endif
        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>