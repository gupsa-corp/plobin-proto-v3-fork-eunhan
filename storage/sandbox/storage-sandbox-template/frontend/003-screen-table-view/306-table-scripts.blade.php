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

<!-- 프로젝트 생성 관련 함수 -->
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
