<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SmsVerificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Register extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $nickname = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $phone_number = '';
    public $country_code = '+82';
    public $verification_code = '';
    public $phone_verified = false;
    public $verification_sent = false;
    public $can_resend = true;
    public $resend_countdown = 0;
    public $is_sending = false;
    public $is_verifying = false;
    public $verification_attempts = 0;
    public $max_attempts = 3;
    
    // Email verification properties
    public $email_verification_code = '';
    public $email_verified = false;
    public $email_verification_sent = false;
    public $can_resend_email = true;
    public $email_resend_countdown = 0;
    public $is_sending_email = false;
    public $is_verifying_email = false;
    public $email_verification_attempts = 0;
    public $max_email_attempts = 5;
    
    // Agreement properties
    public $agree_all = false;
    public $agree_age = false;
    public $agree_terms = false;
    public $agree_privacy = false;
    public $agree_marketing = false;

    /**
     * 동적 검증 규칙 반환
     */
    protected function rules()
    {
        $rules = [
            'first_name' => 'required|min:1|max:50|regex:/^[\p{Hangul}a-zA-Z\s]+$/u',
            'last_name' => 'required|min:1|max:50|regex:/^[\p{Hangul}a-zA-Z\s]+$/u',
            'nickname' => 'nullable|min:2|max:20|unique:users|regex:/^[\p{Hangul}a-zA-Z0-9_]+$/u',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).*$/',
            'phone_number' => 'required|regex:/^01[0-9]-?[0-9]{4}-?[0-9]{4}$/|unique:users,phone_number',
        ];

        // 전화번호 인증이 아직 완료되지 않았고 인증번호가 발송된 경우에만 인증번호 필수
        if ($this->verification_sent && !$this->phone_verified) {
            $rules['verification_code'] = 'required|digits:6';
        }

        // 이메일 인증이 아직 완료되지 않았고 인증번호가 발송된 경우에만 인증번호 필수
        if ($this->email_verification_sent && !$this->email_verified) {
            $rules['email_verification_code'] = 'required|digits:6';
        }

        // 약관 동의 검증 (필수)
        $rules['agree_age'] = 'accepted';
        $rules['agree_terms'] = 'accepted';
        $rules['agree_privacy'] = 'accepted';

        return $rules;
    }

    protected $messages = [
        'first_name.required' => '이름을 입력해주세요.',
        'first_name.min' => '이름을 입력해주세요.',
        'first_name.max' => '이름은 최대 50자까지 입력 가능합니다.',
        'first_name.regex' => '이름은 한글, 영문, 공백만 입력 가능합니다.',
        'last_name.required' => '성을 입력해주세요.',
        'last_name.min' => '성을 입력해주세요.',
        'last_name.max' => '성은 최대 50자까지 입력 가능합니다.',
        'last_name.regex' => '성은 한글, 영문, 공백만 입력 가능합니다.',
        'nickname.min' => '닉네임은 최소 2자 이상이어야 합니다.',
        'nickname.max' => '닉네임은 최대 20자까지 입력 가능합니다.',
        'nickname.unique' => '이미 사용중인 닉네임입니다.',
        'nickname.regex' => '닉네임은 한글, 영문, 숫자, 언더스코어(_)만 입력 가능합니다.',
        'email.required' => '이메일을 입력해주세요.',
        'email.email' => '올바른 이메일 주소를 입력해주세요.',
        'email.unique' => '이미 사용중인 이메일입니다.',
        'email.max' => '이메일은 최대 255자까지 입력 가능합니다.',
        'password.required' => '비밀번호를 입력해주세요.',
        'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
        'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        'password.regex' => '비밀번호는 영문과 숫자를 포함해야 합니다.',
        'phone_number.required' => '휴대폰 번호를 입력해주세요.',
        'phone_number.regex' => '올바른 휴대폰 번호 형식을 입력해주세요. (예: 010-1234-5678)',
        'phone_number.unique' => '이미 등록된 휴대폰 번호입니다.',
        'verification_code.required_if' => '인증번호를 입력해주세요.',
        'verification_code.digits' => '인증번호는 6자리 숫자입니다.',
        'email_verification_code.required_if' => '이메일 인증번호를 입력해주세요.',
        'email_verification_code.digits' => '이메일 인증번호는 6자리 숫자입니다.',
        'agree_age.accepted' => '만 14세 이상임에 동의해주세요.',
        'agree_terms.accepted' => '이용약관에 동의해주세요.',
        'agree_privacy.accepted' => '개인정보처리방침에 동의해주세요.',
    ];


    /**
     * SMS 인증번호 전송
     */
    public function sendVerificationCode()
    {
        try {
            // 휴대폰 번호가 비어있는지 먼저 확인
            if (empty($this->phone_number)) {
                session()->flash('error', '휴대폰 번호를 입력해주세요.');
                return;
            }

            $this->validateOnly('phone_number');

            if (!$this->can_resend) {
                session()->flash('error', '잠시 후 다시 시도해주세요.');
                return;
            }

            if ($this->is_sending) {
                session()->flash('error', '인증번호 전송 중입니다. 잠시만 기다려주세요.');
                return;
            }

            $this->is_sending = true;
            $this->dispatch('sms-sending-started');

            $smsService = new SmsVerificationService();
            $result = $smsService->sendVerificationCode($this->phone_number, $this->country_code);

            if ($result['success']) {
                $this->verification_sent = true;
                $this->can_resend = false;
                $this->resend_countdown = 60;
                $this->verification_attempts = 0;
                $this->startCountdown();
                
                session()->flash('success', $result['message'] . ' (5분 내 입력해주세요)');
                $this->dispatch('sms-sent-successfully');
            } else {
                session()->flash('error', $result['message']);
                $this->dispatch('sms-send-failed');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'SMS 전송 중 오류가 발생했습니다. 다시 시도해주세요.');
            $this->dispatch('sms-send-failed');
        } finally {
            $this->is_sending = false;
        }
    }

    /**
     * SMS 인증번호 검증
     */
    public function verifyPhoneNumber()
    {
        try {
            $this->validateOnly('verification_code');

            if ($this->is_verifying) {
                session()->flash('error', '인증 진행 중입니다. 잠시만 기다려주세요.');
                return;
            }

            if ($this->verification_attempts >= $this->max_attempts) {
                session()->flash('error', '인증 시도 횟수를 초과했습니다. 새로운 인증번호를 요청해주세요.');
                $this->resetVerification();
                return;
            }

            $this->is_verifying = true;
            $this->verification_attempts++;
            $this->dispatch('verification-started');

            $smsService = new SmsVerificationService();
            $result = $smsService->verifyCode($this->phone_number, $this->verification_code, $this->country_code);

            if ($result['success']) {
                $this->phone_verified = true;
                $this->verification_code = '';
                session()->flash('success', $result['message']);
                $this->dispatch('verification-completed');
            } else {
                $remaining = $this->max_attempts - $this->verification_attempts;
                if ($remaining > 0) {
                    session()->flash('error', $result['message'] . " (남은 시도: {$remaining}회)");
                } else {
                    session()->flash('error', '인증 시도 횟수를 초과했습니다. 새로운 인증번호를 요청해주세요.');
                    $this->resetVerification();
                }
                $this->verification_code = '';
                $this->dispatch('verification-failed');
            }
        } catch (\Exception $e) {
            session()->flash('error', '인증 확인 중 오류가 발생했습니다. 다시 시도해주세요.');
            $this->verification_code = '';
            $this->dispatch('verification-failed');
        } finally {
            $this->is_verifying = false;
        }
    }

    /**
     * 재전송 카운트다운 시작
     */
    private function startCountdown()
    {
        $this->dispatch('startCountdown', seconds: $this->resend_countdown);
    }

    /**
     * 카운트다운 업데이트 (Livewire polling에서 호출)
     */
    public function updateCountdown()
    {
        // 이미 인증이 완료되면 카운트다운 중지
        if ($this->phone_verified) {
            return;
        }

        if ($this->resend_countdown > 0) {
            $this->resend_countdown--;
        }
        
        if ($this->resend_countdown <= 0) {
            $this->can_resend = true;
            $this->resend_countdown = 0;
        }
    }

    /**
     * 재전송 카운트다운 완료 처리
     */
    public function countdownFinished()
    {
        $this->can_resend = true;
        $this->resend_countdown = 0;
    }

    /**
     * 인증 상태 초기화
     */
    public function resetVerification()
    {
        $this->verification_sent = false;
        $this->phone_verified = false;
        $this->verification_code = '';
        $this->verification_attempts = 0;
        $this->can_resend = true;
        $this->resend_countdown = 0;
        $this->is_sending = false;
        $this->is_verifying = false;
    }

    /**
     * 전화번호 변경 시 인증 상태 초기화
     */
    public function updatedPhoneNumber()
    {
        $this->resetVerification();
    }

    /**
     * 국가코드 변경 시 인증 상태 초기화
     */
    public function updatedCountryCode()
    {
        $this->resetVerification();
    }

    /**
     * 이메일 인증번호 전송
     */
    public function sendEmailVerificationCode()
    {
        try {
            // 이메일이 비어있는지 먼저 확인
            if (empty($this->email)) {
                session()->flash('error', '이메일을 입력해주세요.');
                return;
            }

            $this->validateOnly('email');

            if (!$this->can_resend_email) {
                session()->flash('error', '잠시 후 다시 시도해주세요.');
                return;
            }

            if ($this->is_sending_email) {
                session()->flash('error', '이메일 인증번호 전송 중입니다. 잠시만 기다려주세요.');
                return;
            }

            $this->is_sending_email = true;
            $this->dispatch('email-sending-started');

            // 6자리 랜덤 인증번호 생성
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // 캐시에 인증번호 저장 (5분 유효)
            Cache::put("email_verification:{$this->email}", $verificationCode, 300);

            // 이메일 전송
            Mail::raw(
                "안녕하세요!\n\n이메일 인증을 위한 인증번호입니다.\n\n인증번호: {$verificationCode}\n\n인증번호는 5분간 유효합니다.\n\n감사합니다.",
                function ($message) {
                    $message->to($this->email)
                           ->subject('[Plobin] 이메일 인증번호');
                }
            );

            $this->email_verification_sent = true;
            $this->can_resend_email = false;
            $this->email_resend_countdown = 60;
            $this->email_verification_attempts = 0;
            $this->startEmailCountdown();
            
            session()->flash('success', '이메일 인증번호가 전송되었습니다. (5분 내 입력해주세요)');
            $this->dispatch('email-sent-successfully');

        } catch (\Exception $e) {
            session()->flash('error', '이메일 전송 중 오류가 발생했습니다. 다시 시도해주세요.');
            $this->dispatch('email-send-failed');
        } finally {
            $this->is_sending_email = false;
        }
    }

    /**
     * 이메일 인증번호 검증
     */
    public function verifyEmailCode()
    {
        try {
            $this->validateOnly('email_verification_code');

            if ($this->is_verifying_email) {
                session()->flash('error', '이메일 인증 진행 중입니다. 잠시만 기다려주세요.');
                return;
            }

            if ($this->email_verification_attempts >= $this->max_email_attempts) {
                session()->flash('error', '인증 시도 횟수를 초과했습니다. 새로운 인증번호를 요청해주세요.');
                $this->resetEmailVerification();
                return;
            }

            $this->is_verifying_email = true;
            $this->email_verification_attempts++;
            $this->dispatch('email-verification-started');

            // 캐시에서 인증번호 확인
            $cachedCode = Cache::get("email_verification:{$this->email}");
            
            if (!$cachedCode) {
                session()->flash('error', '인증번호가 만료되었습니다. 새로운 인증번호를 요청해주세요.');
                $this->resetEmailVerification();
                $this->dispatch('email-verification-failed');
                return;
            }

            if ($this->email_verification_code === $cachedCode) {
                $this->email_verified = true;
                $this->email_verification_code = '';
                
                // 인증 완료 후 캐시에서 제거
                Cache::forget("email_verification:{$this->email}");
                
                session()->flash('success', '이메일 인증이 완료되었습니다!');
                $this->dispatch('email-verification-completed');
            } else {
                $remaining = $this->max_email_attempts - $this->email_verification_attempts;
                if ($remaining > 0) {
                    session()->flash('error', "인증번호가 일치하지 않습니다. (남은 시도: {$remaining}회)");
                } else {
                    session()->flash('error', '인증 시도 횟수를 초과했습니다. 새로운 인증번호를 요청해주세요.');
                    $this->resetEmailVerification();
                }
                $this->email_verification_code = '';
                $this->dispatch('email-verification-failed');
            }
        } catch (\Exception $e) {
            session()->flash('error', '이메일 인증 확인 중 오류가 발생했습니다. 다시 시도해주세요.');
            $this->email_verification_code = '';
            $this->dispatch('email-verification-failed');
        } finally {
            $this->is_verifying_email = false;
        }
    }

    /**
     * 이메일 재전송 카운트다운 시작
     */
    private function startEmailCountdown()
    {
        $this->dispatch('startEmailCountdown', seconds: $this->email_resend_countdown);
    }

    /**
     * 이메일 카운트다운 업데이트
     */
    public function updateEmailCountdown()
    {
        // 이미 인증이 완료되면 카운트다운 중지
        if ($this->email_verified) {
            return;
        }

        if ($this->email_resend_countdown > 0) {
            $this->email_resend_countdown--;
        }
        
        if ($this->email_resend_countdown <= 0) {
            $this->can_resend_email = true;
            $this->email_resend_countdown = 0;
        }
    }

    /**
     * 이메일 카운트다운 완료 처리
     */
    public function emailCountdownFinished()
    {
        $this->can_resend_email = true;
        $this->email_resend_countdown = 0;
    }

    /**
     * 이메일 인증 상태 초기화
     */
    public function resetEmailVerification()
    {
        $this->email_verification_sent = false;
        $this->email_verified = false;
        $this->email_verification_code = '';
        $this->email_verification_attempts = 0;
        $this->can_resend_email = true;
        $this->email_resend_countdown = 0;
        $this->is_sending_email = false;
        $this->is_verifying_email = false;
        
        // 캐시에서도 제거
        if ($this->email) {
            Cache::forget("email_verification:{$this->email}");
        }
    }

    /**
     * 이메일 변경 시 인증 상태 초기화
     */
    public function updatedEmail()
    {
        $this->resetEmailVerification();
    }


    /**
     * 회원가입 처리
     */
    public function register()
    {
        try {
            // 개발 환경에서는 인증 체크 우회 (임시)
            if (config('app.debug') && request()->has('skip_verification')) {
                $this->phone_verified = true;
                $this->email_verified = true;
            }

            // 전화번호 인증이 완료되지 않은 경우
            if (!$this->phone_verified) {
                session()->flash('error', '휴대폰 번호 인증을 완료해주세요.');
                return;
            }

            // 이메일 인증이 완료되지 않은 경우
            if (!$this->email_verified) {
                session()->flash('error', '이메일 인증을 완료해주세요.');
                return;
            }

            // 진행 중인 작업이 있는지 확인
            if ($this->is_sending || $this->is_verifying || $this->is_sending_email || $this->is_verifying_email) {
                session()->flash('error', '인증 진행 중입니다. 잠시만 기다려주세요.');
                return;
            }

            $this->validate();

            // 이미 등록된 전화번호인지 확인
            $existingUser = User::where('phone_number', $this->phone_number)
                               ->where('country_code', $this->country_code)
                               ->first();
            
            if ($existingUser) {
                session()->flash('error', '이미 등록된 휴대폰 번호입니다.');
                return;
            }

            $user = User::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'nickname' => $this->nickname,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'phone_number' => $this->phone_number,
                'country_code' => $this->country_code,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'marketing_consent' => $this->agree_marketing,
            ]);

            Auth::login($user);

            session()->flash('success', '회원가입이 완료되었습니다!');
            return redirect('/dashboard');
            
        } catch (\Exception $e) {
            \Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_data' => [
                    'email' => $this->email,
                    'phone_number' => $this->phone_number,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name
                ]
            ]);
            session()->flash('error', '회원가입 중 오류가 발생했습니다. 다시 시도해주세요.');
        }
    }

    /**
     * 전체 동의 체크박스 업데이트 시
     */
    public function updatedAgreeAll()
    {
        $this->agree_age = $this->agree_all;
        $this->agree_terms = $this->agree_all;
        $this->agree_privacy = $this->agree_all;
    }

    /**
     * 개별 동의 체크박스 업데이트 시 전체 동의 상태 확인
     */
    public function updatedAgreeAge()
    {
        $this->checkAgreeAll();
    }

    public function updatedAgreeTerms()
    {
        $this->checkAgreeAll();
    }

    public function updatedAgreePrivacy()
    {
        $this->checkAgreeAll();
    }

    public function updatedAgreeMarketing()
    {
        // 마케팅 수신동의는 선택사항이므로 전체 동의에 영향을 주지 않음
    }

    /**
     * 모든 개별 동의가 체크되었는지 확인하고 전체 동의 업데이트 (필수 항목만)
     */
    private function checkAgreeAll()
    {
        $this->agree_all = $this->agree_age && $this->agree_terms && $this->agree_privacy;
    }

    public function render()
    {
        return view('200-page-auth.202-page-auth-signup.300-livewire-form');
    }
}
