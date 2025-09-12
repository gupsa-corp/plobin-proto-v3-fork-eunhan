{{-- 모달 관리 JavaScript --}}
<script>
// 모달 관련 함수
function openCreateRoleModal() {
    document.getElementById('create-role-modal').classList.remove('hidden');
}

function closeCreateRoleModal() {
    document.getElementById('create-role-modal').classList.add('hidden');
}

// 모달 외부 클릭시 닫기
document.addEventListener('click', function(event) {
    const modal = document.getElementById('create-role-modal');
    if (event.target === modal) {
        closeCreateRoleModal();
    }
});

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCreateRoleModal();
        closeRoleChangeModal();
        closeStatusChangeModal();
        closeTenantPermissionModal();
    }
});

// 모달 외부 클릭시 닫기 - 추가 모달들
document.addEventListener('click', function(event) {
    const roleModal = document.getElementById('roleChangeModal');
    const statusModal = document.getElementById('statusChangeModal');
    const tenantModal = document.getElementById('tenantPermissionModal');
    
    if (event.target === roleModal) {
        closeRoleChangeModal();
    }
    if (event.target === statusModal) {
        closeStatusChangeModal();
    }
    if (event.target === tenantModal) {
        closeTenantPermissionModal();
    }
});
</script>