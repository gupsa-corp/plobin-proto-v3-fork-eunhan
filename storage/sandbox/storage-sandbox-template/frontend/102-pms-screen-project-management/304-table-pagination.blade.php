<!-- 페이지네이션 -->
<div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
            총 <span class="font-medium"><?= $totalProjects ?></span>개 중 
            <span class="font-medium"><?= $totalProjects > 0 ? (($page - 1) * $perPage + 1) : 0 ?>-<?= min($page * $perPage, $totalProjects) ?></span> 표시
        </div>
        <div class="flex space-x-2">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority, 'sort' => $sortBy, 'order' => strtolower($sortOrder)])) ?>" 
                   class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">이전</a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority, 'sort' => $sortBy, 'order' => strtolower($sortOrder)])) ?>" 
                   class="px-3 py-2 text-sm <?= $i === $page ? 'bg-purple-600 text-white' : 'border border-gray-300 hover:bg-gray-50' ?> rounded-lg"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority, 'sort' => $sortBy, 'order' => strtolower($sortOrder)])) ?>" 
                   class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">다음</a>
            <?php endif; ?>
        </div>
    </div>
</div>