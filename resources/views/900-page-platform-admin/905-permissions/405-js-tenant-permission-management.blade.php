{{-- 테넌트별 권한 관리 JavaScript --}}
<script>
let currentUserTenantPermissions = [];

// 테넌트별 권한 관리 모달 관련
function openTenantPermissionModal(userId) {
    currentUserId = userId;
    // 사용자 이름 찾기
    const userRow = document.querySelector(`button[onclick="openTenantPermissionModal(${userId})"]`).closest('tr');
    currentUserName = userRow.querySelector('.text-sm.font-medium.text-gray-900').textContent;
    
    document.getElementById('selectedUserNameTenant').textContent = currentUserName;
    
    // 사용자의 현재 테넌트 권한 정보 로드
    loadUserTenantPermissions(userId);
    
    document.getElementById('tenantPermissionModal').classList.remove('hidden');
}

function closeTenantPermissionModal() {
    document.getElementById('tenantPermissionModal').classList.add('hidden');
    currentUserId = null;
    currentUserName = null;
    currentUserTenantPermissions = [];
    document.getElementById('newOrganization').value = '';
    document.getElementById('newPermissionLevel').value = '';
    document.getElementById('currentTenantPermissions').innerHTML = '';
}

function loadUserTenantPermissions(userId) {
    // 현재 테이블에서 사용자의 조직 권한 정보를 추출
    const userRow = document.querySelector(`button[onclick="openTenantPermissionModal(${userId})"]`).closest('tr');
    const orgPermissionCell = userRow.querySelector('td:nth-child(3)');
    
    currentUserTenantPermissions = [];
    const permissionElements = orgPermissionCell.querySelectorAll('.flex.items-center.space-x-2');
    
    permissionElements.forEach(elem => {
        const orgName = elem.querySelector('.text-xs.text-gray-600').textContent.trim();
        const permission = elem.querySelector('.inline-flex').textContent.trim();
        currentUserTenantPermissions.push({ orgName, permission, elem });
    });
    
    renderCurrentTenantPermissions();
}

function renderCurrentTenantPermissions() {
    const container = document.getElementById('currentTenantPermissions');
    container.innerHTML = '';
    
    if (currentUserTenantPermissions.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500">현재 조직 권한이 없습니다.</p>';
        return;
    }
    
    currentUserTenantPermissions.forEach((perm, index) => {
        const permissionDiv = document.createElement('div');
        permissionDiv.className = 'flex items-center justify-between p-2 border rounded';
        permissionDiv.innerHTML = `
            <div class="flex items-center space-x-2">
                <span class="font-medium">${perm.orgName}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    ${perm.permission}
                </span>
            </div>
            <div class="flex space-x-2">
                <button onclick="editTenantPermission(${index})" class="text-indigo-600 hover:text-indigo-900 text-xs">수정</button>
                <button onclick="removeTenantPermission(${index})" class="text-red-600 hover:text-red-900 text-xs">제거</button>
            </div>
        `;
        container.appendChild(permissionDiv);
    });
}

function addTenantPermission() {
    const orgId = document.getElementById('newOrganization').value;
    const permissionLevel = document.getElementById('newPermissionLevel').value;
    
    if (!orgId || !permissionLevel) {
        alert('조직과 권한 레벨을 모두 선택해주세요.');
        return;
    }
    
    const orgName = document.getElementById('newOrganization').selectedOptions[0].text;
    const permissionText = document.getElementById('newPermissionLevel').selectedOptions[0].text;
    
    // 중복 체크
    const exists = currentUserTenantPermissions.find(p => p.orgName === orgName);
    if (exists) {
        alert('이미 해당 조직에 권한이 설정되어 있습니다.');
        return;
    }
    
    currentUserTenantPermissions.push({
        orgId,
        orgName,
        permissionLevel,
        permission: permissionText,
        isNew: true
    });
    
    renderCurrentTenantPermissions();
    
    // 폼 리셋
    document.getElementById('newOrganization').value = '';
    document.getElementById('newPermissionLevel').value = '';
}

function editTenantPermission(index) {
    // 간단한 구현: 제거 후 다시 추가하도록 안내
    if (confirm('권한을 수정하려면 제거 후 다시 추가해주세요. 제거하시겠습니까?')) {
        removeTenantPermission(index);
    }
}

function removeTenantPermission(index) {
    if (confirm('정말 이 권한을 제거하시겠습니까?')) {
        currentUserTenantPermissions.splice(index, 1);
        renderCurrentTenantPermissions();
    }
}

function saveTenantPermissions() {
    const permissions = currentUserTenantPermissions.map(p => ({
        organization_id: p.orgId,
        role_name: p.roleName || p.permissionLevel, // role_name 사용
        is_new: p.isNew || false
    }));
    
    // AJAX 요청
    fetch('/platform/admin/permissions/users/update-tenant-permissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            user_id: currentUserId,
            permissions: permissions
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('권한이 성공적으로 업데이트되었습니다.');
            location.reload();
        } else {
            alert('권한 업데이트에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
    
    closeTenantPermissionModal();
}
</script>