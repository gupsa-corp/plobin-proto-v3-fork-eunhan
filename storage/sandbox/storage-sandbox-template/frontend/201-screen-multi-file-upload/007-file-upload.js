/**
 * 파일 업로드 JavaScript 유틸리티
 * Livewire와 함께 사용하는 클라이언트 사이드 헬퍼 함수들
 */

class FileUploadHelper {
    constructor() {
        this.init();
    }

    /**
     * 초기화 함수
     */
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.setupGlobalHelpers();
            this.setupDragDropPolyfill();
        });
    }

    /**
     * 전역 헬퍼 함수들 설정
     */
    setupGlobalHelpers() {
        // 파일 아이콘 반환 함수
        window.getFileIcon = (fileName) => {
            const ext = fileName.split('.').pop().toLowerCase();
            const icons = {
                'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️', 'webp': '🖼️',
                'pdf': '📄', 'doc': '📝', 'docx': '📝', 'txt': '📄',
                'xls': '📊', 'xlsx': '📊', 'csv': '📊',
                'zip': '📦', 'rar': '📦', '7z': '📦',
                'mp4': '🎥', 'avi': '🎥', 'mov': '🎥',
                'mp3': '🎵', 'wav': '🎵', 'flac': '🎵'
            };
            return icons[ext] || '📄';
        };

        // 파일 크기 포맷 함수
        window.formatFileSize = (bytes) => {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        };

        // 파일 타입 검증
        window.validateFileType = (file, allowedTypes = []) => {
            if (allowedTypes.length === 0) return true;
            const fileType = file.type.toLowerCase();
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            return allowedTypes.some(type => 
                fileType.includes(type) || type === fileExtension
            );
        };

        // 파일 크기 검증
        window.validateFileSize = (file, maxSizeMB = 50) => {
            const maxSizeBytes = maxSizeMB * 1024 * 1024;
            return file.size <= maxSizeBytes;
        };

        // 여러 파일 총 크기 계산
        window.getTotalFileSize = (files) => {
            return Array.from(files).reduce((total, file) => total + file.size, 0);
        };

        // 파일 정보 객체 생성
        window.createFileInfo = (file) => {
            return {
                name: file.name,
                size: file.size,
                type: file.type,
                lastModified: file.lastModified,
                extension: file.name.split('.').pop().toLowerCase(),
                icon: window.getFileIcon(file.name),
                formattedSize: window.formatFileSize(file.size)
            };
        };
    }

    /**
     * 드래그 앤 드롭 폴리필 설정
     */
    setupDragDropPolyfill() {
        // 전역 드래그 앤 드롭 이벤트 처리
        window.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        window.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        // 드롭 핸들러 함수
        window.handleDrop = (event, wireComponentId = null) => {
            event.preventDefault();
            event.stopPropagation();

            const files = Array.from(event.dataTransfer.files);
            
            if (wireComponentId) {
                // Livewire 컴포넌트에 파일 전달
                const component = window.livewire.find(wireComponentId);
                if (component) {
                    component.set('files', files);
                }
            }

            return files;
        };

        // 파일 선택 핸들러
        window.handleFileSelect = (event, wireComponentId = null) => {
            const files = Array.from(event.target.files);
            
            if (wireComponentId) {
                const component = window.livewire.find(wireComponentId);
                if (component) {
                    component.set('files', files);
                }
            }

            return files;
        };
    }

    /**
     * 업로드 진행률 표시기
     */
    static createProgressBar(containerId, fileName, fileIndex) {
        const container = document.getElementById(containerId);
        if (!container) return null;

        const progressElement = document.createElement('div');
        progressElement.className = 'mb-3';
        progressElement.id = `progress-item-${fileIndex}`;
        progressElement.innerHTML = `
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-700 flex items-center">
                    <span class="mr-2">${window.getFileIcon(fileName)}</span>
                    ${fileName}
                </span>
                <span id="progress-text-${fileIndex}" class="text-gray-500 font-medium">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                    id="progress-bar-${fileIndex}" 
                    class="bg-green-500 h-2 rounded-full transition-all duration-500 ease-out" 
                    style="width: 0%"
                ></div>
            </div>
        `;

        container.appendChild(progressElement);
        return {
            element: progressElement,
            updateProgress: (percent) => {
                const progressBar = document.getElementById(`progress-bar-${fileIndex}`);
                const progressText = document.getElementById(`progress-text-${fileIndex}`);
                
                if (progressBar) progressBar.style.width = `${percent}%`;
                if (progressText) progressText.textContent = `${Math.round(percent)}%`;
            }
        };
    }

    /**
     * 파일 업로드 시뮬레이터 (개발용)
     */
    static simulateFileUpload(fileIndex, onProgress, onComplete, duration = 3000) {
        let progress = 0;
        const increment = 100 / (duration / 100);
        
        const interval = setInterval(() => {
            progress += increment + (Math.random() * increment * 0.5);
            
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                if (onComplete) onComplete(fileIndex);
            }
            
            if (onProgress) onProgress(fileIndex, progress);
        }, 100);

        return interval;
    }

    /**
     * 클립보드에서 파일 붙여넣기 지원
     */
    static enablePasteSupport(targetElement, callback) {
        targetElement.addEventListener('paste', (event) => {
            const items = event.clipboardData?.items;
            if (!items) return;

            const files = [];
            for (const item of items) {
                if (item.kind === 'file') {
                    files.push(item.getAsFile());
                }
            }

            if (files.length > 0 && callback) {
                callback(files);
            }
        });
    }

    /**
     * 업로드 에러 처리
     */
    static handleUploadError(error, fileName) {
        console.error(`Upload error for ${fileName}:`, error);
        
        // 사용자에게 에러 메시지 표시
        const errorMessage = this.getErrorMessage(error);
        this.showNotification(errorMessage, 'error');
    }

    /**
     * 에러 메시지 변환
     */
    static getErrorMessage(error) {
        const errorMessages = {
            'file_too_large': '파일 크기가 제한을 초과했습니다.',
            'invalid_file_type': '지원하지 않는 파일 형식입니다.',
            'upload_failed': '업로드 중 오류가 발생했습니다.',
            'network_error': '네트워크 연결을 확인해주세요.',
            'server_error': '서버 오류가 발생했습니다.'
        };

        return errorMessages[error.code] || error.message || '알 수 없는 오류가 발생했습니다.';
    }

    /**
     * 알림 표시
     */
    static showNotification(message, type = 'info', duration = 5000) {
        // 알림 요소 생성
        const notification = document.createElement('div');
        notification.className = `
            fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300
            ${type === 'error' ? 'bg-red-500 text-white' : ''}
            ${type === 'success' ? 'bg-green-500 text-white' : ''}
            ${type === 'info' ? 'bg-blue-500 text-white' : ''}
            ${type === 'warning' ? 'bg-yellow-500 text-black' : ''}
        `;
        
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button class="ml-4 text-lg font-bold" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

        document.body.appendChild(notification);

        // 자동 제거
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, duration);
    }
}

// 전역 인스턴스 생성
const fileUploadHelper = new FileUploadHelper();

// 브라우저 지원 확인
if (typeof window !== 'undefined') {
    window.FileUploadHelper = FileUploadHelper;
}