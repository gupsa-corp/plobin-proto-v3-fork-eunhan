<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\SmsVerificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public $first_name = '';
    public $last_name = '';
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

    protected $rules = [
        'first_name' => 'required|min:2|max:50|regex:/^[가-힣a-zA-Z\s]+$/',
        'last_name' => 'required|min:2|max:50|regex:/^[가-힣a-zA-Z\s]+$/',
        'email' => 'required|email|unique:users|max:255',
        'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).*$/',
        'phone_number' => 'required|regex:/^01[0-9]-?[0-9]{4}-?[0-9]{4}$/|unique:users,phone_number',
        'verification_code' => 'required_if:verification_sent,true|digits:6',
    ];

    protected $messages = [
        'first_name.required' => '이름을 입력해주세요.',
        'first_name.min' => '이름은 최소 2자 이상이어야 합니다.',
        'first_name.max' => '이름은 최대 50자까지 입력 가능합니다.',
        'first_name.regex' => '이름은 한글, 영문, 공백만 입력 가능합니다.',
        'last_name.required' => '성을 입력해주세요.',
        'last_name.min' => '성은 최소 2자 이상이어야 합니다.',
        'last_name.max' => '성은 최대 50자까지 입력 가능합니다.',
        'last_name.regex' => '성은 한글, 영문, 공백만 입력 가능합니다.',
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
    ];

    /**
     * SMS 인증번호 전송
     */
    public function sendVerificationCode()
    {
        try {
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
     * 회원가입 처리
     */
    public function register()
    {
        try {
            // 전화번호 인증이 완료되지 않은 경우
            if (!$this->phone_verified) {
                session()->flash('error', '휴대폰 번호 인증을 완료해주세요.');
                return;
            }

            // 진행 중인 작업이 있는지 확인
            if ($this->is_sending || $this->is_verifying) {
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
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'phone_number' => $this->phone_number,
                'country_code' => $this->country_code,
                'phone_verified_at' => now(),
            ]);

            Auth::login($user);

            session()->flash('success', '회원가입이 완료되었습니다!');
            return redirect('/dashboard');
            
        } catch (\Exception $e) {
            session()->flash('error', '회원가입 중 오류가 발생했습니다. 다시 시도해주세요.');
        }
    }

    public function render()
    {
        return view('200-page-auth.202-page-auth-signup.300-livewire-form');
    }
}
