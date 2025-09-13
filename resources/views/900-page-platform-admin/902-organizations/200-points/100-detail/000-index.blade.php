<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '조직 포인트 상세'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.902-organizations.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            <div class="p-6">
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $organization->name }} - 포인트 상세</h1>
                <p class="text-sm text-gray-600 mt-1">조직 ID: {{ $organization->id }}</p>
                <p class="text-sm text-gray-600">{{ $organization->description }}</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600">
                    {{ number_format($organization->points_balance) }}P
                </div>
                <div class="text-sm text-gray-500">현재 잔액</div>
            </div>
        </div>
    </div>

    <!-- 포인트 통계 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">총 적립</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($organization->pointAccount->lifetime_earned ?? 0) }}P</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">총 사용</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($organization->pointAccount->lifetime_spent ?? 0) }}P</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">거래 내역</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transactions->total() }}건</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 포인트 조정 -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">포인트 조정</h2>
        <form action="{{ route('platform.admin.organizations.points.adjust', $organization) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">조정 금액</label>
                    <input type="number" id="amount" name="amount" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                           placeholder="양수는 적립, 음수는 차감" required>
                    <p class="text-xs text-gray-500 mt-1">예: 1000 (적립), -500 (차감)</p>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">사유</label>
                    <input type="text" id="description" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                           placeholder="조정 사유를 입력하세요" required>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        포인트 조정
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 거래 내역 -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">거래 내역</h2>
            <div class="text-sm text-gray-500">
                총 {{ $transactions->total() }}건
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">날짜</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">타입</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">금액</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">잔액</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">설명</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">처리자</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $transaction->created_at->format('Y.m.d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($transaction->transaction_type === 'earn') bg-green-100 text-green-800
                                @elseif($transaction->transaction_type === 'spend') bg-red-100 text-red-800
                                @elseif($transaction->transaction_type === 'refund') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $transaction->getTransactionTypeText() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                            @if($transaction->amount > 0) text-green-600 @else text-red-600 @endif">
                            {{ $transaction->getFormattedAmount() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($transaction->balance_after) }}P
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $transaction->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->processedBy?->name ?? '시스템' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            거래 내역이 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        @if($transactions->hasPages())
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- 뒤로가기 -->
    <div class="flex justify-start">
        <a href="{{ route('platform.admin.organizations.points') }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
            ← 포인트 관리로 돌아가기
        </a>
    </div>
</div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>