{{-- ========================================
     개인정보처리방침 모달
     ======================================== --}}
<div x-data="{ showPrivacyModal: false }" 
     @open-privacy-modal.window="showPrivacyModal = true">
    
    <!-- 모달 오버레이 -->
    <div x-show="showPrivacyModal" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         x-cloak>
        
        <!-- 모달 컨텐츠 -->
        <div x-show="showPrivacyModal" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform scale-95" 
             x-transition:enter-end="opacity-100 transform scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform scale-100" 
             x-transition:leave-end="opacity-0 transform scale-95" 
             @click.away="showPrivacyModal = false"
             class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] flex flex-col">
            
            <!-- 모달 헤더 -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">개인정보처리방침</h3>
                <button @click="showPrivacyModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- 모달 본문 -->
            <div class="p-6 overflow-y-auto flex-1">
                <div class="prose prose-sm max-w-none">
                    <h4>1. 개인정보의 처리 목적</h4>
                    <p>Plobin(이하 "회사")는 다음의 목적을 위하여 개인정보를 처리하고 있으며, 다음의 목적 이외의 용도로는 이용하지 않습니다.</p>
                    <ul>
                        <li>회원 가입의사 확인, 회원제 서비스 제공에 따른 본인 식별·인증</li>
                        <li>서비스 제공에 관한 계약 이행 및 서비스 제공에 따른 요금정산</li>
                        <li>고객 문의에 대한 응답</li>
                        <li>마케팅 및 광고에의 활용</li>
                    </ul>
                    
                    <h4>2. 개인정보의 처리 및 보유기간</h4>
                    <p>회사는 정보주체로부터 개인정보를 수집할 때 동의받은 개인정보 보유·이용기간 또는 법령에 따른 개인정보 보유·이용기간 내에서 개인정보를 처리·보유합니다.</p>
                    <ul>
                        <li>회원정보: 회원탈퇴 시까지 (단, 관련 법령에 의하여 보존 필요시 해당 기간)</li>
                        <li>로그 데이터: 1년</li>
                    </ul>
                    
                    <h4>3. 개인정보의 제3자 제공</h4>
                    <p>회사는 개인정보를 제1조(개인정보의 처리 목적)에서 명시한 범위 내에서만 처리하며, 정보주체의 동의, 법률의 특별한 규정 등 개인정보 보호법 제17조 및 제18조에 해당하는 경우에만 개인정보를 제3자에게 제공합니다.</p>
                    
                    <h4>4. 개인정보처리 위탁</h4>
                    <p>회사는 원활한 개인정보 업무처리를 위하여 다음과 같이 개인정보 처리업무를 위탁하고 있습니다.</p>
                    <ul>
                        <li>수탁업체: AWS (Amazon Web Services)</li>
                        <li>위탁업무 내용: 클라우드 서비스 제공</li>
                    </ul>
                    
                    <h4>5. 정보주체의 권리·의무 및 그 행사방법</h4>
                    <p>이용자는 개인정보주체로서 다음과 같은 권리를 행사할 수 있습니다.</p>
                    <ul>
                        <li>개인정보 열람요구</li>
                        <li>오류 등이 있을 경우 정정·삭제 요구</li>
                        <li>처리정지 요구</li>
                    </ul>
                    
                    <h4>6. 처리하는 개인정보의 항목 작성</h4>
                    <p>회사는 다음의 개인정보 항목을 처리하고 있습니다.</p>
                    <ul>
                        <li>필수항목: 이름, 이메일 주소, 휴대폰번호, 비밀번호</li>
                        <li>선택항목: 프로필 사진</li>
                        <li>자동수집항목: 서비스 이용기록, 접속 로그, 쿠키, 접속 IP 정보</li>
                    </ul>
                    
                    <h4>7. 개인정보의 안전성 확보 조치</h4>
                    <p>회사는 개인정보보호법 제29조에 따라 다음과 같이 안전성 확보에 필요한 기술적/관리적 및 물리적 조치를 하고 있습니다.</p>
                    <ul>
                        <li>개인정보 취급 직원의 최소화 및 교육</li>
                        <li>개인정보의 암호화</li>
                        <li>해킹 등에 대비한 기술적 대책</li>
                        <li>개인정보에 대한 접근 제한</li>
                    </ul>
                    
                    <h4>8. 개인정보 보호책임자</h4>
                    <p>개인정보 처리에 관한 업무를 총괄해서 책임지고, 개인정보 처리와 관련한 정보주체의 불만처리 및 피해구제 등을 위하여 아래와 같이 개인정보 보호책임자를 지정하고 있습니다.</p>
                    <ul>
                        <li>개인정보 보호책임자: 개발팀장</li>
                        <li>연락처: privacy@plobin.com</li>
                    </ul>
                </div>
            </div>
            
            <!-- 모달 푸터 -->
            <div class="flex justify-end p-6 border-t border-gray-200">
                <button @click="showPrivacyModal = false" 
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