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
            alert('프로젝트가 성공적으로 업데이트되었습니다: ' + selectedProject.name);
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
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>