<!-- 테이블 헤더 -->
<div class="overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input type="checkbox" class="rounded">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="id">
                    <a href="?sort=id&order=<?= $sortBy === 'id' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        ID <?= $sortBy === 'id' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' ?>
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="name">
                    <a href="?sort=name&order=<?= $sortBy === 'name' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        프로젝트명 <?= $sortBy === 'name' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' ?>
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="status">상태</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="progress">
                    <a href="?sort=progress&order=<?= $sortBy === 'progress' && $sortOrder === 'DESC' ? 'asc' : 'desc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        진행률 <?= $sortBy === 'progress' ? ($sortOrder === 'DESC' ? '↓' : '↑') : '↕️' ?>
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="team_members">팀 멤버</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="start_date">시작일</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="end_date">종료일</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="priority">
                    <a href="?sort=priority&order=<?= $sortBy === 'priority' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        우선순위 <?= $sortBy === 'priority' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' ?>
                    </a>
                </th>
                <?php foreach ($dynamicColumns ?? [] as $column): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="custom_<?= $column['column_name'] ?>">
                        <?= htmlspecialchars($column['column_label']) ?>
                        <?php if ($column['is_required']): ?>
                            <span class="text-red-500">*</span>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" data-column="client">클라이언트</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
            </tr>
        </thead>
