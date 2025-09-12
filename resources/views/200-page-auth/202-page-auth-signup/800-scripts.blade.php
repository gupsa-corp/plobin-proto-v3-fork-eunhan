{{-- ========================================
     JavaScript 섹션 (Livewire 이벤트 & 인터랙션)
     ======================================== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    let countdownInterval;
    let emailCountdownInterval;

    // ========================================
    // Livewire 이벤트 리스너 등록 (Livewire 3)
    // ========================================
    document.addEventListener('livewire:initialized', function() {

        // SMS 재전송 카운트다운
        Livewire.on('startCountdown', function(data) {
            let seconds = data.seconds || data[0]?.seconds || 60;
            const countdownElement = document.getElementById('countdown');
            const countdownTextElement = document.getElementById('countdown-text');

            console.log('Starting countdown with', seconds, 'seconds');

            if (countdownInterval) {
                clearInterval(countdownInterval);
            }

            // 초기 값 설정
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }
            if (countdownTextElement) {
                countdownTextElement.textContent = seconds;
            }

            countdownInterval = setInterval(function() {
                seconds--;
                
                // 버튼 텍스트 업데이트
                if (countdownElement) {
                    countdownElement.textContent = seconds;
                }
                // 제한시간 텍스트 업데이트
                if (countdownTextElement) {
                    countdownTextElement.textContent = seconds;
                }

                console.log('Countdown:', seconds);

                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                    console.log('Countdown finished');
                    
                    // Livewire 컴포넌트에 카운트다운 완료 알림
                    @this.call('countdownFinished');
                }
            }, 1000);
        });

        // 이메일 재전송 카운트다운
        Livewire.on('startEmailCountdown', function(data) {
            let seconds = data.seconds || data[0]?.seconds || 60;
            const emailCountdownElement = document.getElementById('email-countdown');
            const emailCountdownTextElement = document.getElementById('email-countdown-text');

            console.log('Starting email countdown with', seconds, 'seconds');

            if (emailCountdownInterval) {
                clearInterval(emailCountdownInterval);
            }

            // 초기 값 설정
            if (emailCountdownElement) {
                emailCountdownElement.textContent = seconds;
            }
            if (emailCountdownTextElement) {
                emailCountdownTextElement.textContent = seconds;
            }

            emailCountdownInterval = setInterval(function() {
                seconds--;
                
                // 버튼 텍스트 업데이트
                if (emailCountdownElement) {
                    emailCountdownElement.textContent = seconds;
                }
                // 제한시간 텍스트 업데이트
                if (emailCountdownTextElement) {
                    emailCountdownTextElement.textContent = seconds;
                }

                console.log('Email countdown:', seconds);

                if (seconds <= 0) {
                    clearInterval(emailCountdownInterval);
                    emailCountdownInterval = null;
                    console.log('Email countdown finished');
                    
                    // Livewire 컴포넌트에 카운트다운 완료 알림
                    @this.call('emailCountdownFinished');
                }
            }, 1000);
        });

        // ========================================
        // SMS 전송 관련 이벤트 리스너
        // ========================================
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

        // ========================================
        // SMS 인증 관련 이벤트 리스너
        // ========================================
        Livewire.on('verification-started', function() {
            console.log('인증 시작');
        });

        Livewire.on('verification-completed', function() {
            console.log('인증 완료');
            // SMS 카운트다운 중지
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
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

        // ========================================
        // 이메일 전송 관련 이벤트 리스너
        // ========================================
        Livewire.on('email-sending-started', function() {
            console.log('이메일 전송 시작');
        });

        Livewire.on('email-sent-successfully', function() {
            console.log('이메일 전송 성공');
            // 인증번호 입력 필드에 포커스
            setTimeout(() => {
                const emailVerificationInput = document.getElementById('email_verification_code');
                if (emailVerificationInput) {
                    emailVerificationInput.focus();
                }
            }, 100);
        });

        Livewire.on('email-send-failed', function() {
            console.log('이메일 전송 실패');
        });

        // ========================================
        // 이메일 인증 관련 이벤트 리스너
        // ========================================
        Livewire.on('email-verification-started', function() {
            console.log('이메일 인증 시작');
        });

        Livewire.on('email-verification-completed', function() {
            console.log('이메일 인증 완료');
            // 이메일 카운트다운 중지
            if (emailCountdownInterval) {
                clearInterval(emailCountdownInterval);
                emailCountdownInterval = null;
            }
        });

        Livewire.on('email-verification-failed', function() {
            console.log('이메일 인증 실패');
            // 인증번호 입력 필드 다시 포커스
            setTimeout(() => {
                const emailVerificationInput = document.getElementById('email_verification_code');
                if (emailVerificationInput) {
                    emailVerificationInput.focus();
                    emailVerificationInput.select();
                }
            }, 100);
        });
    });

    // ========================================
    // 인증번호 입력 필드 자동 포맷팅 (숫자만)
    // ========================================
    setTimeout(() => {
        const verificationInput = document.getElementById('verification_code');
        if (verificationInput) {
            verificationInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }

        const emailVerificationInput = document.getElementById('email_verification_code');
        if (emailVerificationInput) {
            emailVerificationInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }
    }, 100);
});
</script>