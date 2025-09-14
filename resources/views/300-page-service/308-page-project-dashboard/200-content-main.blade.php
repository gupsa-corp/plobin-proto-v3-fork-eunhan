@php
    // 컨트롤러에서 전달받은 변수들 사용
    $organization = $organization ?? null;
    $project = $project ?? null;
    $page = $page ?? null;
    $customScreen = $customScreen ?? null;
    $sandboxInfo = $sandboxInfo ?? null;
    
    $organizationId = $organization ? $organization->id : null;
    $projectId = $project ? $project->id : null;
    $pageId = $page ? $page->id : null;

    // 샌드박스 정보는 컨트롤러에서 처리된 데이터 사용
    $hasSandbox = $sandboxInfo['has_sandbox'] ?? false;
    $hasCustomScreen = $sandboxInfo['has_custom_screen'] ?? false;
    $sandboxName = $sandboxInfo['sandbox_name'] ?? null;
    $sandboxLevel = $sandboxInfo['sandbox_level'] ?? null;
    $customScreenFolder = $sandboxInfo['custom_screen_folder'] ?? null;
@endphp

<!-- 페이지별 커스텀 콘텐츠 -->
<div class="px-6 py-6" x-data="">
    @if(!$organization)
        <!-- 조직이 없는 경우 -->
        @include('300-page-service.308-page-project-dashboard.210-error-organization-not-found')
    @elseif(!$project)
        <!-- 프로젝트가 없는 경우 -->
        @include('300-page-service.308-page-project-dashboard.211-error-project-not-found')
    @elseif(isset($customScreen) && !empty($customScreen))
        <!-- 커스텀 화면이 있는 경우 렌더링 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            @php
                // 샌드박스 정보 준비
                $sandboxInfo = [
                    'sandbox_name' => $sandboxName ?? 'unknown',
                    'has_sandbox' => true,
                    'has_custom_screen' => true,
                    'custom_screen_folder' => $customScreenFolder ?? $customScreen['screen'] ?? 'unknown',
                ];

                // 조직, 프로젝트, 페이지 ID 준비
                $organizationId = $organization->id ?? null;
                $projectId = $project->id ?? null;
                $pageId = $page->id ?? null;
            @endphp
            @include('300-page-service.308-page-project-dashboard.221-custom-screen-content', [
                'customScreen' => $customScreen,
                'sandboxInfo' => $sandboxInfo,
                'organizationId' => $organizationId,
                'projectId' => $projectId,
                'pageId' => $pageId
            ])
        </div>
    @elseif($page && $hasCustomScreen && $customScreenFolder)
        <!-- sandbox_custom_screen_folder 기반 커스텀 화면 렌더링 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            @php
                // storage/sandbox 경로에서 실제 파일 찾기 (도메인 폴더 포함)
                $customScreenFilePath = null;
                $sandboxPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/' . $sandboxName);

                // 디버깅 정보
                $debugInfo = [
                    'sandboxName' => $sandboxName,
                    'customScreenFolder' => $customScreenFolder,
                    'sandboxPath' => $sandboxPath,
                    'sandboxPathExists' => is_dir($sandboxPath),
                    'domainFolders' => [],
                    'testedPaths' => []
                ];

                // 도메인 폴더들을 스캔해서 해당 스크린 폴더가 있는지 확인
                if (is_dir($sandboxPath)) {
                    $domainFolders = glob($sandboxPath . '/*-domain-*', GLOB_ONLYDIR);
                    $debugInfo['domainFolders'] = array_map('basename', $domainFolders);

                    foreach ($domainFolders as $domainFolder) {
                        $testPath = $domainFolder . '/' . trim($customScreenFolder, '/') . '/000-content.blade.php';
                        $debugInfo['testedPaths'][] = $testPath;
                        if (file_exists($testPath)) {
                            $customScreenFilePath = $testPath;
                            break;
                        }
                    }
                }

                // 도메인 폴더에서 찾지 못했다면 기존 방식으로 시도 (하위 호환성)
                if (!$customScreenFilePath) {
                    $fallbackPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/' . $sandboxName . '/' . trim($customScreenFolder, '/') . '/000-content.blade.php');
                    $debugInfo['fallbackPath'] = $fallbackPath;
                    $debugInfo['fallbackExists'] = file_exists($fallbackPath);
                    if (file_exists($fallbackPath)) {
                        $customScreenFilePath = $fallbackPath;
                    }
                }
            @endphp

            @if($customScreenFilePath && file_exists($customScreenFilePath))
                {!! view()->file($customScreenFilePath, get_defined_vars())->render() !!}
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                커스텀 화면 파일을 찾을 수 없습니다
                            </p>
                            <div class="mt-2 text-xs text-gray-600">
                                <strong>디버깅 정보:</strong><br>
                                샌드박스명: {{ $debugInfo['sandboxName'] }}<br>
                                스크린폴더: {{ $debugInfo['customScreenFolder'] }}<br>
                                샌드박스 경로: {{ $debugInfo['sandboxPath'] }}<br>
                                경로 존재: {{ $debugInfo['sandboxPathExists'] ? 'Yes' : 'No' }}<br>
                                발견된 도메인: {{ implode(', ', $debugInfo['domainFolders']) }}<br>
                                시도한 경로들:<br>
                                @foreach($debugInfo['testedPaths'] as $path)
                                    - {{ $path }}<br>
                                @endforeach
                                @if(isset($debugInfo['fallbackPath']))
                                    Fallback 경로: {{ $debugInfo['fallbackPath'] }} (존재: {{ $debugInfo['fallbackExists'] ? 'Yes' : 'No' }})<br>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif($page && $hasSandbox)
        <!-- 일반 샌드박스만 설정된 경우 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            @include('300-page-service.308-page-project-dashboard.231-sandbox-content')
        </div>
    @else
        <!-- 빈 페이지 안내 -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            @include('300-page-service.308-page-project-dashboard.241-empty-page-content', [
                'organizationId' => $organizationId,
                'projectId' => $projectId,
                'pageId' => $pageId,
                'page' => $page
            ])
        </div>
    @endif
</div>

<!-- JavaScript 에러 처리 -->
@include('300-page-service.308-page-project-dashboard.400-javascript-error-handling')
