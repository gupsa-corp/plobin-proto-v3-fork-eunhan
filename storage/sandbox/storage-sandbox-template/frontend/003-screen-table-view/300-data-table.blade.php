<!-- 테이블 -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden" 
     x-data="{
         sidebarOpen: false,
         selectedProject: null,
         async openSidebar(project) {
             // 먼저 사이드바 열고 로딩 상태 표시
             this.selectedProject = project;
             this.sidebarOpen = true;
             
             try {
                 // API에서 최신 프로젝트 데이터 가져오기
                 const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects/' + project.id);
                 const result = await response.json();
                 
                 if (result.success && result.data) {
                     // 최신 데이터로 업데이트
                     this.selectedProject = result.data;
                 }
             } catch (error) {
                 console.error('프로젝트 데이터 로딩 실패:', error);
                 // 에러가 발생해도 기존 데이터로 사이드바는 열림
             }
         },
         closeSidebar() {
             this.selectedProject = null;
             this.sidebarOpen = false;
         },
         updateProject() {
             if (!this.selectedProject) {
                 return;
             }
             window.saveProject(this.selectedProject, this);
         }
     }" x-init="window.projectTable = this">
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
                        <tr class="hover:bg-gray-50 cursor-pointer" 
                            @click="openSidebar({
                                id: <?= $project['id'] ?>,
                                name: '<?= addslashes($project['name']) ?>',
                                description: '<?= addslashes($project['description']) ?>',
                                status: '<?= $project['status'] ?>',
                                progress: <?= $project['progress'] ?>,
                                team_members: <?= $project['team_members'] ?>,
                                priority: '<?= $project['priority'] ?>',
                                client: '<?= addslashes($project['client']) ?>',
                                budget: <?= $project['budget'] ?>,
                                start_date: '<?= $project['start_date'] ?>',
                                end_date: '<?= $project['end_date'] ?>',
                                category: '<?= $project['category'] ?>'
                            })"
                            title="클릭하여 편집"
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" @click.stop>
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900" 
                                            @click="openSidebar({
                                                id: <?= $project['id'] ?>,
                                                name: '<?= addslashes($project['name']) ?>',
                                                description: '<?= addslashes($project['description']) ?>',
                                                status: '<?= $project['status'] ?>',
                                                progress: <?= $project['progress'] ?>,
                                                team_members: <?= $project['team_members'] ?>,
                                                priority: '<?= $project['priority'] ?>',
                                                client: '<?= addslashes($project['client']) ?>',
                                                budget: <?= $project['budget'] ?>,
                                                start_date: '<?= $project['start_date'] ?>',
                                                end_date: '<?= $project['end_date'] ?>',
                                                category: '<?= $project['category'] ?>'
                                            })">상세</button>
                                    <button class="text-green-600 hover:text-green-900"
                                            @click="openSidebar({
                                                id: <?= $project['id'] ?>,
                                                name: '<?= addslashes($project['name']) ?>',
                                                description: '<?= addslashes($project['description']) ?>',
                                                status: '<?= $project['status'] ?>',
                                                progress: <?= $project['progress'] ?>,
                                                team_members: <?= $project['team_members'] ?>,
                                                priority: '<?= $project['priority'] ?>',
                                                client: '<?= addslashes($project['client']) ?>',
                                                budget: <?= $project['budget'] ?>,
                                                start_date: '<?= $project['start_date'] ?>',
                                                end_date: '<?= $project['end_date'] ?>',
                                                category: '<?= $project['category'] ?>'
                                            })">편집</button>
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

    <!-- 우측 사이드바 -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-40" 
         @click="closeSidebar()"
         style="display: none;"></div>
    
    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-50 w-96 bg-white shadow-xl"
         style="display: none;">
        <div class="h-full flex flex-col" x-show="selectedProject">
            <!-- 사이드바 헤더 -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">프로젝트 편집</h3>
                    <button @click="closeSidebar()" 
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- 사이드바 콘텐츠 -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- 프로젝트 기본 정보 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">프로젝트명</label>
                    <input type="text" 
                           x-model="selectedProject.name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                    <textarea x-model="selectedProject.description"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                    <select x-model="selectedProject.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="planned">계획</option>
                        <option value="in_progress">진행 중</option>
                        <option value="completed">완료</option>
                        <option value="on_hold">보류</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">진행률 (%)</label>
                    <input type="range" 
                           x-model="selectedProject.progress"
                           min="0" max="100"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex justify-between text-sm text-gray-500 mt-1">
                        <span>0%</span>
                        <span x-text="selectedProject.progress + '%'" class="font-medium text-blue-600"></span>
                        <span>100%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">팀원 수</label>
                    <input type="number" 
                           x-model="selectedProject.team_members"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
                    <select x-model="selectedProject.priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="low">낮음</option>
                        <option value="medium">보통</option>
                        <option value="high">높음</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">클라이언트</label>
                    <input type="text" 
                           x-model="selectedProject.client"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">예산 (원)</label>
                    <input type="number" 
                           x-model="selectedProject.budget"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- 추가 정보 -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">프로젝트 정보</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">프로젝트 ID:</span>
                            <span class="text-gray-900" x-text="selectedProject.id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">카테고리:</span>
                            <span class="text-gray-900" x-text="selectedProject.category || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">시작일:</span>
                            <span class="text-gray-900" x-text="selectedProject.start_date || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">종료일:</span>
                            <span class="text-gray-900" x-text="selectedProject.end_date || '-'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 사이드바 푸터 -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex space-x-3">
                    <button @click="updateProject()" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                        저장
                    </button>
                    <button @click="closeSidebar()" 
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:ring-2 focus:ring-gray-500">
                        취소
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 프로젝트 저장 함수 -->
<script>
window.saveProject = async function(selectedProject, alpineComponent) {
    let originalText = '저장';
    let saveButton = null;
    
    try {
        // 로딩 상태 표시 - 저장 버튼 찾기
        saveButton = Array.from(document.querySelectorAll('button')).find(btn => btn.textContent.trim() === '저장');
        
        if (saveButton) {
            originalText = saveButton.textContent;
            saveButton.textContent = '저장 중...';
            saveButton.disabled = true;
        }
        
        // API 호출을 위한 데이터 준비
        const updateData = {
            name: selectedProject.name,
            description: selectedProject.description,
            status: selectedProject.status,
            progress: parseInt(selectedProject.progress),
            team_members: parseInt(selectedProject.team_members),
            priority: selectedProject.priority,
            client: selectedProject.client,
            budget: parseInt(selectedProject.budget)
        };
        
        // API 호출
        const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects/' + selectedProject.id, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(updateData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('프로젝트가 성공적으로 업데이트되었습니다: ' + selectedProject.name);
            alpineComponent.closeSidebar();
            // 페이지 새로고침으로 업데이트된 데이터 표시
            window.location.reload();
        } else {
            alert('업데이트 실패: ' + (result.message || '알 수 없는 오류'));
        }
        
    } catch (error) {
        console.error('업데이트 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    } finally {
        // 버튼 상태 복원
        if (saveButton) {
            saveButton.textContent = originalText;
            saveButton.disabled = false;
        }
    }
};
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>