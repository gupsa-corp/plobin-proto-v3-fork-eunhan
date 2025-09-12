{{-- 역할 변경 관리 JavaScript --}}
<script>
// 사용자 관리 관련 함수
let currentUserId = null;
let currentUserName = null;

// 역할 변경 모달 관련
function openRoleChangeModal(userId) {
    currentUserId = userId;
    // 사용자 이름 찾기
    const userRow = document.querySelector(`button[onclick="openRoleChangeModal(${userId})"]`).closest('tr');
    currentUserName = userRow.querySelector('.text-sm.font-medium.text-gray-900').textContent;
    
    document.getElementById('selectedUserName').textContent = currentUserName;
    document.getElementById('roleChangeModal').classList.remove('hidden');
}

function closeRoleChangeModal() {
    document.getElementById('roleChangeModal').classList.add('hidden');
    currentUserId = null;
    currentUserName = null;
    document.getElementById('newRole').value = '';
}

function saveRoleChange() {
    const newRole = document.getElementById('newRole').value;
    if (!newRole) {
        alert('역할을 선택해주세요.');
        return;
    }
    
    if (confirm(`정말 ${currentUserName}의 역할을 변경하시겠습니까?`)) {
        // AJAX 요청 (실제 구현시 추가)
        fetch('/platform/admin/permissions/users/change-role', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: currentUserId,
                role: newRole
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('역할이 변경되었습니다.');
                location.reload(); // 페이지 새로고침
            } else {
                alert('역할 변경에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
        
        closeRoleChangeModal();
    }
}
</script>