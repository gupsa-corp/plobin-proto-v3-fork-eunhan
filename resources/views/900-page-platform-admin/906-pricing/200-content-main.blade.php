{{-- 요금제 관리 메인 콘텐츠 --}}
<div class="p-6" id="pricing-management">
    {{-- 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="stats-cards">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">총 요금제</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-plans">로딩 중...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">활성 구독</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-subscriptions">로딩 중...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">월 수익</dt>
                            <dd class="text-lg font-medium text-gray-900" id="monthly-revenue">로딩 중...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">활성 플랜</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-plans">로딩 중...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 요금제 목록 --}}
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">요금제 목록</h2>
                <div class="flex items-center space-x-3">
                    <button id="add-plan-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        새 요금제 추가
                    </button>
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="요금제 검색..." class="block w-64 pr-10 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <select id="status-filter" class="border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">모든 상태</option>
                        <option value="1">활성</option>
                        <option value="0">비활성</option>
                    </select>
                    <select id="type-filter" class="border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">모든 타입</option>
                        <option value="monthly">월간 고정</option>
                        <option value="usage_based">사용량 기반</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">요금제명</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">타입</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">가격</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">구독자 수</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">생성일</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                    </tr>
                </thead>
                <tbody id="plans-tbody" class="bg-white divide-y divide-gray-200">
                    {{-- 동적으로 로드됨 --}}
                </tbody>
            </table>
            
            {{-- 로딩 스피너 --}}
            <div id="loading-spinner" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-2 text-gray-600">로딩 중...</span>
            </div>
            
            {{-- 빈 상태 --}}
            <div id="empty-state" class="hidden text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">요금제가 없습니다</h3>
                <p class="mt-1 text-sm text-gray-500">새 요금제를 추가하여 시작하세요.</p>
                <div class="mt-6">
                    <button id="empty-add-plan-btn" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        새 요금제 추가
                    </button>
                </div>
            </div>
        </div>

        {{-- 페이지네이션 --}}
        <div id="pagination" class="hidden px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700" id="pagination-info">
                    총 <span class="font-medium" id="total-count">0</span>개 요금제
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 요금제 생성/수정 모달 --}}
<div id="plan-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">새 요금제 추가</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="plan-form" class="mt-6 space-y-6">
                <input type="hidden" id="plan-id" name="id">
                
                {{-- 기본 정보 --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="plan-name" class="block text-sm font-medium text-gray-700">요금제명 *</label>
                        <input type="text" id="plan-name" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="plan-slug" class="block text-sm font-medium text-gray-700">슬러그 *</label>
                        <input type="text" id="plan-slug" name="slug" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="plan-description" class="block text-sm font-medium text-gray-700">설명</label>
                    <textarea id="plan-description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                {{-- 플랜 타입 --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">플랜 타입 *</label>
                    <div class="mt-2 space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="monthly" class="form-radio text-blue-600" required>
                            <span class="ml-2">월간 고정</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="usage_based" class="form-radio text-blue-600">
                            <span class="ml-2">사용량 기반</span>
                        </label>
                    </div>
                </div>
                
                {{-- 월간 고정 플랜 설정 --}}
                <div id="monthly-settings" class="space-y-4">
                    <h4 class="text-lg font-medium text-gray-900">월간 고정 플랜 설정</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="monthly-price" class="block text-sm font-medium text-gray-700">월 가격 (원)</label>
                            <input type="number" id="monthly-price" name="monthly_price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="max-members" class="block text-sm font-medium text-gray-700">최대 멤버 수</label>
                            <input type="number" id="max-members" name="max_members" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="무제한인 경우 비워두세요">
                        </div>
                        <div>
                            <label for="max-projects" class="block text-sm font-medium text-gray-700">최대 프로젝트 수</label>
                            <input type="number" id="max-projects" name="max_projects" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="무제한인 경우 비워두세요">
                        </div>
                        <div>
                            <label for="max-sheets" class="block text-sm font-medium text-gray-700">최대 시트 수</label>
                            <input type="number" id="max-sheets" name="max_sheets" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="무제한인 경우 비워두세요">
                        </div>
                        <div class="md:col-span-2">
                            <label for="max-storage" class="block text-sm font-medium text-gray-700">최대 스토리지 (GB)</label>
                            <input type="number" id="max-storage" name="max_storage_gb" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="무제한인 경우 비워두세요">
                        </div>
                    </div>
                </div>
                
                {{-- 사용량 기반 플랜 설정 --}}
                <div id="usage-settings" class="hidden space-y-4">
                    <h4 class="text-lg font-medium text-gray-900">사용량 기반 플랜 설정</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="price-per-member" class="block text-sm font-medium text-gray-700">멤버당 가격 (원/월)</label>
                            <input type="number" id="price-per-member" name="price_per_member" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="price-per-project" class="block text-sm font-medium text-gray-700">프로젝트당 가격 (원/월)</label>
                            <input type="number" id="price-per-project" name="price_per_project" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="price-per-gb" class="block text-sm font-medium text-gray-700">GB당 가격 (원/월)</label>
                            <input type="number" id="price-per-gb" name="price_per_gb" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="price-per-sheet" class="block text-sm font-medium text-gray-700">시트당 가격 (원/월)</label>
                            <input type="number" id="price-per-sheet" name="price_per_sheet" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <h5 class="text-md font-medium text-gray-700">무료 허용량</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="free-members" class="block text-sm font-medium text-gray-700">무료 멤버 수</label>
                            <input type="number" id="free-members" name="free_members" value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="free-projects" class="block text-sm font-medium text-gray-700">무료 프로젝트 수</label>
                            <input type="number" id="free-projects" name="free_projects" value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="free-storage" class="block text-sm font-medium text-gray-700">무료 스토리지 (GB)</label>
                            <input type="number" id="free-storage" name="free_storage_gb" value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="free-sheets" class="block text-sm font-medium text-gray-700">무료 시트 수</label>
                            <input type="number" id="free-sheets" name="free_sheets" value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                
                {{-- 기타 설정 --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sort-order" class="block text-sm font-medium text-gray-700">정렬 순서</label>
                        <input type="number" id="sort-order" name="sort_order" value="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-center space-x-4 pt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" id="is-active" class="form-checkbox text-blue-600" checked>
                            <span class="ml-2">활성화</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_featured" id="is-featured" class="form-checkbox text-blue-600">
                            <span class="ml-2">추천 플랜</span>
                        </label>
                    </div>
                </div>
                
                {{-- 버튼들 --}}
                <div class="flex items-center justify-end pt-6 border-t space-x-3">
                    <button type="button" id="cancel-btn" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        취소
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 구독 취소 모달 --}}
<div id="cancel-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">구독 취소</h3>
                <button id="close-cancel-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="cancel-form" class="mt-6 space-y-4">
                <input type="hidden" id="subscription-id">
                <div>
                    <label for="cancel-reason" class="block text-sm font-medium text-gray-700">취소 사유</label>
                    <textarea id="cancel-reason" name="reason" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="구독을 취소하는 이유를 입력해주세요 (선택사항)"></textarea>
                </div>
                
                <div class="flex items-center justify-end pt-6 border-t space-x-3">
                    <button type="button" id="cancel-cancel-btn" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        취소
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        구독 취소
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 전역 변수
    let currentPlans = [];
    let filteredPlans = [];
    
    // DOM 요소들
    const planModal = document.getElementById('plan-modal');
    const cancelModal = document.getElementById('cancel-modal');
    const planForm = document.getElementById('plan-form');
    const cancelForm = document.getElementById('cancel-form');
    const plansTableBody = document.getElementById('plans-tbody');
    const loadingSpinner = document.getElementById('loading-spinner');
    const emptyState = document.getElementById('empty-state');
    
    // 초기 로드
    loadStatistics();
    loadPlans();
    
    // 이벤트 리스너들
    document.getElementById('add-plan-btn').addEventListener('click', () => openPlanModal());
    document.getElementById('empty-add-plan-btn').addEventListener('click', () => openPlanModal());
    document.getElementById('close-modal').addEventListener('click', closePlanModal);
    document.getElementById('cancel-btn').addEventListener('click', closePlanModal);
    
    document.getElementById('close-cancel-modal').addEventListener('click', closeCancelModal);
    document.getElementById('cancel-cancel-btn').addEventListener('click', closeCancelModal);
    
    // 검색 및 필터 이벤트
    document.getElementById('search-input').addEventListener('input', filterPlans);
    document.getElementById('status-filter').addEventListener('change', filterPlans);
    document.getElementById('type-filter').addEventListener('change', filterPlans);
    
    // 플랜 타입 변경 이벤트
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', togglePlanTypeSettings);
    });
    
    // 폼 제출 이벤트
    planForm.addEventListener('submit', handlePlanSubmit);
    cancelForm.addEventListener('submit', handleCancelSubmit);
    
    // 통계 데이터 로드
    async function loadStatistics() {
        try {
            const response = await fetch('/api/platform/admin/pricing/statistics');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('total-plans').textContent = data.data.total_plans + '개';
                document.getElementById('active-subscriptions').textContent = data.data.active_subscriptions.toLocaleString() + '명';
                document.getElementById('monthly-revenue').textContent = '₩' + data.data.monthly_revenue.toLocaleString();
                document.getElementById('active-plans').textContent = data.data.active_plans + '개';
            }
        } catch (error) {
            console.error('통계 로드 실패:', error);
        }
    }
    
    // 플랜 목록 로드
    async function loadPlans() {
        try {
            showLoading();
            const response = await fetch('/api/platform/admin/pricing/plans');
            const data = await response.json();
            
            if (data.success) {
                currentPlans = data.data;
                filteredPlans = [...currentPlans];
                renderPlans();
            } else {
                showError('플랜 목록을 불러올 수 없습니다.');
            }
        } catch (error) {
            console.error('플랜 로드 실패:', error);
            showError('네트워크 오류가 발생했습니다.');
        } finally {
            hideLoading();
        }
    }
    
    // 플랜 목록 렌더링
    function renderPlans() {
        if (filteredPlans.length === 0) {
            showEmptyState();
            return;
        }
        
        hideEmptyState();
        plansTableBody.innerHTML = filteredPlans.map(plan => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full ${getPlanColor(plan.type)} flex items-center justify-center">
                                ${getPlanIcon(plan.type)}
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${plan.name}</div>
                            <div class="text-sm text-gray-500">${plan.description || '설명 없음'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full ${plan.type === 'monthly' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">
                        ${plan.type === 'monthly' ? '월간 고정' : '사용량 기반'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${formatPrice(plan)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${plan.subscriptions?.length || 0}명
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${plan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${plan.is_active ? '활성' : '비활성'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${new Date(plan.created_at).toLocaleDateString('ko-KR')}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button onclick="editPlan(${plan.id})" class="text-blue-600 hover:text-blue-900">편집</button>
                    <button onclick="togglePlanStatus(${plan.id})" class="text-${plan.is_active ? 'red' : 'green'}-600 hover:text-${plan.is_active ? 'red' : 'green'}-900">
                        ${plan.is_active ? '비활성화' : '활성화'}
                    </button>
                    <button onclick="deletePlan(${plan.id})" class="text-red-600 hover:text-red-900">삭제</button>
                </td>
            </tr>
        `).join('');
        
        updatePaginationInfo();
    }
    
    // 유틸리티 함수들
    function getPlanColor(type) {
        return type === 'monthly' ? 'bg-blue-100' : 'bg-purple-100';
    }
    
    function getPlanIcon(type) {
        if (type === 'monthly') {
            return '<svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>';
        } else {
            return '<svg class="h-6 w-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
    }
    
    function formatPrice(plan) {
        if (plan.type === 'monthly') {
            return plan.monthly_price ? `₩${plan.monthly_price.toLocaleString()}/월` : '무료';
        } else {
            return '사용량 기반';
        }
    }
    
    function showLoading() {
        loadingSpinner.classList.remove('hidden');
        plansTableBody.innerHTML = '';
        emptyState.classList.add('hidden');
    }
    
    function hideLoading() {
        loadingSpinner.classList.add('hidden');
    }
    
    function showEmptyState() {
        emptyState.classList.remove('hidden');
        plansTableBody.innerHTML = '';
    }
    
    function hideEmptyState() {
        emptyState.classList.add('hidden');
    }
    
    function updatePaginationInfo() {
        const totalCount = document.getElementById('total-count');
        if (totalCount) {
            totalCount.textContent = filteredPlans.length;
        }
    }
    
    function showError(message) {
        alert(message); // 나중에 더 좋은 알림 시스템으로 대체
    }
    
    // 필터링 함수
    function filterPlans() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const statusFilter = document.getElementById('status-filter').value;
        const typeFilter = document.getElementById('type-filter').value;
        
        filteredPlans = currentPlans.filter(plan => {
            const matchesSearch = !searchTerm || 
                plan.name.toLowerCase().includes(searchTerm) ||
                (plan.description && plan.description.toLowerCase().includes(searchTerm));
                
            const matchesStatus = !statusFilter || 
                (statusFilter === '1' && plan.is_active) ||
                (statusFilter === '0' && !plan.is_active);
                
            const matchesType = !typeFilter || plan.type === typeFilter;
            
            return matchesSearch && matchesStatus && matchesType;
        });
        
        renderPlans();
    }
    
    // 모달 관련 함수들
    function openPlanModal(plan = null) {
        document.getElementById('modal-title').textContent = plan ? '요금제 수정' : '새 요금제 추가';
        
        if (plan) {
            fillPlanForm(plan);
        } else {
            resetPlanForm();
        }
        
        planModal.classList.remove('hidden');
    }
    
    function closePlanModal() {
        planModal.classList.add('hidden');
        resetPlanForm();
    }
    
    function openCancelModal(subscriptionId) {
        document.getElementById('subscription-id').value = subscriptionId;
        cancelModal.classList.remove('hidden');
    }
    
    function closeCancelModal() {
        cancelModal.classList.add('hidden');
        document.getElementById('cancel-form').reset();
    }
    
    function fillPlanForm(plan) {
        document.getElementById('plan-id').value = plan.id;
        document.getElementById('plan-name').value = plan.name;
        document.getElementById('plan-slug').value = plan.slug;
        document.getElementById('plan-description').value = plan.description || '';
        
        // 플랜 타입 설정
        document.querySelector(`input[name="type"][value="${plan.type}"]`).checked = true;
        togglePlanTypeSettings();
        
        // 월간 고정 플랜 필드들
        if (plan.type === 'monthly') {
            document.getElementById('monthly-price').value = plan.monthly_price || '';
            document.getElementById('max-members').value = plan.max_members || '';
            document.getElementById('max-projects').value = plan.max_projects || '';
            document.getElementById('max-sheets').value = plan.max_sheets || '';
            document.getElementById('max-storage').value = plan.max_storage_gb || '';
        }
        
        // 사용량 기반 플랜 필드들
        if (plan.type === 'usage_based') {
            document.getElementById('price-per-member').value = plan.price_per_member || '';
            document.getElementById('price-per-project').value = plan.price_per_project || '';
            document.getElementById('price-per-gb').value = plan.price_per_gb || '';
            document.getElementById('price-per-sheet').value = plan.price_per_sheet || '';
            document.getElementById('free-members').value = plan.free_members || 0;
            document.getElementById('free-projects').value = plan.free_projects || 0;
            document.getElementById('free-storage').value = plan.free_storage_gb || 0;
            document.getElementById('free-sheets').value = plan.free_sheets || 0;
        }
        
        document.getElementById('sort-order').value = plan.sort_order || 0;
        document.getElementById('is-active').checked = plan.is_active;
        document.getElementById('is-featured').checked = plan.is_featured;
    }
    
    function resetPlanForm() {
        planForm.reset();
        document.getElementById('plan-id').value = '';
        document.querySelector('input[name="type"][value="monthly"]').checked = true;
        togglePlanTypeSettings();
    }
    
    function togglePlanTypeSettings() {
        const selectedType = document.querySelector('input[name="type"]:checked').value;
        const monthlySettings = document.getElementById('monthly-settings');
        const usageSettings = document.getElementById('usage-settings');
        
        if (selectedType === 'monthly') {
            monthlySettings.classList.remove('hidden');
            usageSettings.classList.add('hidden');
        } else {
            monthlySettings.classList.add('hidden');
            usageSettings.classList.remove('hidden');
        }
    }
    
    // 폼 제출 핸들러들
    async function handlePlanSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(planForm);
        const planData = Object.fromEntries(formData.entries());
        
        // 체크박스 처리
        planData.is_active = document.getElementById('is-active').checked;
        planData.is_featured = document.getElementById('is-featured').checked;
        
        const planId = document.getElementById('plan-id').value;
        const isEdit = !!planId;
        
        try {
            const url = isEdit ? `/api/platform/admin/pricing/plans/${planId}` : '/api/platform/admin/pricing/plans';
            const method = isEdit ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(planData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                closePlanModal();
                loadPlans();
                loadStatistics();
                alert(data.message);
            } else {
                showError(data.message || '저장에 실패했습니다.');
            }
        } catch (error) {
            console.error('플랜 저장 실패:', error);
            showError('네트워크 오류가 발생했습니다.');
        }
    }
    
    async function handleCancelSubmit(e) {
        e.preventDefault();
        
        const subscriptionId = document.getElementById('subscription-id').value;
        const reason = document.getElementById('cancel-reason').value;
        
        try {
            const response = await fetch(`/api/platform/admin/pricing/subscriptions/${subscriptionId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason })
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeCancelModal();
                loadPlans();
                loadStatistics();
                alert(data.message);
            } else {
                showError(data.message || '취소에 실패했습니다.');
            }
        } catch (error) {
            console.error('구독 취소 실패:', error);
            showError('네트워크 오류가 발생했습니다.');
        }
    }
    
    // 전역 함수들 (인라인 이벤트 핸들러용)
    window.editPlan = function(planId) {
        const plan = currentPlans.find(p => p.id === planId);
        if (plan) {
            openPlanModal(plan);
        }
    };
    
    window.togglePlanStatus = async function(planId) {
        const plan = currentPlans.find(p => p.id === planId);
        if (!plan) return;
        
        if (confirm(`정말로 이 요금제를 ${plan.is_active ? '비활성화' : '활성화'}하시겠습니까?`)) {
            try {
                const response = await fetch(`/api/platform/admin/pricing/plans/${planId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ...plan,
                        is_active: !plan.is_active
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadPlans();
                    loadStatistics();
                    alert(data.message);
                } else {
                    showError(data.message || '상태 변경에 실패했습니다.');
                }
            } catch (error) {
                console.error('상태 변경 실패:', error);
                showError('네트워크 오류가 발생했습니다.');
            }
        }
    };
    
    window.deletePlan = async function(planId) {
        if (confirm('정말로 이 요금제를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
            try {
                const response = await fetch(`/api/platform/admin/pricing/plans/${planId}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadPlans();
                    loadStatistics();
                    alert(data.message);
                } else {
                    showError(data.message || '삭제에 실패했습니다.');
                }
            } catch (error) {
                console.error('삭제 실패:', error);
                showError('네트워크 오류가 발생했습니다.');
            }
        }
    };
});
</script>