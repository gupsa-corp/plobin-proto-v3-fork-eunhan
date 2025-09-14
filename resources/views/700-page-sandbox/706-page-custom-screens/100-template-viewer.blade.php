<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include($common . '.301-layout-head', ['title' => '템플릿 미리보기 - ' . $customScreen['title']])

<body class="bg-gray-100">
    <!-- 템플릿 화면용 드롭다운 네비게이션만 표시 -->
    <div class="bg-white border-b border-gray-200">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-3">
            @include('700-page-sandbox.700-common.402-template-screen-header', [
                'sandboxName' => $sandboxName,
                'customScreen' => $customScreen
            ])
        </div>
    </div>
    
    <div class="min-h-screen bg-gray-50">
        <div class="w-full">
            <!-- 템플릿 콘텐츠 렌더링 -->
            <div class="template-content">
                {!! $templateContent !!}
            </div>
        </div>
    </div>
    
    <!-- Alpine.js for interactivity -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Filament Scripts -->
    @filamentScripts
</body>
</html>