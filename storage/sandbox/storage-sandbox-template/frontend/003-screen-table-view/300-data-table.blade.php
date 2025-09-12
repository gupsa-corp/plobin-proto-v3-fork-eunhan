<!-- 테이블 -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
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
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                    $statusColors = [
                        'planned' => 'bg-purple-100 text-purple-800',
                        'in_progress' => 'bg-blue-100 text-blue-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'on_hold' => 'bg-yellow-100 text-yellow-800'
                    ];
                    
                    $statusLabels = [
                        'planned' => '계획',
                        'in_progress' => '진행 중',
                        'completed' => '완료',
                        'on_hold' => '보류'
                    ];
                    
                    $priorityColors = [
                        'high' => 'bg-red-100 text-red-800',
                        'medium' => 'bg-yellow-100 text-yellow-800',
                        'low' => 'bg-green-100 text-green-800'
                    ];
                    
                    $priorityLabels = [
                        'high' => '높음',
                        'medium' => '보통',
                        'low' => '낮음'
                    ];
                ?>
                
                <?php if(empty($projectsData)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            검색 결과가 없습니다.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($projectsData as $project): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded" value="<?= $project['id'] ?>">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-gray-600 text-sm"><?= $project['id'] ?></span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($project['name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($project['description'], 0, 100)) ?><?= strlen($project['description']) > 100 ? '...' : '' ?></div>
                                        <?php if(!empty($project['category'])): ?>
                                            <div class="text-xs text-blue-600 mt-1">#<?= $project['category'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusColors[$project['status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <?= $statusLabels[$project['status']] ?? $project['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $project['progress'] ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?= $project['progress'] ?>%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full mr-2"></div>
                                    <div class="text-sm text-gray-900"><?= $project['team_members'] ?>명</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($project['start_date']): ?>
                                    <?= date('Y-m-d', strtotime($project['start_date'])) ?>
                                    <?php if($project['end_date']): ?>
                                        <br><small>~ <?= date('Y-m-d', strtotime($project['end_date'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= $project['created_date'] ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $priorityColors[$project['priority']] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <?= $priorityLabels[$project['priority']] ?? $project['priority'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($project['client']) ?: '-' ?>
                                <?php if($project['budget'] > 0): ?>
                                    <br><small class="text-green-600">₩<?= number_format($project['budget']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900" onclick="alert('프로젝트 ID: <?= $project['id'] ?>')">상세</button>
                                    <button class="text-green-600 hover:text-green-900">편집</button>
                                    <button class="text-red-600 hover:text-red-900" onclick="confirm('정말 삭제하시겠습니까?')">삭제</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
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
</div>