<!-- 공통 필터 바 컴포넌트 -->
<?php
// 기본값 설정
$search = $search ?? '';
$status = $status ?? '';
$priority = $priority ?? '';
$statusOptions = $statusOptions ?? [
    '' => '모든 상태',
    'planned' => '계획',
    'in_progress' => '진행 중',
    'completed' => '완료',
    'on_hold' => '보류'
];
$priorityOptions = $priorityOptions ?? [
    '' => '모든 우선순위',
    'high' => '높음',
    'medium' => '보통',
    'low' => '낮음'
];
$searchPlaceholder = $searchPlaceholder ?? '검색...';
?>

<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-center">
        <!-- 검색 필드 -->
        <div class="flex-1 min-w-64">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
        </div>
        
        <!-- 상태 필터 -->
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
            <?php foreach($statusOptions as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- 우선순위 필터 -->
        <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
            <?php foreach($priorityOptions as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= $priority === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- 검색 버튼 -->
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
            검색
        </button>
        
        <!-- 초기화 버튼 -->
        <?php if(!empty($search) || !empty($status) || !empty($priority)): ?>
            <a href="?" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                초기화
            </a>
        <?php endif; ?>
    </form>
    
    <!-- 추가 컨트롤 슬롯 -->
    <?php if(isset($additionalControls)): ?>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <?= $additionalControls ?>
        </div>
    <?php endif; ?>
</div>