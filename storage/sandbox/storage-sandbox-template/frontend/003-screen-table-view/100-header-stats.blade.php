<!-- 헤더 및 통계 -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-purple-600">🗂️</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">프로젝트 테이블 뷰</h1>
                <p class="text-gray-600">실제 데이터베이스 연동으로 프로젝트를 체계적으로 관리하세요</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="openCreateModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                <span>+</span>
                <span>새 프로젝트 생성</span>
            </button>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600">전체 프로젝트</div>
            <div class="text-2xl font-bold text-blue-800"><?= $stats['total'] ?? 0 ?></div>
        </div>
        <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600">진행 중</div>
            <div class="text-2xl font-bold text-green-800"><?= $stats['in_progress'] ?? 0 ?></div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600">완료</div>
            <div class="text-2xl font-bold text-purple-800"><?= $stats['completed'] ?? 0 ?></div>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-orange-600">평균 진행률</div>
            <div class="text-2xl font-bold text-orange-800"><?= number_format($stats['avg_progress'] ?? 0, 1) ?>%</div>
        </div>
    </div>
</div>
