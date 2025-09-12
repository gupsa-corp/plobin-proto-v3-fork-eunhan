{{-- ========================================
     이용약관 모달
     ======================================== --}}
<div x-data="{ showTermsModal: false }" 
     @open-terms-modal.window="showTermsModal = true">
    
    <!-- 모달 오버레이 -->
    <div x-show="showTermsModal" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         x-cloak>
        
        <!-- 모달 컨텐츠 -->
        <div x-show="showTermsModal" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform scale-95" 
             x-transition:enter-end="opacity-100 transform scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform scale-100" 
             x-transition:leave-end="opacity-0 transform scale-95" 
             @click.away="showTermsModal = false"
             class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col">
            
            <!-- 모달 헤더 -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">이용약관</h3>
                <button @click="showTermsModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- 모달 본문 -->
            <div class="p-6 overflow-y-auto flex-1">
                <div class="prose prose-sm max-w-none">
                    <h4>제1조 (목적)</h4>
                    <p>이 약관은 Plobin(이하 "회사")이 제공하는 서비스의 이용과 관련하여 회사와 이용자의 권리, 의무 및 책임사항, 기타 필요한 사항을 규정함을 목적으로 합니다.</p>
                    
                    <h4>제2조 (정의)</h4>
                    <p>1. "서비스"라 함은 회사가 제공하는 모든 서비스를 의미합니다.</p>
                    <p>2. "이용자"라 함은 회사의 서비스에 접속하여 이 약관에 따라 회사가 제공하는 서비스를 받는 회원 및 비회원을 말합니다.</p>
                    <p>3. "회원"이라 함은 회사에 개인정보를 제공하여 회원등록을 한 자로서, 회사의 정보를 지속적으로 제공받으며, 회사가 제공하는 서비스를 계속적으로 이용할 수 있는 자를 말합니다.</p>
                    
                    <h4>제3조 (약관의 효력 및 변경)</h4>
                    <p>1. 이 약관은 서비스 화면에 게시하거나 기타의 방법으로 이용자에게 공지함으로써 효력을 발생합니다.</p>
                    <p>2. 회사는 합리적인 사유가 발생할 경우 관련 법령에 위배되지 않는 범위에서 이 약관을 변경할 수 있습니다.</p>
                    
                    <h4>제4조 (서비스의 제공 및 변경)</h4>
                    <p>1. 회사는 다음과 같은 업무를 수행합니다.</p>
                    <p>- 프로젝트 관리 서비스 제공</p>
                    <p>- 조직 관리 서비스 제공</p>
                    <p>- 기타 회사가 정하는 업무</p>
                    
                    <h4>제5조 (서비스의 중단)</h4>
                    <p>1. 회사는 컴퓨터 등 정보통신설비의 보수점검, 교체 및 고장, 통신의 두절 등의 사유가 발생한 경우에는 서비스의 제공을 일시적으로 중단할 수 있습니다.</p>
                    
                    <h4>제6조 (회원가입)</h4>
                    <p>1. 이용자는 회사가 정한 가입 양식에 따라 회원정보를 기입한 후 이 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청합니다.</p>
                    <p>2. 회사는 제1항과 같이 회원으로 가입할 것을 신청한 이용자 중 다음 각 호에 해당하지 않는 한 회원으로 등록합니다.</p>
                    
                    <h4>제7조 (개인정보보호)</h4>
                    <p>회사는 관련법령이 정하는 바에 따라서 이용자 등록정보를 포함한 이용자의 개인정보를 보호하기 위하여 노력합니다. 이용자의 개인정보보호에 관해서는 관련법령 및 회사가 정하는 개인정보처리방침에 정한 바에 의합니다.</p>
                </div>
            </div>
            
            <!-- 모달 푸터 -->
            <div class="flex justify-end p-6 border-t border-gray-200">
                <button @click="showTermsModal = false" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                    확인
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js cloaking style -->
<style>
    [x-cloak] { display: none !important; }
</style>