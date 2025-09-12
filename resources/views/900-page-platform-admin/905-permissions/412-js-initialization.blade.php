{{-- 초기화 및 이벤트 리스너 JavaScript --}}
<script>
// Livewire 이벤트 리스너 설정
document.addEventListener('DOMContentLoaded', function() {
    // 권한 관리 탭에서만 실행
    if (window.location.pathname.includes('/permissions/permissions')) {
        // Livewire 스코프 변경 이벤트 리스너
        window.addEventListener('scope-changed', function(event) {
            const { scope, organizationId } = event.detail[0];
            currentScope = scope;
            currentOrganizationId = organizationId;
            
            // 스코프가 조직이고 조직 ID가 없으면 데이터를 로드하지 않음
            if (scope === 'organization' && !organizationId) {
                // 컨트롤 패널과 매트릭스 숨기기
                const controlPanel = document.getElementById('control-panel');
                const permissionsMatrix = document.getElementById('permissions-matrix');
                controlPanel.style.display = 'none';
                permissionsMatrix.style.display = 'none';
                return;
            }
            
            // 데이터 로드
            loadScopedData();
        });
        
        setTimeout(() => {
            // 기존 DOM 기반 스코프 선택 이벤트 리스너는 Livewire로 대체됨
            
            const searchInput = document.getElementById('permission-search');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(handlePermissionSearch, 300));
            }
            
            const categoryFilter = document.getElementById('category-filter');
            if (categoryFilter) {
                categoryFilter.addEventListener('change', handleCategoryFilter);
            }
            
            // 기본적으로는 데이터를 로드하지 않음 - 사용자가 스코프를 선택해야 함
            loadOrganizationList();
        }, 100);
    }
    
    // 역할 관리 페이지에서 실행
    if (window.location.pathname.includes('/permissions/roles') || 
        document.getElementById('roles-table-body')) {
        
        // 페이지 로드 시 역할 데이터 로드
        setTimeout(() => {
            loadRolesData();
        }, 100);
        
        // 탭 전환 시 역할 데이터 로드
        const roleTabLink = document.querySelector('[data-tab="roles"]');
        if (roleTabLink) {
            roleTabLink.addEventListener('click', function() {
                setTimeout(() => {
                    loadRolesData();
                }, 100);
            });
        }
    }
});
</script>