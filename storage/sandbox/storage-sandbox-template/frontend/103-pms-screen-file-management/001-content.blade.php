{{-- 샌드박스 다중 파일 업로드 템플릿 --}}
<?php
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="text-blue-600">📁</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">다중 파일 업로드</h1>
                    <p class="text-gray-600">여러 파일을 한 번에 업로드하고 관리하세요</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button class="px-3 py-1 text-sm bg-white shadow-sm rounded-md text-blue-600">업로드</button>
                    <a href="<?= getScreenUrl('frontend', '008-screen-uploaded-files-list') ?>"
                       class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-200 rounded-md">
                        파일 목록
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">

        <!-- 업로드 영역 -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors duration-200">
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <p class="text-xl text-gray-600 mb-2">파일을 여기에 드래그하거나 클릭하여 선택하세요</p>
                <p class="text-sm text-gray-500 mb-4">최대 10MB per file, 총 50MB까지 업로드 가능</p>
                <input type="file" id="file-input" multiple class="hidden" accept="*/*">
                <button type="button" onclick="document.getElementById('file-input').click()"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                    파일 선택
                </button>
            </div>
        </div>

        <!-- 파일 목록 미리보기 -->
        <div id="file-list" class="bg-white rounded-lg shadow-md p-6 mb-8" style="display: none;">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">선택된 파일들</h2>
            <div id="selected-files" class="space-y-3"></div>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    총 <span id="total-files">0</span>개 파일, <span id="total-size">0</span> MB
                </div>
                <div class="space-x-3">
                    <button type="button" onclick="clearFiles()" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                        모두 제거
                    </button>
                    <button type="button" onclick="uploadFiles()" id="upload-btn" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200" disabled>
                        업로드 시작
                    </button>
                </div>
            </div>
        </div>

        <!-- 업로드 진행률 -->
        <div id="upload-progress" class="bg-white rounded-lg shadow-md p-6 mb-8" style="display: none;">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">업로드 진행률</h2>
            <div class="space-y-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="text-sm text-gray-600">
                    <span id="current-file">준비 중...</span>
                    <span id="progress-text" class="float-right">0%</span>
                </div>
            </div>
        </div>

        <!-- 업로드 결과 -->
        <div id="upload-results" class="bg-white rounded-lg shadow-md p-6" style="display: none;">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">업로드 결과</h2>
            <div id="results-list" class="space-y-3"></div>
        </div>
    </div>
</div>

<script>
let selectedFiles = [];
let uploadInProgress = false;

document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('file-input');

    // 드래그 앤 드롭 이벤트
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', handleDrop);
    uploadArea.addEventListener('click', () => fileInput.click());

    // 파일 선택 이벤트
    fileInput.addEventListener('change', handleFileSelect);
});

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('border-blue-400', 'bg-blue-50');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('border-blue-400', 'bg-blue-50');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('border-blue-400', 'bg-blue-50');

    const files = Array.from(e.dataTransfer.files);
    addFiles(files);
}

function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    addFiles(files);
    // 같은 파일 다시 선택할 수 있도록 초기화
    e.target.value = '';
}

function addFiles(files) {
    const maxFileSize = 10 * 1024 * 1024; // 10MB
    const maxTotalSize = 50 * 1024 * 1024; // 50MB
    let currentTotalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);

    files.forEach(file => {
        if (file.size > maxFileSize) {
            showNotification(`파일 "${file.name}"이(가) 너무 큽니다. 최대 10MB까지 허용됩니다.`, 'error');
            return;
        }

        if (currentTotalSize + file.size > maxTotalSize) {
            showNotification(`총 파일 크기가 50MB를 초과합니다.`, 'error');
            return;
        }

        // 중복 파일 검사
        if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
            showNotification(`파일 "${file.name}"이(가) 이미 선택되었습니다.`, 'warning');
            return;
        }

        selectedFiles.push(file);
        currentTotalSize += file.size;
    });

    updateFileList();
}

function updateFileList() {
    const fileList = document.getElementById('file-list');
    const selectedFilesDiv = document.getElementById('selected-files');
    const totalFilesSpan = document.getElementById('total-files');
    const totalSizeSpan = document.getElementById('total-size');
    const uploadBtn = document.getElementById('upload-btn');

    if (selectedFiles.length === 0) {
        fileList.style.display = 'none';
        return;
    }

    fileList.style.display = 'block';
    selectedFilesDiv.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
        fileDiv.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">${file.name}</p>
                    <p class="text-sm text-gray-500">${formatFileSize(file.size)}</p>
                </div>
            </div>
            <button type="button" onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        selectedFilesDiv.appendChild(fileDiv);
    });

    const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    totalFilesSpan.textContent = selectedFiles.length;
    totalSizeSpan.textContent = (totalSize / (1024 * 1024)).toFixed(2);
    uploadBtn.disabled = selectedFiles.length === 0;
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFileList();
}

function clearFiles() {
    selectedFiles = [];
    updateFileList();
}

async function uploadFiles() {
    if (uploadInProgress || selectedFiles.length === 0) return;

    uploadInProgress = true;
    const uploadBtn = document.getElementById('upload-btn');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const currentFileSpan = document.getElementById('current-file');
    const progressText = document.getElementById('progress-text');
    const uploadResults = document.getElementById('upload-results');
    const resultsList = document.getElementById('results-list');

    uploadBtn.disabled = true;
    uploadBtn.textContent = '업로드 중...';
    uploadProgress.style.display = 'block';
    uploadResults.style.display = 'block';
    resultsList.innerHTML = '';

    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < selectedFiles.length; i++) {
        const file = selectedFiles[i];
        const progress = ((i + 1) / selectedFiles.length) * 100;

        currentFileSpan.textContent = `업로드 중: ${file.name}`;
        progressBar.style.width = `${progress}%`;
        progressText.textContent = `${Math.round(progress)}%`;

        try {
            const result = await uploadFile(file);
            successCount++;

            const resultDiv = document.createElement('div');
            resultDiv.className = 'flex items-center space-x-3 p-3 bg-green-50 rounded-lg';
            resultDiv.innerHTML = `
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm text-green-700">${file.name} - 업로드 성공</span>
            `;
            resultsList.appendChild(resultDiv);
        } catch (error) {
            failCount++;

            const resultDiv = document.createElement('div');
            resultDiv.className = 'flex items-center space-x-3 p-3 bg-red-50 rounded-lg';
            resultDiv.innerHTML = `
                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-sm text-red-700">${file.name} - 업로드 실패: ${error.message}</span>
            `;
            resultsList.appendChild(resultDiv);
        }
    }

    uploadInProgress = false;
    uploadBtn.disabled = false;
    uploadBtn.textContent = '업로드 시작';

    currentFileSpan.textContent = '업로드 완료';
    progressText.textContent = '100%';

    // 완료 알림
    if (failCount === 0) {
        showNotification(`모든 파일이 성공적으로 업로드되었습니다! (${successCount}개)`, 'success');
    } else {
        showNotification(`업로드 완료: 성공 ${successCount}개, 실패 ${failCount}개`, 'warning');
    }

    // 파일 목록 초기화
    clearFiles();
}

async function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch('<?= getApiUrl("upload-handler.php") ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    });

    if (!response.ok) {
        const error = await response.json();
        throw new Error(error.message || '업로드 실패');
    }

    return await response.json();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showNotification(message, type = 'info') {
    // 간단한 알림 시스템 - 실제로는 더 정교한 시스템을 사용할 수 있습니다
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    } text-white`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
</div>
