<!-- 테이블 헤더 -->
<div class="overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input type="checkbox" class="rounded">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="?sort=name&order=<?= $sortBy === 'name' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        프로젝트명 <?= $sortBy === 'name' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' ?>
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="?sort=progress&order=<?= $sortBy === 'progress' && $sortOrder === 'DESC' ? 'asc' : 'desc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        진행률 <?= $sortBy === 'progress' ? ($sortOrder === 'DESC' ? '↓' : '↑') : '↕️' ?>
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">팀 멤버</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">시작일</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="?sort=priority&order=<?= $sortBy === 'priority' && $sortOrder === 'ASC' ? 'desc' : 'asc' ?>&<?= http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) ?>" class="hover:text-gray-900">
                        우선순위 <?= $sortBy === 'priority' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' ?>
                    </a>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">클라이언트</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
            </tr>
        </thead>