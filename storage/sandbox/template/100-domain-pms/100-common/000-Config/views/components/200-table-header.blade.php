<!-- 공통 테이블 헤더 컴포넌트 -->
<?php
// 기본값 설정
$sortBy = $sortBy ?? '';
$sortOrder = $sortOrder ?? 'asc';
$columns = $columns ?? [];

// URL 파라미터 유지
$queryParams = array_filter([
    'search' => $search ?? '',
    'status' => $status ?? '',
    'priority' => $priority ?? '',
    'page' => $page ?? 1
]);

/**
 * 정렬 URL 생성
 */
function getSortUrl($column, $currentSortBy, $currentSortOrder, $queryParams) {
    $newOrder = ($currentSortBy === $column && $currentSortOrder === 'asc') ? 'desc' : 'asc';
    $params = array_merge($queryParams, [
        'sort' => $column,
        'order' => $newOrder
    ]);
    return '?' . http_build_query($params);
}

/**
 * 정렬 아이콘 반환
 */
function getSortIcon($column, $currentSortBy, $currentSortOrder) {
    if ($currentSortBy !== $column) {
        return '↕️'; // 정렬 안됨
    }
    return $currentSortOrder === 'asc' ? '↑' : '↓';
}
?>

<thead class="bg-gray-50">
    <tr>
        <!-- 체크박스 컬럼 -->
        <?php if(isset($showCheckbox) && $showCheckbox): ?>
            <th class="px-6 py-3 text-left">
                <input type="checkbox" 
                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                       @change="toggleAll($event.target.checked)">
            </th>
        <?php endif; ?>
        
        <!-- 동적 컬럼들 -->
        <?php foreach($columns as $column): ?>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <?php if($column['sortable'] ?? false): ?>
                    <a href="<?= getSortUrl($column['key'], $sortBy, $sortOrder, $queryParams) ?>" 
                       class="group inline-flex items-center hover:text-gray-700">
                        <?= htmlspecialchars($column['label']) ?>
                        <span class="ml-1 text-gray-400 group-hover:text-gray-600">
                            <?= getSortIcon($column['key'], $sortBy, $sortOrder) ?>
                        </span>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($column['label']) ?>
                <?php endif; ?>
            </th>
        <?php endforeach; ?>
        
        <!-- 액션 컬럼 -->
        <?php if(isset($showActions) && $showActions): ?>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                액션
            </th>
        <?php endif; ?>
    </tr>
</thead>