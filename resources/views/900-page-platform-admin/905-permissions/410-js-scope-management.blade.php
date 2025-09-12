{{-- 스코프 선택 관리 JavaScript --}}
<script>
// 스코프 선택 관련 전역 변수
let currentScope = null;
let currentOrganizationId = null;

// 스코프 선택 처리
function handleScopeSelection(scope) {
    currentScope = scope;
    const organizationSelector = document.getElementById('organization-selector');
    const controlPanel = document.getElementById('control-panel');
    const permissionsMatrix = document.getElementById('permissions-matrix');
    
    if (scope === 'organization') {
        organizationSelector.classList.remove('hidden');
        // 조직 선택까지 완료되면 데이터 로드
    } else if (scope === 'platform') {
        organizationSelector.classList.add('hidden');
        currentOrganizationId = null;
        // 즉시 플랫폼 권한 데이터 로드
        loadScopedData();
    }
    
    // UI 업데이트
    updateScopeUI();
}

// 조직 선택 처리
function handleOrganizationSelection(organizationId) {
    currentOrganizationId = organizationId;
    if (currentScope === 'organization' && organizationId) {
        loadScopedData();
    }
}

// 스코프별 데이터 로드
function loadScopedData() {
    const controlPanel = document.getElementById('control-panel');
    const permissionsMatrix = document.getElementById('permissions-matrix');
    
    // UI 요소 표시
    controlPanel.style.display = 'block';
    permissionsMatrix.style.display = 'block';
    
    // API 요청에 스코프 정보 포함
    Promise.all([
        fetch(`/api/platform/admin/permissions/matrix?scope=${currentScope}&organization_id=${currentOrganizationId || ''}`),
        fetch(`/api/platform/admin/permissions/stats?scope=${currentScope}&organization_id=${currentOrganizationId || ''}`)
    ])
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(([matrixData, statsData]) => {
        if (matrixData.success) {
            permissionsData = matrixData.data.permissions;
            rolesData = matrixData.data.roles;
            renderPermissionMatrix(matrixData.data.matrix);
            populateCategoryFilter();
            
            // Calculate and update stats from the matrix data
            const totalPermissions = Object.values(permissionsData).reduce((total, perms) => total + perms.length, 0);
            const totalCategories = Object.keys(permissionsData).length;
            const totalRoles = rolesData.length;
            
            // Update stats display
            document.getElementById('total-permissions').textContent = totalPermissions;
            document.getElementById('active-roles').textContent = totalRoles;
            document.getElementById('total-categories').textContent = totalCategories;
        } else {
            showError('권한 데이터 로드 실패: ' + (matrixData.message || '알 수 없는 오류'));
        }
        
        // Stats API is optional now since we calculate from matrix data
        if (statsData && statsData.success) {
            // Override with stats API data if available
            updateStats(statsData.data);
        }
        
        hideLoading();
    })
    .catch(error => {
        console.error('Failed to load scoped data:', error);
        showError('데이터 로드 중 오류가 발생했습니다.');
        hideLoading();
    });
}

// UI 업데이트
function updateScopeUI() {
    // 선택된 라디오 버튼에 따라 스타일 업데이트
    const platformLabel = document.querySelector('[for="scope-platform"]');
    const organizationLabel = document.querySelector('[for="scope-organization"]');
    
    if (currentScope === 'platform') {
        platformLabel.classList.add('border-white', 'bg-opacity-20');
        organizationLabel.classList.remove('border-white', 'bg-opacity-20');
    } else if (currentScope === 'organization') {
        organizationLabel.classList.add('border-white', 'bg-opacity-20');
        platformLabel.classList.remove('border-white', 'bg-opacity-20');
    }
}

// 조직 목록 로드
function loadOrganizationList() {
    fetch('/api/organizations/list')
    .then(response => response.json())
    .then(data => {
        const organizationFilter = document.getElementById('organization-filter');
        if (organizationFilter && data.success) {
            // 기존 옵션 제거 (첫 번째 제외)
            while (organizationFilter.children.length > 1) {
                organizationFilter.removeChild(organizationFilter.lastChild);
            }
            
            // 조직 목록 추가
            data.data.forEach(org => {
                const option = document.createElement('option');
                option.value = org.id;
                option.textContent = org.name;
                organizationFilter.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Failed to load organizations:', error);
        // 샘플 데이터 사용
        console.log('Using sample organization data');
    });
}
</script>