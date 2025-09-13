<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '구독 관리'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.906-pricing.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('900-page-platform-admin.906-pricing.100-header-main')
            <div class="p-6">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">📊 구독 관리</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">조직</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">요금제</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">다음 결제일</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                                </tr>
                            </thead>
                            <tbody id="subscriptions-table-body" class="bg-white divide-y divide-gray-200">
                                <tr id="loading-row">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        구독 데이터를 불러오는 중...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
    
    <script>
        // 구독 데이터 로드
        async function loadSubscriptions() {
            try {
                const response = await fetch('/api/platform/admin/pricing/subscriptions');
                const result = await response.json();
                
                if (result.success) {
                    renderSubscriptions(result.data.data || []);
                } else {
                    showError(result.message || '구독 데이터를 불러올 수 없습니다.');
                }
            } catch (error) {
                console.error('구독 데이터 로드 오류:', error);
                showError('서버와 연결할 수 없습니다.');
            }
        }

        // 구독 목록 렌더링
        function renderSubscriptions(subscriptions) {
            const tbody = document.getElementById('subscriptions-table-body');
            
            if (subscriptions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            등록된 구독이 없습니다.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = subscriptions.map(subscription => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            ${subscription.organization?.name || '알 수 없음'}
                        </div>
                        <div class="text-sm text-gray-500">
                            ID: ${subscription.organization?.id || 'N/A'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            ${subscription.plan_name || '알 수 없음'}
                        </div>
                        <div class="text-sm text-gray-500">
                            ₩${subscription.monthly_price?.toLocaleString() || '0'}/월
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(subscription.status)}">
                            ${getStatusText(subscription.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${subscription.next_payment_date ? new Date(subscription.next_payment_date).toLocaleDateString('ko-KR') : '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="editSubscription(${subscription.id})" 
                                class="text-blue-600 hover:text-blue-900 mr-3">
                            수정
                        </button>
                        <button onclick="toggleSubscriptionStatus(${subscription.id}, '${subscription.status}')"
                                class="text-${subscription.status === 'active' ? 'red' : 'green'}-600 hover:text-${subscription.status === 'active' ? 'red' : 'green'}-900">
                            ${subscription.status === 'active' ? '일시정지' : '활성화'}
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // 구독 상태별 스타일링
        function getStatusClass(status) {
            switch (status) {
                case 'active':
                    return 'bg-green-100 text-green-800';
                case 'cancelled':
                    return 'bg-red-100 text-red-800';
                case 'suspended':
                    return 'bg-yellow-100 text-yellow-800';
                case 'pending':
                    return 'bg-gray-100 text-gray-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        // 구독 상태 텍스트
        function getStatusText(status) {
            switch (status) {
                case 'active':
                    return '활성';
                case 'cancelled':
                    return '취소됨';
                case 'suspended':
                    return '일시정지';
                case 'pending':
                    return '대기중';
                default:
                    return '알 수 없음';
            }
        }

        // 구독 상태 토글
        async function toggleSubscriptionStatus(subscriptionId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
            const action = newStatus === 'active' ? '활성화' : '일시정지';
            
            if (!confirm(`정말로 이 구독을 ${action}하시겠습니까?`)) return;

            try {
                const response = await fetch(`/api/platform/admin/pricing/subscriptions/${subscriptionId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert(`구독이 성공적으로 ${action}되었습니다.`);
                    loadSubscriptions(); // 목록 새로고침
                } else {
                    alert(`오류: ${result.message}`);
                }
            } catch (error) {
                console.error('구독 상태 변경 오류:', error);
                alert('서버와 연결할 수 없습니다.');
            }
        }

        // 오류 표시
        function showError(message) {
            const tbody = document.getElementById('subscriptions-table-body');
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-red-500">
                        ❌ ${message}
                    </td>
                </tr>
            `;
        }

        // 편집 모달 (추후 구현)
        function editSubscription(subscriptionId) {
            alert('구독 편집 기능은 추후 구현 예정입니다.');
        }

        // 페이지 로드시 구독 데이터 로드
        document.addEventListener('DOMContentLoaded', function() {
            loadSubscriptions();
        });
    </script>
</body>
</html>