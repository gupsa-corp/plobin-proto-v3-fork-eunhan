<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DisplayNameSettings extends Component
{
    public $display_name_preference = 'auto';
    public $user;
    
    public $availableOptions = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->display_name_preference = $this->user->display_name_preference ?? 'auto';
        
        $this->loadAvailableOptions();
    }
    
    public function loadAvailableOptions()
    {
        $this->availableOptions = [
            'auto' => [
                'label' => '자동 설정',
                'description' => '닉네임 → 실명 → 이메일 순서로 자동 선택',
                'preview' => $this->getPreviewForOption('auto')
            ],
            'real_name' => [
                'label' => '실명 우선',
                'description' => '실명을 표시명으로 사용 (없으면 이메일)',
                'preview' => $this->getPreviewForOption('real_name')
            ],
            'nickname' => [
                'label' => '닉네임 우선',
                'description' => '닉네임을 표시명으로 사용 (없으면 실명 → 이메일)',
                'preview' => $this->getPreviewForOption('nickname')
            ],
            'email' => [
                'label' => '이메일 사용',
                'description' => '이메일의 @ 앞부분을 표시명으로 사용',
                'preview' => $this->getPreviewForOption('email')
            ]
        ];
    }
    
    private function getPreviewForOption($option)
    {
        switch ($option) {
            case 'real_name':
                $fullName = $this->user->full_name;
                return $fullName ?: explode('@', $this->user->email)[0];
                
            case 'nickname':
                return $this->user->nickname ?: $this->user->full_name ?: explode('@', $this->user->email)[0];
                
            case 'email':
                return explode('@', $this->user->email)[0];
                
            case 'auto':
            default:
                if ($this->user->nickname) {
                    return $this->user->nickname;
                }
                
                $fullName = $this->user->full_name;
                if ($fullName) {
                    return $fullName;
                }
                
                return explode('@', $this->user->email)[0];
        }
    }
    
    public function updateDisplayNamePreference()
    {
        $this->validate([
            'display_name_preference' => 'required|in:auto,real_name,nickname,email'
        ]);
        
        $this->user->update([
            'display_name_preference' => $this->display_name_preference
        ]);
        
        // 옵션 미리보기 다시 로드
        $this->loadAvailableOptions();
        
        session()->flash('message', '표시명 설정이 성공적으로 변경되었습니다.');
        
        // 페이지 새로고침으로 헤더의 표시명도 업데이트
        $this->redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.profile.display-name-settings');
    }
}
