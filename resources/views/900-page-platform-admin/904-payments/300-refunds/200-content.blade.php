{{-- 환불 관리 메인 콘텐츠 --}}
<div class="refunds-content" style="padding: 24px;" x-data="refundsManagement">

    {{-- 필터 섹션 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">필터 및 검색</h3>
        
        <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- 검색 --}}
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                <input type="text" 
                       id="search"
                       name="search"
                       value="{{ $filters['search'] }}"
                       placeholder="조직명, 주문ID, 설명으로 검색..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            {{-- 기간 필터 --}}
            <div>
                <label for="period-filter" class="block text-sm font-medium text-gray-700 mb-2">기간</label>
                <select name="period" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="3months" {{ $filters['period'] == '3months' ? 'selected' : '' }}>최근 3개월</option>
                    <option value="6months" {{ $filters['period'] == '6months' ? 'selected' : '' }}>최근 6개월</option>
                    <option value="1year" {{ $filters['period'] == '1year' ? 'selected' : '' }}>최근 1년</option>
                    <option value="all" {{ $filters['period'] == 'all' ? 'selected' : '' }}>전체</option>
                </select>
            </div>
            
            {{-- 버튼들 --}}
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm flex-1">
                    필터 적용
                </button>
                <a href="{{ request()->url() }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm">
                    초기화
                </a>
            </div>
        </form>
    </div>

    {{-- 환불 목록 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">환불 목록</h3>
                <div class="text-sm text-gray-500">
                    총 {{ $refunds->total() }}개의 환불 건
                </div>
            </div>
        </div>

        @if($refunds->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                주문 정보
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                조직
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                결제 정보
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                금액
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                환불일
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                액션
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($refunds as $refund)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $refund->order_id }}</div>
                                    <div class="text-sm text-gray-500">{{ $refund->description ?: '설명 없음' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($refund->organization)
                                        <div class="text-sm text-gray-900">{{ $refund->organization->name }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $refund->organization->id }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">조직 정보 없음</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $refund->method ?: '알 수 없음' }}</div>
                                    @if($refund->card_number)
                                        <div class="text-sm text-gray-500">{{ $refund->card_number }}</div>
                                    @endif
                                    @if($refund->card_company)
                                        <div class="text-xs text-gray-400">{{ $refund->card_company }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $refund->getFormattedAmount() }}</div>
                                    @if($refund->vat)
                                        <div class="text-xs text-gray-500">VAT: {{ number_format($refund->vat) }}원</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($refund->approved_at)
                                        <div>{{ $refund->approved_at->format('Y-m-d') }}</div>
                                        <div class="text-xs text-gray-400">{{ $refund->approved_at->format('H:i:s') }}</div>
                                    @else
                                        <span class="text-gray-400">승인 대기</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="viewRefund('{{ $refund->id }}')" 
                                                class="text-blue-600 hover:text-blue-900">
                                            상세
                                        </button>
                                        @if($refund->receipt_url)
                                            <a href="{{ $refund->receipt_url }}" 
                                               target="_blank"
                                               class="text-green-600 hover:text-green-900">
                                                영수증
                                            </a>
                                        @endif
                                        <button @click="processAdditionalRefund('{{ $refund->id }}')" 
                                                class="text-orange-600 hover:text-orange-900">
                                            추가환불
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 페이지네이션 --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $refunds->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="text-gray-400 text-lg mb-2">🔄</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">환불 내역이 없습니다</h3>
                <p class="text-sm text-gray-500">필터 조건을 변경하거나 기간을 조정해보세요.</p>
            </div>
        @endif
    </div>

    {{-- 환불 상세 모달 --}}
    <div x-show="showRefundModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="showRefundModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">환불 상세 정보</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">주문 ID</label>
                            <p class="mt-1 text-sm text-gray-600" x-text="selectedRefund.order_id"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">환불 상태</label>
                            <span class="mt-1 px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                부분 취소
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">환불 금액</label>
                            <p class="mt-1 text-sm text-gray-600" x-text="selectedRefund.amount + '원'"></p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="showRefundModal = false" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        닫기
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 추가 환불 모달 --}}
    <div x-show="showAdditionalRefundModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.away="showAdditionalRefundModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">추가 환불 처리</h3>
                </div>
                <form @submit.prevent="submitAdditionalRefund()">
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">환불 금액</label>
                                <input type="number" 
                                       x-model="additionalRefundAmount"
                                       class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="환불할 금액을 입력하세요"
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">환불 사유</label>
                                <textarea x-model="additionalRefundReason"
                                         class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                         rows="3"
                                         placeholder="환불 사유를 입력하세요"
                                         required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                        <button type="button"
                                @click="showAdditionalRefundModal = false" 
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            취소
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            환불 처리
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('refundsManagement', () => ({
        showRefundModal: false,
        showAdditionalRefundModal: false,
        selectedRefund: {},
        additionalRefundAmount: '',
        additionalRefundReason: '',

        init() {
            console.log('Refunds management initialized');
        },

        viewRefund(refundId) {
            // 실제 구현시 AJAX로 환불 상세 정보를 가져와야 함
            this.selectedRefund = {
                id: refundId,
                order_id: 'ORD-' + refundId,
                amount: '50000'
            };
            this.showRefundModal = true;
        },

        processAdditionalRefund(refundId) {
            this.selectedRefund = { id: refundId };
            this.additionalRefundAmount = '';
            this.additionalRefundReason = '';
            this.showAdditionalRefundModal = true;
        },

        async submitAdditionalRefund() {
            if (!this.additionalRefundAmount || !this.additionalRefundReason) {
                alert('모든 필드를 입력해주세요.');
                return;
            }

            try {
                const response = await fetch(`/platform/admin/payments/${this.selectedRefund.id}/refund`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        refund_amount: this.additionalRefundAmount,
                        reason: this.additionalRefundReason
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('환불이 성공적으로 처리되었습니다.');
                    this.showAdditionalRefundModal = false;
                    location.reload();
                } else {
                    alert('환불 처리 중 오류가 발생했습니다: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('환불 처리 중 오류가 발생했습니다.');
            }
        }
    }));
});
</script>