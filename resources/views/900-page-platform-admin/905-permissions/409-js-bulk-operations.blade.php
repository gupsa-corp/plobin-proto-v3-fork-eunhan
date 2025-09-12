{{-- 일괄 작업 관리 JavaScript --}}
<script>
// 일괄 관리 모드 토글
function toggleBulkMode() {
    isBulkMode = !isBulkMode;
    const bulkPanel = document.getElementById('bulk-actions-panel');
    
    if (isBulkMode) {
        bulkPanel?.classList.remove('hidden');
        showBulkCheckboxes();
    } else {
        bulkPanel?.classList.add('hidden');
        selectedPermissions.clear();
        hideBulkCheckboxes();
    }
}

// 일괄 체크박스 표시
function showBulkCheckboxes() {
    document.querySelectorAll('.permission-row').forEach(row => {
        const permissionName = row.dataset.permission;
        const cell = row.querySelector('td div');
        
        // 이미 체크박스가 있는지 확인
        if (!cell.querySelector('.bulk-checkbox')) {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'bulk-checkbox permission-checkbox mr-3';
            checkbox.dataset.permission = permissionName;
            checkbox.onchange = () => togglePermissionSelection(permissionName);
            
            cell.insertBefore(checkbox, cell.firstChild);
        }
    });
}

// 일괄 체크박스 숨김
function hideBulkCheckboxes() {
    document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.remove());
}

// 권한 선택 토글
function togglePermissionSelection(permission) {
    if (selectedPermissions.has(permission)) {
        selectedPermissions.delete(permission);
    } else {
        selectedPermissions.add(permission);
    }
    
    const countEl = document.getElementById('selected-count');
    if (countEl) countEl.textContent = selectedPermissions.size;
}

// 일괄 모드 취소
function cancelBulkMode() {
    toggleBulkMode();
}

// 권한 생성 모달 열기
function openCreatePermissionModal() {
    const name = prompt('권한 이름을 입력하세요:');
    if (!name) return;
    
    const category = prompt('카테고리를 입력하세요:');
    if (!category) return;
    
    const description = prompt('설명을 입력하세요 (선택사항):') || '';
    
    createPermission(name, category, description);
}

// 권한 생성
function createPermission(name, category, description) {
    fetch('/api/platform/admin/permissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            name: name,
            category: category,
            description: description,
            guard_name: 'web'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showSuccess('새 권한이 생성되었습니다.');
            loadInitialData();
        } else {
            showError('권한 생성에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Permission creation failed:', error);
        showError('권한 생성 중 오류가 발생했습니다.');
    });
}

// 권한 내보내기
function exportPermissions() {
    fetch('/api/platform/admin/permissions/export')
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const dataStr = JSON.stringify(result.data, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `permissions-export-${new Date().toISOString().split('T')[0]}.json`;
            link.click();
            
            URL.revokeObjectURL(url);
            showSuccess('권한 데이터가 내보내졌습니다.');
        } else {
            showError('내보내기에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Export failed:', error);
        showError('내보내기 중 오류가 발생했습니다.');
    });
}
</script>