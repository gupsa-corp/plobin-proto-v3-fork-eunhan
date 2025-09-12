{{-- 결제 관리 메인 콘텐츠 --}}
<div class="p-6">
    <!-- 필터 섹션 -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">조직명</label>
                <input type="text" name="organization" value="{{ request('organization') }}" 
                       placeholder="조직명으로 검색..." 
                       class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">결제 상태</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">전체</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>완료</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>대기</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>취소</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>환불</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">결제일 (시작)</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">결제일 (종료)</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                필터 적용
            </button>
        </form>
    </div>

    <!-- 결제 내역 목록 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">결제 내역</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">결제 정보</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">조직</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">플랜</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">결제 금액</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">결제일</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments ?? [] as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $payment->payment_id ?? 'PAY-' . $payment->id }}</div>
                                    <div class="text-sm text-gray-500">{{ $payment->payment_method ?? 'Card' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $payment->organization->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">ID: {{ $payment->organization->id ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->plan_name ?? 'Standard Plan' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">
                                    ₩{{ number_format($payment->amount ?? 29900) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $status = $payment->status ?? 'completed';
                                    $statusClasses = [
                                        'completed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusNames = [
                                        'completed' => '완료',
                                        'pending' => '대기',
                                        'cancelled' => '취소',
                                        'refunded' => '환불'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusNames[$status] ?? $status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->created_at->format('Y.m.d H:i') ?? '2024.01.01 12:00' }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">상세보기</a>
                                @if(($payment->status ?? 'completed') === 'completed')
                                    <button onclick="showCancelModal('{{ $payment->id ?? 1 }}')" 
                                            class="text-red-600 hover:text-red-900">취소</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                결제 내역이 없습니다.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($payments) && method_exists($payments, 'hasPages') && $payments->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- 결제 취소 모달 -->
<div id="cancelModal" class="fixed inset-0 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">결제 취소</h3>
            <p class="text-sm text-gray-600 mb-4">
                이 결제를 취소하시겠습니까? 취소된 결제는 복원할 수 없습니다.
            </p>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">취소 사유</label>
                    <textarea name="reason" required rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                              placeholder="취소 사유를 입력하세요"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideCancelModal()" 
                            class="px-4 py-2 text-gray-500 hover:text-gray-700">취소</button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">결제 취소</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showCancelModal(paymentId) {
        document.getElementById('cancelForm').action = `/platform/admin/payments/${paymentId}/cancel`;
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function hideCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }
</script>