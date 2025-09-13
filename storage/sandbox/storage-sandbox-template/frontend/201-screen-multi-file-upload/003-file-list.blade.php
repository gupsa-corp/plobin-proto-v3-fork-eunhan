{{-- 선택된 파일 목록 컴포넌트 --}}
@if(!empty($files))
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span class="mr-2">📋</span>
            선택된 파일 (<span class="text-green-600">{{ count($files) }}</span>개)
        </h3>
        
        <div class="space-y-3 mb-4">
            @foreach($files as $index => $file)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">{{ $this->getFileIcon($file->getClientOriginalName()) }}</span>
                        <div>
                            <p class="font-medium text-gray-900">{{ $file->getClientOriginalName() }}</p>
                            <p class="text-sm text-gray-500">{{ $this->formatFileSize($file->getSize()) }}</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="removeFile({{ $index }})"
                        class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors"
                        title="파일 삭제"
                    >
                        <span class="text-xl">🗑️</span>
                    </button>
                </div>
            @endforeach
        </div>
        
        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                총 용량: <span class="font-semibold text-gray-900">{{ $this->getTotalSize() }}</span>
            </div>
            <div class="flex space-x-2">
                <button 
                    type="button" 
                    wire:click="clearFiles"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2"
                >
                    <span>🗑️</span>
                    <span>전체 삭제</span>
                </button>
                <button 
                    type="button" 
                    wire:click="startUpload"
                    class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center space-x-2"
                    wire:loading.attr="disabled"
                    wire:target="startUpload"
                >
                    <span wire:loading.remove wire:target="startUpload">🚀</span>
                    <svg wire:loading wire:target="startUpload" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>업로드 시작</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 파일 제한 안내 --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start space-x-2">
            <span class="text-blue-500 mt-0.5">ℹ️</span>
            <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">업로드 제한사항</p>
                <ul class="space-y-1 text-blue-600">
                    <li>• 최대 파일 크기: {{ $maxFileSize }}MB</li>
                    <li>• 최대 파일 개수: {{ $maxFiles }}개</li>
                    <li>• 총 업로드 크기: {{ $maxTotalSize }}MB</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif