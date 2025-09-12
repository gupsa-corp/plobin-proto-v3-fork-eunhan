/**
 * PMS 공통 JavaScript 함수들
 */

// 모달 관리 함수들
window.pmsModal = {
    /**
     * 모달 열기
     */
    open: function(modalId, title, data = {}) {
        const modal = document.querySelector(`[x-data*="${modalId}"]`);
        if (modal && modal.__x) {
            modal.__x.$data.modalOpen = true;
            modal.__x.$data.modalTitle = title;
            Object.assign(modal.__x.$data, data);
        }
    },

    /**
     * 모달 닫기
     */
    close: function(modalId) {
        const modal = document.querySelector(`[x-data*="${modalId}"]`);
        if (modal && modal.__x) {
            modal.__x.$data.modalOpen = false;
        }
    }
};

// 테이블 관리 함수들
window.pmsTable = {
    /**
     * 전체 선택/해제
     */
    toggleAll: function(checked) {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.updateSelectedCount();
    },

    /**
     * 선택된 항목 수 업데이트
     */
    updateSelectedCount: function() {
        const selected = document.querySelectorAll('tbody input[type="checkbox"]:checked');
        const countEl = document.querySelector('.selected-count');
        if (countEl) {
            countEl.textContent = selected.length;
        }
    },

    /**
     * 테이블 정렬 적용
     */
    sort: function(column, order) {
        const url = new URL(window.location);
        url.searchParams.set('sort', column);
        url.searchParams.set('order', order);
        window.location.href = url.toString();
    }
};

// 폼 유틸리티 함수들
window.pmsForm = {
    /**
     * 폼 데이터 수집
     */
    serialize: function(form) {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        return data;
    },

    /**
     * 폼 유효성 검사
     */
    validate: function(form, rules = {}) {
        const errors = {};
        const data = this.serialize(form);

        for (let field in rules) {
            const rule = rules[field];
            const value = data[field];

            if (rule.required && (!value || value.trim() === '')) {
                errors[field] = rule.message || `${field}는 필수 입력입니다.`;
            }

            if (rule.minLength && value && value.length < rule.minLength) {
                errors[field] = rule.message || `${field}는 최소 ${rule.minLength}자 이상 입력해주세요.`;
            }

            if (rule.maxLength && value && value.length > rule.maxLength) {
                errors[field] = rule.message || `${field}는 최대 ${rule.maxLength}자까지 입력 가능합니다.`;
            }
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors: errors
        };
    }
};

// 유틸리티 함수들
window.pmsUtils = {
    /**
     * 날짜 포맷팅
     */
    formatDate: function(date, format = 'YYYY-MM-DD') {
        if (!date) return '';
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day);
    },

    /**
     * 숫자 포맷팅
     */
    formatNumber: function(number, decimals = 0) {
        return new Intl.NumberFormat('ko-KR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    },

    /**
     * 진행률 색상 반환
     */
    getProgressColor: function(progress) {
        if (progress >= 80) return 'bg-green-500';
        if (progress >= 50) return 'bg-yellow-500';
        if (progress >= 20) return 'bg-orange-500';
        return 'bg-red-500';
    },

    /**
     * 상태별 색상 클래스 반환
     */
    getStatusColor: function(status) {
        const colors = {
            'planned': 'bg-gray-100 text-gray-800',
            'in_progress': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'on_hold': 'bg-yellow-100 text-yellow-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    },

    /**
     * 우선순위별 색상 클래스 반환
     */
    getPriorityColor: function(priority) {
        const colors = {
            'high': 'bg-red-100 text-red-800',
            'medium': 'bg-yellow-100 text-yellow-800',
            'low': 'bg-green-100 text-green-800'
        };
        return colors[priority] || 'bg-gray-100 text-gray-800';
    },

    /**
     * 디바운스 함수
     */
    debounce: function(func, wait) {
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
};

// API 호출 함수들
window.pmsApi = {
    /**
     * 기본 API 호출
     */
    call: async function(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const finalOptions = Object.assign(defaultOptions, options);

        try {
            const response = await fetch(url, finalOptions);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || '요청 처리 중 오류가 발생했습니다.');
            }

            return data;
        } catch (error) {
            console.error('API 호출 오류:', error);
            throw error;
        }
    },

    /**
     * GET 요청
     */
    get: function(url) {
        return this.call(url, { method: 'GET' });
    },

    /**
     * POST 요청
     */
    post: function(url, data) {
        return this.call(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    /**
     * PUT 요청
     */
    put: function(url, data) {
        return this.call(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    /**
     * DELETE 요청
     */
    delete: function(url) {
        return this.call(url, { method: 'DELETE' });
    }
};