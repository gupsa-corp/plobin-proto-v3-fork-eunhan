{{-- 업로드 진행 상황 컴포넌트 --}}
@if($currentStep === 'progress')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span class="mr-2">⏳</span>
            업로드 진행 상황
        </h3>
        
        <div class="space-y-3 mb-6">
            @foreach($files as $index => $file)
                <div class="mb-3">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-700 flex items-center">
                            <span class="mr-2">{{ $this->getFileIcon($file->getClientOriginalName()) }}</span>
                            {{ $file->getClientOriginalName() }}
                        </span>
                        <span class="text-gray-500 font-medium">
                            {{ $uploadProgress[$index] ?? 0 }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            class="bg-green-500 h-2 rounded-full transition-all duration-500 ease-out"
                            style="width: {{ $uploadProgress[$index] ?? 0 }}%"
                        ></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $this->formatFileSize($file->getSize()) }}
                        @if(isset($uploadProgress[$index]) && $uploadProgress[$index] == 100)
                            <span class="text-green-600 ml-2">✅ 완료</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- 전체 진행률 --}}
        <div class="pt-4 border-t border-gray-200">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span class="font-medium">전체 진행률</span>
                <span class="font-semibold text-gray-900">{{ $overallProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                <div 
                    class="bg-green-500 h-3 rounded-full transition-all duration-500 ease-out"
                    style="width: {{ $overallProgress }}%"
                ></div>
            </div>
            <div class="flex justify-between items-center text-xs text-gray-500">
                <span>{{ count($files) }}개 파일 업로드 중...</span>
                <span>{{ $this->getTotalSize() }}</span>
            </div>
        </div>
        
        {{-- 업로드 취소 버튼 --}}
        <div class="mt-4 text-center">
            <button 
                type="button"
                wire:click="newUpload"
                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm"
                @if($overallProgress > 50) disabled @endif
            >
                @if($overallProgress > 50)
                    업로드 완료 대기 중...
                @else
                    업로드 취소
                @endif
            </button>
        </div>
    </div>

    {{-- 실시간 업데이트를 위한 스크립트 --}}
    <div wire:poll.500ms="calculateOverallProgress"></div>
</div>
@endif