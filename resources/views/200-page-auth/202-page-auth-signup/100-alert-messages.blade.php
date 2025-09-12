{{-- ========================================
     메시지 표시 섹션 (상단 알림)
     ======================================== --}}
@if (session()->has('success'))
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">성공!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if (session()->has('error'))
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">오류!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif