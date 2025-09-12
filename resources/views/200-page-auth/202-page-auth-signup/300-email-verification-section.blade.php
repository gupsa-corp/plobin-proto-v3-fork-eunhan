{{-- ========================================
     이메일 인증 섹션
     ======================================== --}}
<div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-600 border-b border-gray-200 pb-2">이메일 인증</h3>
    

    <!-- 이메일 인증번호 전송 -->
    <div>
        <button
            type="button"
            wire:click="sendEmailVerificationCode"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-50"
            @if(!$can_resend_email || $is_sending_email || !$email) disabled @endif
            class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="sendEmailVerificationCode">
                @if($email_verification_sent && !$can_resend_email && !$email_verified)
                    <span>재전송 ({{ $email_resend_countdown }})</span>
                @elseif($is_sending_email)
                    전송 중...
                @else
                    이메일 인증번호 전송
                @endif
            </span>
            <span wire:loading wire:target="sendEmailVerificationCode">전송 중...</span>
        </button>
        @if($email_verification_sent && !$can_resend_email && !$email_verified)
            <div class="text-xs text-red-500 mt-1" wire:poll.1s="updateEmailCountdown">
                제한시간: {{ $email_resend_countdown }}초 남음
            </div>
        @endif
    </div>

    <!-- 인증번호 입력 (인증번호 전송 후에만 표시) -->
    @if($email_verification_sent)
    <div>
        <label for="email_verification_code" class="block text-sm font-medium text-gray-700 mb-1">
            이메일 인증번호
            @if($email_verified)
                <span class="text-green-600 text-sm ml-2">✓ 인증 완료</span>
            @elseif($email_verification_attempts > 0)
                <span class="text-orange-600 text-sm ml-2">시도 {{ $email_verification_attempts }}/{{ $max_email_attempts }}</span>
            @endif
        </label>
        <div class="flex gap-2">
            <input
                type="text"
                id="email_verification_code"
                wire:model="email_verification_code"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                placeholder="6자리 인증번호 입력"
                maxlength="6"
                @if($email_verified) disabled @endif
            />
            @if(!$email_verified)
            <button
                type="button"
                wire:click="verifyEmailCode"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                @if($is_verifying_email || $email_verification_attempts >= $max_email_attempts) disabled @endif
                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap"
            >
                <span wire:loading.remove wire:target="verifyEmailCode">
                    @if($is_verifying_email)
                        확인 중...
                    @elseif($email_verification_attempts >= $max_email_attempts)
                        시도 초과
                    @else
                        인증 확인
                    @endif
                </span>
                <span wire:loading wire:target="verifyEmailCode">확인 중...</span>
            </button>
            @endif
        </div>
        @error('email_verification_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
    @endif
</div>