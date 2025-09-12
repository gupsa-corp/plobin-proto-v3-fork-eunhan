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
    @include('200-page-auth.202-page-auth-signup.600-agreement-section')

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