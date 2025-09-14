<!-- 공통 페이지네이션 컴포넌트 -->
<?php
// 기본값 설정
$page = $page ?? 1;
$totalItems = $totalItems ?? 0;
$perPage = $perPage ?? 10;
$totalPages = $totalPages ?? (int) ceil($totalItems / $perPage);

// URL 파라미터 유지
$queryParams = array_filter([
    'search' => $search ?? '',
    'status' => $status ?? '',
    'priority' => $priority ?? '',
    'sort' => $sortBy ?? '',
    'order' => isset($sortOrder) ? strtolower($sortOrder) : ''
]);
?>

<div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
    <div class="flex items-center justify-between">
        <!-- 항목 정보 표시 -->
        <div class="text-sm text-gray-700">
            총 <span class="font-medium"><?= $totalItems ?></span>개 중 
            <span class="font-medium"><?= $totalItems > 0 ? (($page - 1) * $perPage + 1) : 0 ?>-<?= min($page * $perPage, $totalItems) ?></span> 표시
        </div>
        
        <!-- 페이지 네비게이션 -->
        <div class="flex space-x-2">
            <!-- 이전 버튼 -->
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&<?= http_build_query($queryParams) ?>" 
                   class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">이전</a>
            <?php endif; ?>
            
            <!-- 페이지 번호들 -->
            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>&<?= http_build_query($queryParams) ?>" 
                   class="px-3 py-2 text-sm <?= $i === $page ? 'bg-purple-600 text-white' : 'border border-gray-300 hover:bg-gray-50' ?> rounded-lg"><?= $i ?></a>
            <?php endfor; ?>
            
            <!-- 다음 버튼 -->
            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&<?= http_build_query($queryParams) ?>" 
                   class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">다음</a>
            <?php endif; ?>
        </div>
    </div>
</div>