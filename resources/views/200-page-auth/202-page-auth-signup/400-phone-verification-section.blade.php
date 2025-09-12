{{-- ========================================
     휴대폰 인증 섹션
     ======================================== --}}
<div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-600 border-b border-gray-200 pb-2">휴대폰 인증</h3>

    <!-- 휴대폰 번호 입력 -->
    <div>
        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
            휴대폰 번호
        </label>
        <div class="flex gap-2">
            <select
                wire:model="country_code"
                class="w-24 px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
                <option value="+82">+82</option>
            </select>
            <input
                type="tel"
                id="phone_number"
                wire:model.blur="phone_number"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="010-1234-5678"
            />
        </div>
        @error('phone_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>


    <!-- SMS 인증번호 전송 -->
    <div>
        <button
            type="button"
            wire:click="sendVerificationCode"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-50"
            @if(!$can_resend || $is_sending || !$phone_number) disabled @endif
            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="sendVerificationCode">
                @if($verification_sent && !$can_resend && !$phone_verified)
                    <span>재전송 ({{ $resend_countdown }})</span>
                @elseif($is_sending)
                    전송 중...
                @else
                    인증번호 전송
                @endif
            </span>
            <span wire:loading wire:target="sendVerificationCode">전송 중...</span>
        </button>
        @if($verification_sent && !$can_resend && !$phone_verified)
            <div class="text-xs text-red-500 mt-1" wire:poll.1s="updateCountdown">
                제한시간: {{ $resend_countdown }}초 남음
            </div>
        @endif
    </div>

    <!-- 인증번호 입력 (인증번호 전송 후에만 표시) -->
    @if($verification_sent)
    <div>
        <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">
            휴대폰 인증번호
            @if($phone_verified)
                <span class="text-green-600 text-sm ml-2">✓ 인증 완료</span>
            @elseif($verification_attempts > 0)
                <span class="text-orange-600 text-sm ml-2">시도 {{ $verification_attempts }}/{{ $max_attempts }}</span>
            @endif
        </label>
        <div class="flex gap-2">
            <input
                type="text"
                id="verification_code"
                wire:model="verification_code"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="6자리 인증번호 입력"
                maxlength="6"
                @if($phone_verified) disabled @endif
            />
            @if(!$phone_verified)
            <button
                type="button"
                wire:click="verifyPhoneNumber"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                @if($is_verifying || $verification_attempts >= $max_attempts) disabled @endif
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap"
            >
                <span wire:loading.remove wire:target="verifyPhoneNumber">
                    @if($is_verifying)
                        확인 중...
                    @elseif($verification_attempts >= $max_attempts)
                        시도 초과
                    @else
                        인증 확인
                    @endif
                </span>
                <span wire:loading wire:target="verifyPhoneNumber">확인 중...</span>
            </button>
            @endif
        </div>
        @error('verification_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    @endif
</div>