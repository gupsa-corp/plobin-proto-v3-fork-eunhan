{{-- 권한 매트릭스 관리 JavaScript --}}
<script>
// 전역 변수
var permissionsData = {};
var rolesData = [];
let hierarchicalRolesData = [];
let isBulkMode = false;
let selectedPermissions = new Set();

// 초기 데이터 로드
function loadInitialData() {
    Promise.all([
        fetch('/api/platform/admin/permissions/matrix'),
        fetch('/api/platform/admin/permissions/stats')
    ])
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(([matrixData, statsData]) => {
        if (matrixData.success) {
            permissionsData = matrixData.data.permissions;
            rolesData = matrixData.data.roles;
            renderPermissionMatrix(matrixData.data.matrix);
            populateCategoryFilter();
        }
        
        if (statsData.success) {
            updateStats(statsData.data);
        }
        
        hideLoading();
    })
    .catch(error => {
        console.error('Failed to load initial data:', error);
        showError('데이터 로드에 실패했습니다.');
        hideLoading();
    });
}

// 권한 매트릭스 렌더링
function renderPermissionMatrix(matrix) {
    const table = document.getElementById('permissions-matrix-table');
    const thead = table?.querySelector('thead tr');
    const tbody = document.getElementById('permissions-matrix-body');
    
    if (!table || !thead || !tbody) return;
    
    // 헤더 생성 (역할들)
    thead.innerHTML = `
        <th class="sticky left-0 z-10 bg-gray-50 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
            권한
        </th>
    `;
    
    rolesData.forEach(role => {
        const th = document.createElement('th');
        th.className = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider';
        th.innerHTML = `
            <div class="flex flex-col items-center">
                <span class="mb-1">${role}</span>
                <button onclick="selectAllForRole('${role}')" class="text-xs text-blue-600 hover:text-blue-800">
                    전체선택
                </button>
            </div>
        `;
        thead.appendChild(th);
    });
    
    // 본문 생성
    tbody.innerHTML = '';
    
    Object.keys(permissionsData).forEach(category => {
        // 카테고리 헤더
        const categoryRow = document.createElement('tr');
        categoryRow.className = 'category-header';
        categoryRow.innerHTML = `
            <td colspan="${rolesData.length + 1}" class="px-6 py-2 font-semibold text-gray-900 bg-gray-100">
                ${category}
            </td>
        `;
        tbody.appendChild(categoryRow);
        
        // 권한 행들
        permissionsData[category].forEach(permission => {
            const row = document.createElement('tr');
            row.className = 'permission-row';
            row.dataset.permission = permission.name;
            row.dataset.category = category;
            
            let rowHtml = `
                <td class="sticky left-0 z-10 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200">
                    <div class="flex items-center">
                        <div>
                            <div>${permission.name}</div>
                            ${permission.description ? `<div class="text-xs text-gray-500">${permission.description}</div>` : ''}
                        </div>
                    </div>
                </td>
            `;
            
            rolesData.forEach(role => {
                const isChecked = matrix[role] && matrix[role][category] && matrix[role][category][permission.name];
                rowHtml += `
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <input type="checkbox" 
                               class="permission-checkbox" 
                               data-role="${role}" 
                               data-permission="${permission.name}"
                               ${isChecked ? 'checked' : ''}
                               onchange="toggleRolePermission('${role}', '${permission.name}', this.checked)">
                    </td>
                `;
            });
            
            row.innerHTML = rowHtml;
            tbody.appendChild(row);
        });
    });
    
    document.getElementById('matrix-loading')?.classList.add('hidden');
    table?.classList.remove('hidden');
}

// 카테고리 필터 채우기
function populateCategoryFilter() {
    const select = document.getElementById('category-filter');
    if (!select) return;
    
    // 기존 옵션 제거 (첫 번째 제외)
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    
    Object.keys(permissionsData).forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        select.appendChild(option);
    });
}

// 통계 업데이트
function updateStats(stats) {
    const totalPermEl = document.getElementById('total-permissions');
    const activeRolesEl = document.getElementById('active-roles');
    const totalCatEl = document.getElementById('total-categories');
    
    if (totalPermEl) totalPermEl.textContent = stats.total_permissions || 0;
    if (activeRolesEl) activeRolesEl.textContent = stats.total_roles || 0;
    if (totalCatEl) totalCatEl.textContent = Object.keys(stats.permissions_by_category || {}).length;
}

// 권한 검색 처리
function handlePermissionSearch() {
    const searchInput = document.getElementById('permission-search');
    if (!searchInput) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('.permission-row');
    
    rows.forEach(row => {
        const permissionName = row.dataset.permission.toLowerCase();
        const permissionDesc = row.querySelector('.text-xs')?.textContent?.toLowerCase() || '';
        const category = row.dataset.category.toLowerCase();
        
        const matches = permissionName.includes(searchTerm) || 
                       permissionDesc.includes(searchTerm) || 
                       category.includes(searchTerm);
        
        row.style.display = matches ? '' : 'none';
    });
    
    updateCategoryHeaders();
}

// 카테고리 필터 처리
function handleCategoryFilter() {
    const categoryFilter = document.getElementById('category-filter');
    if (!categoryFilter) return;
    
    const selectedCategory = categoryFilter.value;
    const rows = document.querySelectorAll('.permission-row');
    const categoryHeaders = document.querySelectorAll('.category-header');
    
    if (!selectedCategory) {
        rows.forEach(row => row.style.display = '');
        categoryHeaders.forEach(header => header.style.display = '');
    } else {
        rows.forEach(row => {
            row.style.display = row.dataset.category === selectedCategory ? '' : 'none';
        });
        
        categoryHeaders.forEach(header => {
            const categoryName = header.textContent.trim();
            header.style.display = categoryName === selectedCategory ? '' : 'none';
        });
    }
}

// 역할 권한 토글
function toggleRolePermission(role, permission, isChecked) {
    const data = {
        role_name: role,
        permissions: []
    };
    
    // 해당 역할의 모든 권한 상태를 수집
    const roleCheckboxes = document.querySelectorAll(`[data-role="${role}"]`);
    roleCheckboxes.forEach(checkbox => {
        if (checkbox.checked) {
            data.permissions.push(checkbox.dataset.permission);
        }
    });
    
    // API 호출
    fetch('/api/platform/admin/permissions/roles/permissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            // 실패시 체크박스 상태 되돌리기
            const checkbox = document.querySelector(`[data-role="${role}"][data-permission="${permission}"]`);
            checkbox.checked = !isChecked;
            showError('권한 업데이트에 실패했습니다.');
        } else {
            showSuccess('권한이 업데이트되었습니다.');
        }
    })
    .catch(error => {
        console.error('Permission update failed:', error);
        const checkbox = document.querySelector(`[data-role="${role}"][data-permission="${permission}"]`);
        checkbox.checked = !isChecked;
        showError('권한 업데이트 중 오류가 발생했습니다.');
    });
}

// 역할별 전체 선택
function selectAllForRole(role) {
    const checkboxes = document.querySelectorAll(`[data-role="${role}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    // 권한 업데이트
    const permissions = allChecked ? [] : Array.from(checkboxes).map(cb => cb.dataset.permission);
    
    fetch('/api/platform/admin/permissions/roles/permissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            role_name: role,
            permissions: permissions
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess(`${role} 역할의 권한이 ${allChecked ? '제거' : '할당'}되었습니다.`);
        } else {
            showError('권한 업데이트에 실패했습니다.');
            checkboxes.forEach(checkbox => {
                checkbox.checked = allChecked;
            });
        }
    })
    .catch(error => {
        console.error('Bulk permission update failed:', error);
        showError('권한 업데이트 중 오류가 발생했습니다.');
        checkboxes.forEach(checkbox => {
            checkbox.checked = allChecked;
        });
    });
}

// 카테고리 헤더 업데이트
function updateCategoryHeaders() {
    const categoryHeaders = document.querySelectorAll('.category-header');
    
    categoryHeaders.forEach(header => {
        const categoryName = header.textContent.trim();
        const categoryRows = document.querySelectorAll(`[data-category="${categoryName}"]`);
        const visibleRows = Array.from(categoryRows).filter(row => row.style.display !== 'none');
        
        header.style.display = visibleRows.length > 0 ? '' : 'none';
    });
}
</script>