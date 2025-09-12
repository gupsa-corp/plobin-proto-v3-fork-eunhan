{{-- 샌드박스 테이블 뷰 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();

    // SQLite 데이터베이스 연결
    $dbPath = storage_path('sandbox/storage-sandbox-template/backend/database/release.sqlite');
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 검색 및 필터 파라미터 처리
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $sortBy = $_GET['sort'] ?? 'created_date';
        $sortOrder = $_GET['order'] ?? 'desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // WHERE 조건 구성
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(name LIKE :search OR description LIKE :search OR client LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($status)) {
            $whereConditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($priority)) {
            $whereConditions[] = "priority = :priority";
            $params[':priority'] = $priority;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // 정렬 컬럼 검증
        $allowedSortColumns = ['name', 'status', 'priority', 'created_date', 'progress', 'start_date', 'end_date'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_date';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'ASC' : 'DESC';
        
        // 전체 개수 조회
        $countSql = "SELECT COUNT(*) FROM projects $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalProjects = $countStmt->fetchColumn();
        
        // 프로젝트 데이터 조회
        $sql = "SELECT 
                    id, name, description, status, progress, team_members, priority, 
                    start_date, end_date, client, category, budget, 
                    created_date, estimated_hours, actual_hours
                FROM projects 
                $whereClause 
                ORDER BY $sortBy $sortOrder 
                LIMIT :limit OFFSET :offset";
                
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $projectsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 페이지네이션 계산
        $totalPages = ceil($totalProjects / $perPage);
        
        // 통계 데이터 조회
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority,
                AVG(progress) as avg_progress
            FROM projects
        ");
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "데이터베이스 연결 오류: " . $e->getMessage();
        $projectsData = [];
        $totalProjects = 0;
        $totalPages = 1;
        $stats = ['total' => 0, 'in_progress' => 0, 'completed' => 0, 'high_priority' => 0, 'avg_progress' => 0];
    }
?><div class="min-h-screen bg-gray-50 p-6">
    {{-- 에러 메시지 표시 --}}
    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ $error }}
        </div>
    @endif

    {{-- 헤더 및 통계 --}}
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
            <div class="flex space-x-2">
                <a href="?view=gantt" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">간트차트</a>
                <button class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">내보내기</button>
            </div>
        </div>
        
        {{-- 통계 카드 --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-blue-600">전체 프로젝트</div>
                <div class="text-2xl font-bold text-blue-800">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-green-600">진행 중</div>
                <div class="text-2xl font-bold text-green-800">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-purple-600">완료</div>
                <div class="text-2xl font-bold text-purple-800">{{ $stats['completed'] }}</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-sm text-orange-600">평균 진행률</div>
                <div class="text-2xl font-bold text-orange-800">{{ number_format($stats['avg_progress'], 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- 필터 바 --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ htmlspecialchars($search) }}" 
                       placeholder="프로젝트명, 클라이언트 검색..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">모든 상태</option>
                <option value="planned" {{ $status === 'planned' ? 'selected' : '' }}>계획</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>진행 중</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>완료</option>
                <option value="on_hold" {{ $status === 'on_hold' ? 'selected' : '' }}>보류</option>
            </select>
            <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">모든 우선순위</option>
                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>높음</option>
                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>보통</option>
                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>낮음</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">검색</button>
            @if(!empty($search) || !empty($status) || !empty($priority))
                <a href="?" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">초기화</a>
            @endif
        </form>
    </div>

    {{-- 테이블 --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="?sort=name&order={{ $sortBy === 'name' && $sortOrder === 'ASC' ? 'desc' : 'asc' }}&{{ http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) }}" class="hover:text-gray-900">
                                프로젝트명 {{ $sortBy === 'name' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' }}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="?sort=progress&order={{ $sortBy === 'progress' && $sortOrder === 'DESC' ? 'asc' : 'desc' }}&{{ http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) }}" class="hover:text-gray-900">
                                진행률 {{ $sortBy === 'progress' ? ($sortOrder === 'DESC' ? '↓' : '↑') : '↕️' }}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">팀 멤버</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">시작일</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="?sort=priority&order={{ $sortBy === 'priority' && $sortOrder === 'ASC' ? 'desc' : 'asc' }}&{{ http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority])) }}" class="hover:text-gray-900">
                                우선순위 {{ $sortBy === 'priority' ? ($sortOrder === 'ASC' ? '↑' : '↓') : '↕️' }}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">클라이언트</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
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
                    @endphp
                    
                    @if(empty($projectsData))
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                검색 결과가 없습니다.
                            </td>
                        </tr>
                    @else
                        @foreach($projectsData as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="rounded" value="{{ $project['id'] }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-gray-600 text-sm">{{ $project['id'] }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ htmlspecialchars($project['name']) }}</div>
                                            <div class="text-sm text-gray-500">{{ htmlspecialchars(substr($project['description'], 0, 100)) }}{{ strlen($project['description']) > 100 ? '...' : '' }}</div>
                                            @if(!empty($project['category']))
                                                <div class="text-xs text-blue-600 mt-1">#{{ $project['category'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$project['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$project['status']] ?? $project['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $project['progress'] }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $project['progress'] }}%</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full mr-2"></div>
                                        <div class="text-sm text-gray-900">{{ $project['team_members'] }}명</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($project['start_date'])
                                        {{ date('Y-m-d', strtotime($project['start_date'])) }}
                                        @if($project['end_date'])
                                            <br><small>~ {{ date('Y-m-d', strtotime($project['end_date'])) }}</small>
                                        @endif
                                    @else
                                        {{ $project['created_date'] }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColors[$project['priority']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $priorityLabels[$project['priority']] ?? $project['priority'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ htmlspecialchars($project['client']) ?: '-' }}
                                    @if($project['budget'] > 0)
                                        <br><small class="text-green-600">₩{{ number_format($project['budget']) }}</small>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900" onclick="alert('프로젝트 ID: {{ $project['id'] }}')">상세</button>
                                        <button class="text-green-600 hover:text-green-900">편집</button>
                                        <button class="text-red-600 hover:text-red-900" onclick="confirm('정말 삭제하시겠습니까?')">삭제</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        
        {{-- 페이지네이션 --}}
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    총 <span class="font-medium">{{ $totalProjects }}</span>개 중 
                    <span class="font-medium">{{ $totalProjects > 0 ? (($page - 1) * $perPage + 1) : 0 }}-{{ min($page * $perPage, $totalProjects) }}</span> 표시
                </div>
                <div class="flex space-x-2">
                    @if($page > 1)
                        <a href="?page={{ $page - 1 }}&{{ http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority, 'sort' => $sortBy, 'order' => strtolower($sortOrder)])) }}" 
                           class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">이전</a>
                    @endif
                    
                    @for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++)
                        <a href="?page={{ $i }}&{{ http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority, 'sort' => $sortBy, 'order' => strtolower($sortOrder)])) }}" 
                           class="px-3 py-2 text-sm {{ $i === $page ? 'bg-purple-600 text-white' : 'border border-gray-300 hover:bg-gray-50' }} rounded-lg">{{ $i }}</a>
                    @endfor
                    
                    @if($page < $totalPages)
                        <a href="?page={{ $page + 1 }}&{{ http_build_query(array_filter(['search' => $search, 'status' => $status, 'priority' => $priority, 'sort' => $sortBy, 'order' => strtolower($sortOrder)])) }}" 
                           class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">다음</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>