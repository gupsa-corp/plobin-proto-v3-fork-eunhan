{{-- 사용자 상태 변경 관리 JavaScript --}}
<script>
// 사용자 상태 변경 모달 관련
function toggleUserStatus(userId) {
    currentUserId = userId;
    // 사용자 이름 찾기
    const userRow = document.querySelector(`button[onclick="toggleUserStatus(${userId})"]`).closest('tr');
    currentUserName = userRow.querySelector('.text-sm.font-medium.text-gray-900').textContent;
    
    document.getElementById('statusChangeMessage').textContent = 
        `정말 ${currentUserName}을(를) 비활성화 하시겠습니까?`;
    document.getElementById('statusChangeModal').classList.remove('hidden');
}

function closeStatusChangeModal() {
    document.getElementById('statusChangeModal').classList.add('hidden');
    currentUserId = null;
    currentUserName = null;
}

function confirmStatusChange() {
    // AJAX 요청 (실제 구현시 추가)
    fetch('/platform/admin/permissions/users/toggle-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            user_id: currentUserId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('사용자 상태가 변경되었습니다.');
            location.reload(); // 페이지 새로고침
        } else {
            alert('상태 변경에 실패했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
    
    closeStatusChangeModal();
}
</script>