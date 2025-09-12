{{-- 사용자 검색 및 필터링 JavaScript --}}
<script>
// 사용자 검색 및 필터링 기능
function searchUsers(searchTerm) {
    const rows = document.querySelectorAll('#usersTableBody tr');
    const term = searchTerm.toLowerCase();

    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // 빈 행은 제외

        const name = row.querySelector('.text-sm.font-medium.text-gray-900')?.textContent.toLowerCase() || '';
        const email = row.querySelector('.text-sm.text-gray-500')?.textContent.toLowerCase() || '';
        const organization = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';

        const matches = name.includes(term) || email.includes(term) || organization.includes(term);
        row.style.display = matches ? '' : 'none';
    });

    updateEmptyState();
}

function filterByRole(role) {
    const rows = document.querySelectorAll('#usersTableBody tr');

    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // 빈 행은 제외

        const roleElements = row.querySelectorAll('td:nth-child(2) .inline-flex');
        let hasRole = false;

        if (role === '') {
            hasRole = true; // 모든 역할 표시
        } else if (role === 'no_role') {
            hasRole = roleElements.length === 0 ||
                     (roleElements.length === 1 && roleElements[0].textContent.includes('역할 없음'));
        } else {
            const roleText = role === 'platform_admin' ? '플랫폼 관리자' :
                           role === 'organization_admin' ? '조직 관리자' :
                           role === 'organization_member' ? '조직 멤버' : role;

            Array.from(roleElements).forEach(elem => {
                if (elem.textContent.includes(roleText)) {
                    hasRole = true;
                }
            });
        }

        row.style.display = hasRole ? '' : 'none';
    });

    updateEmptyState();
}

function filterByOrganization(orgId) {
    const rows = document.querySelectorAll('#usersTableBody tr');

    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // 빈 행은 제외

        if (orgId === '') {
            row.style.display = ''; // 모든 조직 표시
            return;
        }

        const orgPermissionCell = row.querySelector('td:nth-child(3)');
        let hasOrganization = false;

        if (orgId === 'no_org') {
            // 조직 소속 없음 체크
            hasOrganization = orgPermissionCell.textContent.includes('조직 소속 없음');
        } else {
            // 특정 조직 체크
            const orgElements = orgPermissionCell.querySelectorAll('.text-xs.text-gray-600');
            Array.from(orgElements).forEach(elem => {
                const orgName = elem.textContent.trim();
                // 조직 ID로 매칭하는 것이 더 정확하지만, 현재는 이름으로 매칭
                if (elem.title && elem.title.includes(orgName)) {
                    hasOrganization = true;
                }
            });
        }

        row.style.display = hasOrganization ? '' : 'none';
    });

    updateEmptyState();
}

function filterByPermission(permissionLevel) {
    const rows = document.querySelectorAll('#usersTableBody tr');

    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // 빈 행은 제외

        if (permissionLevel === '') {
            row.style.display = ''; // 모든 권한 표시
            return;
        }

        const orgPermissionCell = row.querySelector('td:nth-child(3)');
        let hasPermission = false;

        // 새로운 역할 기반 시스템에서는 역할명으로 직접 검색
        hasPermission = orgPermissionCell.textContent.includes(permissionLevel);

        row.style.display = hasPermission ? '' : 'none';
    });

    updateEmptyState();
}

function clearFilters() {
    // 검색어 초기화
    document.getElementById('userSearchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('organizationFilter').value = '';
    document.getElementById('permissionFilter').value = '';

    // 모든 행 표시
    const rows = document.querySelectorAll('#usersTableBody tr');
    rows.forEach(row => {
        row.style.display = '';
    });

    updateEmptyState();
}

function updateEmptyState() {
    const rows = document.querySelectorAll('#usersTableBody tr');
    const visibleRows = Array.from(rows).filter(row =>
        row.style.display !== 'none' && !row.querySelector('td[colspan]')
    );

    const emptyRow = document.querySelector('#usersTableBody tr td[colspan]');
    if (emptyRow) {
        emptyRow.closest('tr').style.display = visibleRows.length === 0 ? '' : 'none';
        if (visibleRows.length === 0) {
            emptyRow.textContent = '검색 결과가 없습니다.';
        }
    }
}

// 페이지 로드시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 사용자 테이블에 ID 추가 (검색을 위해)
    const tbody = document.querySelector('tbody');
    if (tbody) {
        tbody.id = 'usersTableBody';
    }
});
</script>
