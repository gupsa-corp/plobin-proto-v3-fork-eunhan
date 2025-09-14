{{-- 업로드 드롭존 컴포넌트 --}}
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8 mb-6">
        <div 
            id="drop-zone"
            class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-green-500 hover:bg-green-50 transition-colors cursor-pointer"
        >
            <div class="mb-4">
                <span class="text-6xl">📁</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                파일을 드래그 앤 드롭하거나 클릭하여 선택하세요
            </h3>
            <p class="text-gray-600 mb-4">
                JPG, PNG, PDF, DOC, XLS, ZIP 등 다양한 형식을 지원합니다
            </p>
            
            <input 
                type="file" 
                id="file-input"
                multiple 
                class="hidden" 
                accept="*/*"
            >
            
            <button 
                type="button" 
                onclick="document.getElementById('file-input').click()"
                class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors"
            >
                파일 선택
            </button>
        </div>

        {{-- 에러 메시지 --}}
        <div id="error-message" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg hidden">
            <div class="flex items-center">
                <span class="text-red-500 mr-2">⚠️</span>
                <span id="error-text" class="text-red-700"></span>
            </div>
        </div>

        {{-- 로딩 상태 --}}
        <div id="loading-state" class="mt-4 text-center hidden">
            <div class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-700">파일 처리 중...</span>
            </div>
        </div>
    </div>
</div>