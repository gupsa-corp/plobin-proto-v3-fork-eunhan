{{-- 업로드 완료 컴포넌트 --}}
@if($currentStep === 'complete')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="text-center">
            <div class="mb-6">
                <span class="text-6xl">✅</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">업로드 완료!</h3>
            <p class="text-gray-600 mb-6">
                {{ count($uploadedFiles) }}개 파일이 성공적으로 업로드되었습니다.
            </p>
            
            {{-- 업로드된 파일 목록 --}}
            @if(!empty($uploadedFiles))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-green-800 mb-3 flex items-center justify-center">
                        <span class="mr-2">📁</span>
                        업로드된 파일
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($uploadedFiles as $file)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center space-x-2 text-green-700">
                                    <span>{{ $this->getFileIcon($file['original_name']) }}</span>
                                    <span>{{ $file['original_name'] }}</span>
                                </div>
                                <span class="text-green-600">{{ $this->formatFileSize($file['size']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            {{-- 통계 정보 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm text-gray-600">업로드 파일</div>
                    <div class="text-lg font-semibold text-gray-900">{{ count($uploadedFiles) }}개</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm text-gray-600">총 용량</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ $this->formatFileSize(array_sum(array_column($uploadedFiles, 'size'))) }}
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm text-gray-600">업로드 시간</div>
                    <div class="text-lg font-semibold text-gray-900">
                        {{ now()->format('H:i:s') }}
                    </div>
                </div>
            </div>
            
            {{-- 액션 버튼 --}}
            <div class="flex justify-center space-x-4">
                <button 
                    type="button" 
                    wire:click="newUpload"
                    class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2"
                >
                    <span>🔄</span>
                    <span>새 업로드</span>
                </button>
                <button 
                    type="button" 
                    onclick="window.location.href='{{ url('/file-list') }}'"
                    class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center space-x-2"
                >
                    <span>📂</span>
                    <span>파일 목록 보기</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 추가 액션 --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mt-6">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                파일들이 안전하게 저장되었습니다
            </div>
            <div class="flex space-x-2">
                <button 
                    type="button"
                    onclick="window.print()"
                    class="text-xs px-3 py-1 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                >
                    업로드 내역 인쇄
                </button>
                <button 
                    type="button"
                    onclick="navigator.share ? navigator.share({title: '파일 업로드 완료', text: '{{ count($uploadedFiles) }}개 파일이 업로드되었습니다.'}) : null"
                    class="text-xs px-3 py-1 text-gray-600 border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                >
                    공유
                </button>
            </div>
        </div>
    </div>
</div>
@endif