<!-- 새 프로젝트 생성 모달 -->
<div id="createProjectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">새 프로젝트 생성</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>
            <form id="createProjectForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">프로젝트명</label>
                    <input type="text" id="projectName" name="name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">설명</label>
                    <textarea id="projectDescription" name="description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">클라이언트</label>
                    <input type="text" id="projectClient" name="client"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">상태</label>
                        <select id="projectStatus" name="status"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="planned">계획</option>
                            <option value="in_progress">진행 중</option>
                            <option value="completed">완료</option>
                            <option value="on_hold">보류</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">우선순위</label>
                        <select id="projectPriority" name="priority"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="low">낮음</option>
                            <option value="medium">보통</option>
                            <option value="high">높음</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">진행률 (%)</label>
                        <input type="number" id="projectProgress" name="progress" min="0" max="100" value="0"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">팀원 수</label>
                        <input type="number" id="projectTeamMembers" name="team_members" min="1" value="1"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">예산</label>
                    <input type="number" id="projectBudget" name="budget" min="0" step="1000"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeCreateModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm hover:bg-gray-200">
                        취소
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700">
                        생성
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 컬럼 관리 모달 -->
<div id="columnManagerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">컬럼 관리</h3>
                <button onclick="closeColumnManager()" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <!-- 탭 메뉴 -->
            <div class="mb-4">
                <nav class="flex space-x-1" aria-label="Tabs">
                    <button onclick="switchColumnTab('custom')" id="customTab" class="tab-button px-3 py-2 text-sm font-medium rounded-md bg-blue-100 text-blue-700">
                        사용자 컬럼
                    </button>
                    <button onclick="switchColumnTab('system')" id="systemTab" class="tab-button px-3 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700">
                        기본 컬럼
                    </button>
                </nav>
            </div>

            <!-- 컬럼 추가 버튼 (사용자 컬럼 탭에서만 표시) -->
            <div class="mb-4" id="addColumnButton">
                <button onclick="openAddColumnModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    + 새 컬럼 추가
                </button>
            </div>

            <!-- 컬럼 목록 -->
            <div id="columnsList" class="space-y-2">
                <!-- 동적으로 채워질 컬럼들 -->
            </div>

            <!-- 기본 컬럼 관리 설명 -->
            <div id="systemColumnInfo" class="hidden mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="text-yellow-400">⚠️</span>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">기본 컬럼 관리</h4>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>기본 컬럼들은 시스템에서 필수적으로 사용되는 컬럼으로, 삭제할 수 없습니다.</p>
                            <p>활성/비활성 상태만 변경할 수 있습니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 컬럼 추가 모달 -->
<div id="addColumnModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">새 컬럼 추가</h3>
                <button onclick="closeAddColumnModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>
            <form id="addColumnForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">컬럼명</label>
                    <input type="text" id="columnName" name="column_name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                           placeholder="예: custom_field_1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">표시 이름</label>
                    <input type="text" id="columnLabel" name="column_label" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                           placeholder="예: 커스텀 필드">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">데이터 타입</label>
                    <select id="columnType" name="column_type" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="TEXT">텍스트</option>
                        <option value="INTEGER">숫자</option>
                        <option value="DECIMAL">소수</option>
                        <option value="DATE">날짜</option>
                        <option value="BOOLEAN">예/아니오</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">표시 타입</label>
                    <select id="displayType" name="display_type"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="input">입력창</option>
                        <option value="textarea">텍스트 영역</option>
                        <option value="select">드롭다운</option>
                        <option value="checkbox">체크박스</option>
                        <option value="date">날짜 선택</option>
                        <option value="number">숫자 입력</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="isRequired" name="is_required" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="isRequired" class="ml-2 block text-sm text-gray-900">필수 항목</label>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddColumnModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm hover:bg-gray-200">
                        취소
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        추가
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 컬럼 관리 관련 함수 -->
<script>
// 새 프로젝트 생성 모달 열기
function openCreateModal() {
    document.getElementById('createProjectModal').classList.remove('hidden');
}

// 새 프로젝트 생성 모달 닫기
function closeCreateModal() {
    document.getElementById('createProjectModal').classList.add('hidden');
    document.getElementById('createProjectForm').reset();
}

// 새 프로젝트 생성
async function createProject(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const projectData = {
        name: formData.get('name'),
        description: formData.get('description'),
        status: formData.get('status'),
        progress: parseInt(formData.get('progress')) || 0,
        team_members: parseInt(formData.get('team_members')) || 1,
        priority: formData.get('priority'),
        client: formData.get('client'),
        budget: parseInt(formData.get('budget')) || 0
    };

    try {
        const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(projectData)
        });

        const result = await response.json();

        if (result.success) {
            closeCreateModal();
            window.location.reload();
        } else {
            alert('프로젝트 생성 실패: ' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('생성 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    }
}

// 폼 제출 이벤트 리스너
document.getElementById('createProjectForm').addEventListener('submit', createProject);

// 컬럼 관리 모달 열기
function openColumnManager() {
    // 기본적으로 사용자 컬럼 탭 선택
    switchColumnTab('custom');
    document.getElementById('columnManagerModal').classList.remove('hidden');
}

// 컬럼 관리 모달 닫기
function closeColumnManager() {
    document.getElementById('columnManagerModal').classList.add('hidden');
}

// 탭 전환 함수
function switchColumnTab(tabType) {
    // 탭 버튼 스타일 업데이트
    document.getElementById('customTab').className = tabType === 'custom'
        ? 'tab-button px-3 py-2 text-sm font-medium rounded-md bg-blue-100 text-blue-700'
        : 'tab-button px-3 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700';

    document.getElementById('systemTab').className = tabType === 'system'
        ? 'tab-button px-3 py-2 text-sm font-medium rounded-md bg-blue-100 text-blue-700'
        : 'tab-button px-3 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700';

    // 컨텐츠 표시/숨김
    const addButton = document.getElementById('addColumnButton');
    const systemInfo = document.getElementById('systemColumnInfo');

    if (tabType === 'custom') {
        addButton.classList.remove('hidden');
        systemInfo.classList.add('hidden');
        loadColumns('custom'); // 사용자 컬럼만 로드
    } else {
        addButton.classList.add('hidden');
        systemInfo.classList.remove('hidden');
        loadColumns('system'); // 기본 컬럼만 로드
    }
}

// 컬럼 추가 모달 열기
function openAddColumnModal() {
    document.getElementById('addColumnModal').classList.remove('hidden');
}

// 컬럼 추가 모달 닫기
function closeAddColumnModal() {
    document.getElementById('addColumnModal').classList.add('hidden');
    document.getElementById('addColumnForm').reset();
}

// 컬럼 목록 로드
async function loadColumns(tabType = 'all') {
    try {
        let url = '/api/sandbox/storage-sandbox-template/backend/api.php/columns';
        if (tabType !== 'all') {
            url += '?type=' + tabType;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            displayColumns(result.data, tabType);
        } else {
            alert('컬럼 목록 로드 실패: ' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('컬럼 로드 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    }
}

// 컬럼 목록 표시
function displayColumns(columns, tabType = 'all') {
    const container = document.getElementById('columnsList');
    container.innerHTML = '';

    if (columns.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <p>${tabType === 'custom' ? '사용자 정의 컬럼이 없습니다.' : tabType === 'system' ? '기본 컬럼이 없습니다.' : '컬럼이 없습니다.'}</p>
            </div>
        `;
        return;
    }

    columns.forEach(column => {
        const columnItem = document.createElement('div');
        const isSystemColumn = column.is_system === 1 || column.is_system === true;

        columnItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
        columnItem.innerHTML = `
            <div class="flex-1">
                <div class="font-medium text-gray-900 flex items-center">
                    ${column.column_label}
                    ${isSystemColumn ? '<span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">시스템</span>' : ''}
                </div>
                <div class="text-sm text-gray-500">${column.column_name} (${column.column_type})</div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs px-2 py-1 rounded ${column.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${column.is_active ? '활성' : '비활성'}
                </span>
                <button onclick="toggleColumn(${column.id}, ${!column.is_active})"
                        class="text-blue-600 hover:text-blue-800 text-sm">
                    ${column.is_active ? '비활성' : '활성'}
                </button>
                ${!isSystemColumn ?
                    `<button onclick="deleteColumn(${column.id}, '${column.column_name}')"
                            class="text-red-600 hover:text-red-800 text-sm">
                        삭제
                    </button>` :
                    '<span class="text-xs text-gray-400">삭제불가</span>'
                }
            </div>
        `;
        container.appendChild(columnItem);
    });
}

// 새 컬럼 추가
async function addColumn(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const columnData = {
        column_name: formData.get('column_name'),
        column_label: formData.get('column_label'),
        column_type: formData.get('column_type'),
        display_type: formData.get('display_type') || 'input',
        is_required: formData.get('is_required') ? 1 : 0,
        is_active: 1
    };

    try {
        const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/columns', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(columnData)
        });

        const result = await response.json();

        if (result.success) {
            closeAddColumnModal();
            loadColumns();
        } else {
            alert('컬럼 추가 실패: ' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('컬럼 추가 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    }
}

// 컬럼 활성/비활성 토글
async function toggleColumn(columnId, isActive) {
    try {
        const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/columns/' + columnId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ is_active: isActive ? 1 : 0 })
        });

        const result = await response.json();

        if (result.success) {
            loadColumns();
        } else {
            alert('컬럼 상태 변경 실패: ' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('컬럼 상태 변경 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    }
}

// 컬럼 삭제
async function deleteColumn(columnId, columnName) {
    if (!confirm(`"${columnName}" 컬럼을 정말 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.`)) {
        return;
    }

    try {
        const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/columns/' + columnId, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            loadColumns();
        } else {
            alert('컬럼 삭제 실패: ' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('컬럼 삭제 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    }
}

// 컬럼 추가 폼 이벤트 리스너
document.getElementById('addColumnForm').addEventListener('submit', addColumn);
</script>

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

        // 동적 필드들 추가 (custom_로 시작하는 속성들)
        const customData = {};
        Object.keys(selectedProject).forEach(key => {
            if (key.startsWith('custom_')) {
                const columnName = key.replace('custom_', '');
                customData[columnName] = selectedProject[key];
            }
        });

        if (Object.keys(customData).length > 0) {
            updateData.custom_data = customData;
        }

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

// 프로젝트 삭제 함수
window.deleteProject = async function(projectId, projectName) {
    if (!confirm(`"${projectName}" 프로젝트를 정말 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.`)) {
        return;
    }

    try {
        const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects/' + projectId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            // 페이지 새로고침으로 업데이트된 데이터 표시
            window.location.reload();
        } else {
            alert('프로젝트 삭제 실패: ' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        console.error('삭제 에러:', error);
        alert('네트워크 오류가 발생했습니다: ' + error.message);
    }
};
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
