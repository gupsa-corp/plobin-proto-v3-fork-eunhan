<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditProfile extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $nickname = '';
    public $email = '';
    public $phone = '';
    public $organization = '';
    public $marketing_consent = false;
    public $display_name_preference = 'auto';
    
    public $displayNameOptions = [];

    protected $rules = [
        'first_name' => 'required|min:1|max:50|regex:/^[\p{Hangul}a-zA-Z\s]+$/u',
        'last_name' => 'required|min:1|max:50|regex:/^[\p{Hangul}a-zA-Z\s]+$/u',
        'nickname' => 'nullable|min:2|max:20|regex:/^[\p{Hangul}a-zA-Z0-9_]+$/u',
        'phone' => 'nullable|string',
        'marketing_consent' => 'boolean',
        'display_name_preference' => 'required|in:auto,real_name,nickname,email',
    ];

    protected $messages = [
        'first_name.required' => '성을 입력해주세요.',
        'first_name.min' => '성을 입력해주세요.',
        'first_name.max' => '성은 최대 50자까지 입력 가능합니다.',
        'first_name.regex' => '성은 한글, 영문, 공백만 입력 가능합니다.',
        'last_name.required' => '이름을 입력해주세요.',
        'last_name.min' => '이름을 입력해주세요.',
        'last_name.max' => '이름은 최대 50자까지 입력 가능합니다.',
        'last_name.regex' => '이름은 한글, 영문, 공백만 입력 가능합니다.',
        'nickname.min' => '닉네임은 최소 2자 이상이어야 합니다.',
        'nickname.max' => '닉네임은 최대 20자까지 입력 가능합니다.',
        'nickname.regex' => '닉네임은 한글, 영문, 숫자, 언더스코어(_)만 입력 가능합니다.',
        'nickname.unique' => '이미 사용중인 닉네임입니다.',
    ];

    public function mount()
    {
        $user = Auth::user();
        
        // 인증되지 않은 사용자인 경우 리다이렉트
        if (!$user) {
            return redirect()->route('login');
        }

        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->nickname = $user->nickname ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone_number ?? '';
        $this->organization = $user->organizations()->first()->name ?? '소속 없음';
        $this->marketing_consent = $user->marketing_consent ?? false;
        $this->display_name_preference = $user->display_name_preference ?? 'auto';
        
        $this->loadDisplayNameOptions();
    }

    public function updateProfile()
    {
        // 닉네임 unique 검사 (현재 사용자 제외)
        $user = Auth::user();
        $rules = $this->rules;
        if ($this->nickname) {
            $rules['nickname'] = 'nullable|min:2|max:20|regex:/^[\p{Hangul}a-zA-Z0-9_]+$/u|unique:users,nickname,' . $user->id;
        }
        
        $this->validate($rules);

        $user = Auth::user();
        
        // 인증되지 않은 사용자인 경우 처리
        if (!$user) {
            session()->flash('error', '인증이 필요합니다.');
            return redirect()->route('login');
        }

        $user->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nickname' => $this->nickname ?: null,
            'phone_number' => $this->phone ?: null,
            'marketing_consent' => $this->marketing_consent,
            'display_name_preference' => $this->display_name_preference,
        ]);

        session()->flash('message', '개인정보가 성공적으로 수정되었습니다.');
        
        // 옵션 미리보기 다시 로드
        $this->loadDisplayNameOptions();
    }
    
    public function loadDisplayNameOptions()
    {
        $user = Auth::user();
        
        $this->displayNameOptions = [
            'auto' => [
                'label' => '자동 설정',
                'description' => '닉네임 → 실명 → 이메일 순서로 자동 선택',
                'preview' => $this->getPreviewForOption('auto', $user)
            ],
            'real_name' => [
                'label' => '실명 우선',
                'description' => '실명을 표시명으로 사용 (없으면 이메일)',
                'preview' => $this->getPreviewForOption('real_name', $user)
            ],
            'nickname' => [
                'label' => '닉네임 우선',
                'description' => '닉네임을 표시명으로 사용 (없으면 실명 → 이메일)',
                'preview' => $this->getPreviewForOption('nickname', $user)
            ],
            'email' => [
                'label' => '이메일 사용',
                'description' => '이메일의 @ 앞부분을 표시명으로 사용',
                'preview' => $this->getPreviewForOption('email', $user)
            ]
        ];
    }
    
    private function getPreviewForOption($option, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        // 현재 입력된 값들을 사용하여 미리보기 계산
        $currentFirstName = $this->first_name ?: $user->first_name;
        $currentLastName = $this->last_name ?: $user->last_name;
        $currentNickname = $this->nickname ?: $user->nickname;
        $fullName = trim(($currentFirstName ?? '') . ' ' . ($currentLastName ?? ''));
        
        switch ($option) {
            case 'real_name':
                return $fullName ?: explode('@', $user->email)[0];
                
            case 'nickname':
                return $currentNickname ?: $fullName ?: explode('@', $user->email)[0];
                
            case 'email':
                return explode('@', $user->email)[0];
                
            case 'auto':
            default:
                if ($currentNickname) {
                    return $currentNickname;
                }
                
                if ($fullName) {
                    return $fullName;
                }
                
                return explode('@', $user->email)[0];
        }
    }
    
    public function updatedFirstName()
    {
        $this->loadDisplayNameOptions();
    }
    
    public function updatedLastName()
    {
        $this->loadDisplayNameOptions();
    }
    
    public function updatedNickname()
    {
        $this->loadDisplayNameOptions();
    }

    public function render()
    {
        return view('300-page-service.304-page-mypage-edit.300-livewire-form-profile');
    }
}