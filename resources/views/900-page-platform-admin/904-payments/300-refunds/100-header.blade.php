{{-- 환불 관리 헤더 --}}
<div class="bg-white shadow-sm border-b border-gray-200" style="padding: 24px;">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="mr-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <span class="text-xl">🔄</span>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">환불 관리</h1>
                <p class="mt-1 text-sm text-gray-600">결제 환불 요청을 처리하고 관리합니다</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            {{-- 통계 요약 --}}
            <div class="bg-red-50 rounded-lg px-4 py-3">
                <div class="text-sm text-red-600">이번 달 환불</div>
                <div class="text-lg font-bold text-red-700">{{ $refunds->total() }}건</div>
            </div>
            
            {{-- 사용자 정보 --}}
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-medium text-gray-700">G</span>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-900">게스트 사용자</div>
                    <div class="text-xs text-gray-500">guest@example.com</div>
                </div>
            </div>
        </div>
    </div>
</div>