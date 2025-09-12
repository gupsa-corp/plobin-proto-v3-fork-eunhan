<div>
{{-- ============================================
     LIVEWIRE 회원가입 폼 - 분할된 인클루드 버전
     ============================================ --}}

{{-- 메시지 표시 섹션 --}}
@include('200-page-auth.202-page-auth-signup.100-alert-messages')

{{-- 회원가입 폼 --}}
<form wire:submit.prevent="register" class="space-y-6">
    
    {{-- 기본 정보 섹션 --}}
    @include('200-page-auth.202-page-auth-signup.200-basic-info-section')

    {{-- 이메일 인증 섹션 --}}
    @include('200-page-auth.202-page-auth-signup.300-email-verification-section')

    {{-- 휴대폰 인증 섹션 --}}
    @include('200-page-auth.202-page-auth-signup.400-phone-verification-section')

    {{-- 비밀번호 설정 섹션 --}}
    @include('200-page-auth.202-page-auth-signup.500-password-section')
    
    {{-- 회원가입 동의 섹션 --}}
    <div class="space-y-4">
        <h3 class="text-sm font-semibold text-gray-600 border-b border-gray-200 pb-2">회원가입 동의</h3>
        
        <!-- 전체 동의 -->
        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-md">
            <input 
                type="checkbox" 
                id="agree_all"
                wire:model.live="agree_all"
                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
            />
            <label for="agree_all" class="flex-1 text-sm font-semibold text-gray-700 cursor-pointer">
                전체 동의
            </label>
        </div>

        <!-- 개별 동의 항목들 -->
        <div class="space-y-3 pl-4">
            <!-- 만 14세 이상 -->
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="agree_age"
                    wire:model.live="agree_age"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="agree_age" class="text-sm text-gray-700 cursor-pointer">
                    <span class="text-red-500">(필수)</span> 만 14세 이상입니다
                </label>
            </div>
            @error('agree_age') <span class="text-red-500 text-xs ml-7">{{ $message }}</span> @enderror

            <!-- 이용약관 동의 -->
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="agree_terms"
                    wire:model.live="agree_terms"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="agree_terms" class="text-sm text-gray-700 cursor-pointer">
                    <span class="text-red-500">(필수)</span> 이용약관에 동의합니다
                </label>
            </div>
            @error('agree_terms') <span class="text-red-500 text-xs ml-7">{{ $message }}</span> @enderror

            <!-- 개인정보처리방침 동의 -->
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="agree_privacy"
                    wire:model.live="agree_privacy"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="agree_privacy" class="text-sm text-gray-700 cursor-pointer">
                    <span class="text-red-500">(필수)</span> 개인정보처리방침에 동의합니다
                </label>
            </div>
            @error('agree_privacy') <span class="text-red-500 text-xs ml-7">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- 회원가입 버튼 --}}
    @include('200-page-auth.202-page-auth-signup.900-submit-button')

</form>

{{-- 로그인 링크 --}}
<p class="mt-6 text-center text-sm text-gray-600">
    이미 계정이 있으신가요?
    <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">로그인</a>
</p>

{{-- JavaScript 섹션 --}}
@include('200-page-auth.202-page-auth-signup.800-scripts')

</div>