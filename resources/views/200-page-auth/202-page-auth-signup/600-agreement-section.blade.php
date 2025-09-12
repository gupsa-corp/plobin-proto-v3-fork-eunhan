{{-- ========================================
     회원가입 동의 섹션
     ======================================== --}}
<div class="space-y-4">
    <h3 class="text-sm font-semibold text-gray-600 border-b border-gray-200 pb-2">회원가입 동의</h3>
    
    <!-- 전체 동의 -->
    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-md">
        <input 
            type="checkbox" 
            id="agree_all"
            wire:model="agree_all"
            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
        />
        <label for="agree_all" class="flex-1 text-sm font-semibold text-gray-700 cursor-pointer">
            전체 동의
        </label>
    </div>

    <!-- 개별 동의 항목들 -->
    <div class="space-y-3 pl-4">
        <!-- 만 14세 이상 -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="agree_age"
                    wire:model="agree_age"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="agree_age" class="text-sm text-gray-700 cursor-pointer">
                    <span class="text-red-500">(필수)</span> 만 14세 이상입니다
                </label>
            </div>
        </div>
        @error('agree_age') <span class="text-red-500 text-xs ml-7">{{ $message }}</span> @enderror

        <!-- 이용약관 동의 -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="agree_terms"
                    wire:model="agree_terms"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="agree_terms" class="text-sm text-gray-700 cursor-pointer">
                    <span class="text-red-500">(필수)</span> 이용약관에 동의합니다
                </label>
            </div>
            <button 
                type="button" 
                wire:click="$dispatch('open-terms-modal')"
                class="text-xs text-blue-600 hover:text-blue-800 underline focus:outline-none"
            >
                보기 🔗
            </button>
        </div>
        @error('agree_terms') <span class="text-red-500 text-xs ml-7">{{ $message }}</span> @enderror

        <!-- 개인정보처리방침 동의 -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <input 
                    type="checkbox" 
                    id="agree_privacy"
                    wire:model="agree_privacy"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="agree_privacy" class="text-sm text-gray-700 cursor-pointer">
                    <span class="text-red-500">(필수)</span> 개인정보처리방침에 동의합니다
                </label>
            </div>
            <button 
                type="button" 
                wire:click="$dispatch('open-privacy-modal')"
                class="text-xs text-blue-600 hover:text-blue-800 underline focus:outline-none"
            >
                보기 🔗
            </button>
        </div>
        @error('agree_privacy') <span class="text-red-500 text-xs ml-7">{{ $message }}</span> @enderror
    </div>

    <!-- 이용약관 모달 -->
    @include('200-page-auth.202-page-auth-signup.601-terms-modal')

    <!-- 개인정보처리방침 모달 -->
    @include('200-page-auth.202-page-auth-signup.602-privacy-modal')
</div>