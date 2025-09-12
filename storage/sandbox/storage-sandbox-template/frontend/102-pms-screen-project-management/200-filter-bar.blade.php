<!-- 필터 바 -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-64">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="프로젝트명, 클라이언트 검색..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">모든 상태</option>
            <option value="planned" <?= $status === 'planned' ? 'selected' : '' ?>>계획</option>
            <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>진행 중</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>완료</option>
            <option value="on_hold" <?= $status === 'on_hold' ? 'selected' : '' ?>>보류</option>
        </select>
        <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">모든 우선순위</option>
            <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>높음</option>
            <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>보통</option>
            <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>낮음</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">검색</button>
        <?php if(!empty($search) || !empty($status) || !empty($priority)): ?>
            <a href="?" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">초기화</a>
        <?php endif; ?>
    </form>
    
    <!-- 컬럼 선택 드롭다운 -->
    <div class="flex-shrink-0">
        <?php include '201-column-selector.blade.php'; ?>
    </div>
</div>