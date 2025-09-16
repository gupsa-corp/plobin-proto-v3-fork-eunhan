{{-- 파일 업로드 템플릿 --}}
<?php 
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;
    

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 p-6">

    {{-- 헤더 --}}
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl">📤</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">파일 업로드</h1>
                        <p class="text-gray-600">여러 파일을 한 번에 업로드하세요</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">업로드 제한</div>
                    <div class="text-lg font-semibold text-gray-900">50MB / 파일</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 업로드 영역 --}}
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
            <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-green-500 hover:bg-green-50 transition-colors cursor-pointer">
                <div class="mb-4">
                    <span class="text-6xl">📁</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">파일을 드래그 앤 드롭하거나 클릭하여 선택하세요</h3>
                <p class="text-gray-600 mb-4">JPG, PNG, PDF, DOC, XLS, ZIP 등 다양한 형식을 지원합니다</p>
                <input type="file" id="file-input" multiple class="hidden" accept="*/*">
                <button type="button" id="select-files-btn" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                    파일 선택
                </button>
            </div>
        </div>

        {{-- 선택된 파일 목록 --}}
        <div id="selected-files" class="bg-white rounded-xl shadow-sm p-6 mb-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="mr-2">📋</span>
                선택된 파일 (<span id="file-count">0</span>개)
            </h3>
            <div id="file-list" class="space-y-3 mb-4">
                {{-- 선택된 파일들이 여기에 표시됩니다 --}}
            </div>
            <div class="flex justify-between items-center pt-4 border-t">
                <div class="text-sm text-gray-600">
                    총 용량: <span id="total-size" class="font-semibold">0 MB</span>
                </div>
                <div class="space-x-2">
                    <button type="button" id="clear-files" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        전체 삭제
                    </button>
                    <button type="button" id="upload-files" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        업로드 시작
                    </button>
                </div>
            </div>
        </div>

        {{-- 업로드 진행 상황 --}}
        <div id="upload-progress" class="bg-white rounded-xl shadow-sm p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="mr-2">⏳</span>
                업로드 진행 상황
            </h3>
            <div class="space-y-3" id="progress-list">
                {{-- 업로드 진행 상황이 여기에 표시됩니다 --}}
            </div>
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>전체 진행률</span>
                    <span id="overall-progress-text">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div id="overall-progress-bar" class="bg-green-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>

        {{-- 업로드 완료 --}}
        <div id="upload-complete" class="bg-white rounded-xl shadow-sm p-6 hidden">
            <div class="text-center">
                <div class="mb-4">
                    <span class="text-6xl">✅</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">업로드 완료!</h3>
                <p class="text-gray-600 mb-4">모든 파일이 성공적으로 업로드되었습니다.</p>
                <div class="flex justify-center space-x-4">
                    <button type="button" onclick="window.location.reload()" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        새 업로드
                    </button>
                    <button type="button" onclick="window.location.href='#'" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        파일 목록 보기
                    </button>
                </div>
            </div>
        </div>

        {{-- 업로드 가이드라인 --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="mr-2">ℹ️</span>
                업로드 가이드라인
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">지원 형식</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• 이미지: JPG, PNG, GIF, WebP</li>
                        <li>• 문서: PDF, DOC, DOCX, TXT</li>
                        <li>• 스프레드시트: XLS, XLSX, CSV</li>
                        <li>• 압축파일: ZIP, RAR, 7Z</li>
                        <li>• 기타: 모든 파일 형식</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">제한사항</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• 최대 파일 크기: 50MB</li>
                        <li>• 한 번에 최대 20개 파일</li>
                        <li>• 총 업로드 크기: 500MB</li>
                        <li>• 악성코드 자동 검사</li>
                        <li>• 안전한 저장소에 보관</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const selectedFilesDiv = document.getElementById('selected-files');
        const fileList = document.getElementById('file-list');
        const fileCount = document.getElementById('file-count');
        const totalSize = document.getElementById('total-size');
        const clearFilesBtn = document.getElementById('clear-files');
        const uploadFilesBtn = document.getElementById('upload-files');
        const uploadProgress = document.getElementById('upload-progress');
        const progressList = document.getElementById('progress-list');
        const uploadComplete = document.getElementById('upload-complete');
        const overallProgressBar = document.getElementById('overall-progress-bar');
        const overallProgressText = document.getElementById('overall-progress-text');

        let selectedFiles = [];

        // 드래그 앤 드롭 이벤트
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-green-500', 'bg-green-50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-green-500', 'bg-green-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-green-500', 'bg-green-50');
            handleFiles(e.dataTransfer.files);
        });

        // 드롭존 클릭 이벤트 - 버튼 클릭은 제외
        dropZone.addEventListener('click', (e) => {
            // 버튼 클릭이 아닌 경우에만 파일 선택창 열기
            if (!e.target.closest('#select-files-btn')) {
                fileInput.click();
            }
        });

        // 파일 선택 버튼 클릭 이벤트
        const selectFilesBtn = document.getElementById('select-files-btn');
        selectFilesBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // 이벤트 버블링 방지
            fileInput.click();
        });

        // 파일 선택 이벤트
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        // 파일 처리
        function handleFiles(files) {
            selectedFiles = [...selectedFiles, ...Array.from(files)];
            updateFileList();
        }

        // 파일 목록 업데이트
        function updateFileList() {
            fileList.innerHTML = '';
            let totalSizeBytes = 0;

            selectedFiles.forEach((file, index) => {
                totalSizeBytes += file.size;
                
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                fileItem.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">${getFileIcon(file.name)}</span>
                        <div>
                            <p class="font-medium text-gray-900">${file.name}</p>
                            <p class="text-sm text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                        <span class="text-xl">🗑️</span>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });

            fileCount.textContent = selectedFiles.length;
            totalSize.textContent = formatFileSize(totalSizeBytes);
            
            if (selectedFiles.length > 0) {
                selectedFilesDiv.classList.remove('hidden');
            } else {
                selectedFilesDiv.classList.add('hidden');
            }
        }

        // 파일 삭제
        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            updateFileList();
        };

        // 전체 파일 삭제
        clearFilesBtn.addEventListener('click', () => {
            selectedFiles = [];
            updateFileList();
        });

        // 업로드 시작
        uploadFilesBtn.addEventListener('click', () => {
            if (selectedFiles.length === 0) return;
            
            selectedFilesDiv.classList.add('hidden');
            uploadProgress.classList.remove('hidden');
            
            simulateUpload();
        });

        // 업로드 시뮬레이션 (실제 구현에서는 실제 API 호출)
        function simulateUpload() {
            progressList.innerHTML = '';
            let completedFiles = 0;
            
            selectedFiles.forEach((file, index) => {
                const progressItem = document.createElement('div');
                progressItem.className = 'mb-3';
                progressItem.innerHTML = `
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">${file.name}</span>
                        <span id="progress-${index}" class="text-gray-500">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="bar-${index}" class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                `;
                progressList.appendChild(progressItem);
                
                // 개별 파일 업로드 시뮬레이션
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 20;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(interval);
                        completedFiles++;
                        
                        if (completedFiles === selectedFiles.length) {
                            setTimeout(() => {
                                uploadProgress.classList.add('hidden');
                                uploadComplete.classList.remove('hidden');
                            }, 500);
                        }
                    }
                    
                    document.getElementById(`progress-${index}`).textContent = Math.round(progress) + '%';
                    document.getElementById(`bar-${index}`).style.width = progress + '%';
                    
                    // 전체 진행률 업데이트
                    const overallProgress = (completedFiles * 100 + progress) / selectedFiles.length;
                    overallProgressBar.style.width = overallProgress + '%';
                    overallProgressText.textContent = Math.round(overallProgress) + '%';
                }, 200 + Math.random() * 300);
            });
        }

        // 파일 아이콘 반환
        function getFileIcon(fileName) {
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
        }

        // 파일 크기 포맷
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

    });
    </script>
</div>