{{-- Livewire 파일 업로드 관리자 메인 뷰 --}}
<div x-data="{ showGuidelines: true }">
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
                    <div class="text-lg font-semibold text-gray-900">{{ $maxFileSize }}MB / 파일</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 업로드 단계별 컴포넌트 --}}
    @if($currentStep === 'upload' || $currentStep === 'selected')
        {{-- 드롭존 컴포넌트 --}}
        @include('frontend.007-screen-file-upload.002-upload-dropzone')
        
        {{-- 파일 리스트 컴포넌트 --}}
        @include('frontend.007-screen-file-upload.003-file-list')
    @endif

    {{-- 업로드 진행 컴포넌트 --}}
    @include('frontend.007-screen-file-upload.004-upload-progress')

    {{-- 업로드 완료 컴포넌트 --}}
    @include('frontend.007-screen-file-upload.005-upload-complete')

    {{-- 가이드라인 컴포넌트 --}}
    <div x-show="showGuidelines" x-transition class="mt-6">
        @include('frontend.007-screen-file-upload.006-upload-guidelines')
        
        {{-- 가이드라인 토글 버튼 --}}
        <div class="text-center mt-4">
            <button 
                type="button" 
                @click="showGuidelines = false"
                class="text-sm text-gray-500 hover:text-gray-700 underline"
            >
                가이드라인 숨기기
            </button>
        </div>
    </div>
    
    {{-- 가이드라인 다시 보기 버튼 --}}
    <div x-show="!showGuidelines" class="text-center mt-6">
        <button 
            type="button" 
            @click="showGuidelines = true"
            class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
            📋 업로드 가이드라인 보기
        </button>
    </div>
</div>

{{-- Alpine.js가 필요한 경우 --}}
@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush