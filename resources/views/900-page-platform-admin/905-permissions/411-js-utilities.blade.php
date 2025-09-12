{{-- 유틸리티 함수들 JavaScript --}}
<script>
// 유틸리티 함수들
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function hideLoading() {
    document.getElementById('matrix-loading')?.classList.add('hidden');
}

function showError(message) {
    alert('오류: ' + message);
}

function showSuccess(message) {
    alert('성공: ' + message);
}
</script>