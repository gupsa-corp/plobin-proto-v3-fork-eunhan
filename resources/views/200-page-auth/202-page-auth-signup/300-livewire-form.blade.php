<div>
<form wire:submit.prevent="register" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                이름
            </label>
            <input 
                type="text" 
                id="first_name"
                wire:model="first_name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="이름을 입력하세요"
            />
            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                성
            </label>
            <input 
                type="text" 
                id="last_name"
                wire:model="last_name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="성을 입력하세요"
            />
            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            이메일
        </label>
        <input 
            type="email" 
            id="email"
            wire:model="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="이메일을 입력하세요"
        />
        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- 휴대폰 번호 입력 -->
    <div>
        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
            휴대폰 번호
        </label>
        <div class="flex gap-2">
            <select 
                wire:model="country_code"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="+82">+82 (한국)</option>
            </select>
            <input 
                type="tel" 
                id="phone_number"
                wire:model="phone_number"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="010-1234-5678"
            />
            <button 
                type="button"
                wire:click="sendVerificationCode"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                @if(!$can_resend || $is_sending) disabled @endif
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap"
            >
                <span wire:loading.remove wire:target="sendVerificationCode">
                    @if($verification_sent && !$can_resend)
                        재전송 (<span wire:poll.1s="updateCountdown" id="countdown">{{ $resend_countdown }}</span>)
                    @elseif($is_sending)
                        전송 중...
                    @else
                        인증번호 전송
                    @endif
                </span>
                <span wire:loading wire:target="sendVerificationCode">전송 중...</span>
            </button>
        </div>
        @error('phone_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <!-- SMS 인증번호 입력 (인증번호가 전송된 경우에만 표시) -->
    @if($verification_sent)
    <div>
        <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">
            인증번호
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
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap"
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

    <button 
        type="submit" 
        @if(!$phone_verified || $is_sending || $is_verifying) disabled @endif
        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
        wire:loading.attr="disabled"
        wire:loading.class="opacity-50 cursor-not-allowed"
    >
        <span wire:loading.remove>
            @if(!$phone_verified)
                휴대폰 인증을 완료해주세요
            @elseif($is_sending || $is_verifying)
                인증 진행 중...
            @else
                회원가입
            @endif
        </span>
        <span wire:loading>가입 중...</span>
    </button>
</form>

<!-- 개발 환경 알림 -->
@if(app()->environment(['local', 'testing']) || !config('solapi.api_key') || config('solapi.api_key') === 'your_solapi_api_key_here')
    <div class="mt-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">⚠️ 개발 모드: SMS는 실제로 전송되지 않으며 로그에서 인증번호를 확인할 수 있습니다.</span>
    </div>
@endif

<!-- 메시지 표시 -->
@if (session()->has('success'))
    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">성공!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if (session()->has('error'))
    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">오류!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    let countdownInterval;
    
    // Livewire 이벤트 리스너 등록 - Livewire 3 방식
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('startCountdown', function(data) {
            let seconds = data.seconds || data[0]?.seconds || 60;
            const countdownElement = document.getElementById('countdown');
            
            console.log('Starting countdown with', seconds, 'seconds');
            
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }
            
            countdownInterval = setInterval(function() {
                if (countdownElement) {
                    countdownElement.textContent = seconds;
                }
                
                seconds--;
                
                if (seconds < 0) {
                    clearInterval(countdownInterval);
                    // Livewire 컴포넌트에 카운트다운 완료 알림
                    @this.call('countdownFinished');
                }
            }, 1000);
        });

        // SMS 전송 관련 이벤트 리스너
        Livewire.on('sms-sending-started', function() {
            console.log('SMS 전송 시작');
        });

        Livewire.on('sms-sent-successfully', function() {
            console.log('SMS 전송 성공');
            // 인증번호 입력 필드에 포커스
            setTimeout(() => {
                const verificationInput = document.getElementById('verification_code');
                if (verificationInput) {
                    verificationInput.focus();
                }
            }, 100);
        });

        Livewire.on('sms-send-failed', function() {
            console.log('SMS 전송 실패');
        });

        Livewire.on('verification-started', function() {
            console.log('인증 시작');
        });

        Livewire.on('verification-completed', function() {
            console.log('인증 완료');
            // 회원가입 버튼에 포커스
            setTimeout(() => {
                const submitButton = document.querySelector('button[type="submit"]');
                if (submitButton && !submitButton.disabled) {
                    submitButton.focus();
                }
            }, 100);
        });

        Livewire.on('verification-failed', function() {
            console.log('인증 실패');
            // 인증번호 입력 필드 다시 포커스
            setTimeout(() => {
                const verificationInput = document.getElementById('verification_code');
                if (verificationInput) {
                    verificationInput.focus();
                    verificationInput.select();
                }
            }, 100);
        });
    });

    // 인증번호 입력 필드 자동 포맷팅 (숫자만 입력)
    setTimeout(() => {
        const verificationInput = document.getElementById('verification_code');
        if (verificationInput) {
            verificationInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }
    }, 100);
});
</script>
</div>
