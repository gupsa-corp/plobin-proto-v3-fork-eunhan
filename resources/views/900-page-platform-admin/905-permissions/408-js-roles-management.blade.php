{{-- 역할 데이터 관리 JavaScript --}}
<script>
// 역할 데이터 로드 (계층 정보 포함)
function loadRolesData() {
    fetch('/api/core/permissions')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hierarchicalRolesData = data.data.roles;
            renderRolesTable(hierarchicalRolesData);
            updateRoleStats(data.data);
        } else {
            showError('역할 데이터 로드에 실패했습니다.');
        }
        hideRolesLoading();
    })
    .catch(error => {
        console.error('Failed to load roles data:', error);
        showError('역할 데이터 로드 중 오류가 발생했습니다.');
        hideRolesLoading();
    });
}

// 역할 테이블 렌더링
function renderRolesTable(roles) {
    const tbody = document.getElementById('roles-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!roles || roles.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                    등록된 역할이 없습니다.
                </td>
            </tr>
        `;
        return;
    }
    
    roles.forEach(role => {
        const row = createRoleRow(role);
        tbody.appendChild(row);
    });
}

// 역할 행 생성
function createRoleRow(role) {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50';
    
    // 계층 레벨별 아이콘과 색상
    const scopeLevelConfig = {
        'platform': { 
            icon: '🏢', 
            color: 'red', 
            text: '플랫폼', 
            bgClass: 'bg-red-100', 
            textClass: 'text-red-800' 
        },
        'organization': { 
            icon: '🏢', 
            color: 'blue', 
            text: '조직', 
            bgClass: 'bg-blue-100', 
            textClass: 'text-blue-800' 
        },
        'project': { 
            icon: '📁', 
            color: 'green', 
            text: '프로젝트', 
            bgClass: 'bg-green-100', 
            textClass: 'text-green-800' 
        },
        'page': { 
            icon: '📄', 
            color: 'purple', 
            text: '페이지', 
            bgClass: 'bg-purple-100', 
            textClass: 'text-purple-800' 
        }
    };
    
    const config = scopeLevelConfig[role.scope_level] || scopeLevelConfig.platform;
    
    // 소속/부모 정보 구성
    let belongsToInfo = '';
    if (role.parent_role) {
        belongsToInfo = `
            <div class="text-xs text-gray-500">
                부모: <span class="font-medium">${role.parent_role.name}</span>
            </div>
        `;
    } else if (role.organization) {
        belongsToInfo = `
            <div class="text-xs text-gray-500">
                조직: <span class="font-medium">${role.organization.name}</span>
            </div>
        `;
    } else if (role.hierarchy_path !== '플랫폼') {
        belongsToInfo = `
            <div class="text-xs text-gray-500">
                ${role.hierarchy_path}
            </div>
        `;
    }
    
    tr.innerHTML = `
        <!-- 역할명 -->
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full ${config.bgClass} flex items-center justify-center">
                        <span class="text-sm">${config.icon}</span>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">${role.name}</div>
                    <div class="text-sm text-gray-500">ID: ${role.id}</div>
                </div>
            </div>
        </td>
        
        <!-- 계층 레벨 -->
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.bgClass} ${config.textClass}">
                ${config.icon} ${config.text}
            </span>
        </td>
        
        <!-- 소속/부모 -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            ${belongsToInfo || '-'}
        </td>
        
        <!-- 설명 -->
        <td class="px-6 py-4 text-sm text-gray-500">
            <div class="max-w-xs truncate" title="${role.description || ''}">
                ${role.description || '-'}
            </div>
        </td>
        
        <!-- 권한 수 -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                ${role.permissions ? role.permissions.length : 0}개
            </span>
        </td>
        
        <!-- 생성자 -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            ${role.creator ? `
                <div class="text-sm text-gray-900">${role.creator.name}</div>
                <div class="text-xs text-gray-500">${role.creator.email}</div>
            ` : '-'}
        </td>
        
        <!-- 생성일 -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            ${role.created_at ? new Date(role.created_at).toLocaleDateString('ko-KR') : '-'}
        </td>
        
        <!-- 상태 -->
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                role.is_active 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-gray-100 text-gray-800'
            }">
                ${role.is_active ? '활성' : '비활성'}
            </span>
        </td>
        
        <!-- 작업 -->
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center justify-end space-x-2">
                <button onclick="viewRoleDetails(${role.id})" 
                        class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">
                    상세
                </button>
                <button onclick="editRole(${role.id})" 
                        class="text-blue-600 hover:text-blue-900 text-xs font-medium">
                    편집
                </button>
                ${role.children_count > 0 ? 
                    `<span class="text-gray-400 text-xs">자식 역할 있음</span>` :
                    `<button onclick="deleteRole(${role.id})" 
                             class="text-red-600 hover:text-red-900 text-xs font-medium">
                        삭제
                    </button>`
                }
            </div>
        </td>
    `;
    
    return tr;
}

// 역할 통계 업데이트
function updateRoleStats(data) {
    // 통계 정보가 있다면 업데이트
    if (data.scope_statistics) {
        console.log('Role Statistics:', data.scope_statistics);
    }
}

// 로딩 숨김
function hideRolesLoading() {
    const loadingRow = document.getElementById('loading-row');
    if (loadingRow) {
        loadingRow.style.display = 'none';
    }
}

// 역할 상세 보기
function viewRoleDetails(roleId) {
    const role = hierarchicalRolesData.find(r => r.id === roleId);
    if (!role) return;
    
    // 모달이나 상세 페이지로 이동
    console.log('Role Details:', role);
    alert(`역할 상세 정보:\n\n이름: ${role.name}\n레벨: ${role.scope_level}\n설명: ${role.description || '없음'}\n권한 수: ${role.permissions ? role.permissions.length : 0}\n계층 경로: ${role.hierarchy_path}`);
}

// 역할 편집
function editRole(roleId) {
    console.log('Edit role:', roleId);
    alert('역할 편집 기능은 준비 중입니다.');
}

// 역할 삭제
function deleteRole(roleId) {
    if (!confirm('정말로 이 역할을 삭제하시겠습니까?')) return;
    
    fetch(`/api/platform/admin/roles/${roleId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('역할이 삭제되었습니다.');
            loadRolesData(); // 데이터 새로고침
        } else {
            showError('역할 삭제에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Delete role failed:', error);
        showError('역할 삭제 중 오류가 발생했습니다.');
    });
}
</script>