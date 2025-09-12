{{-- 샌드박스 대시보드 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
    {{-- 헤더 --}}
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl">📊</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">프로젝트 대시보드</h1>
                        <p class="text-gray-600">실시간 프로젝트 현황을 한눈에 확인하세요</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">마지막 업데이트</div>
                    <div class="text-lg font-semibold text-gray-900">{{ date('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 통계 카드들 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">전체 프로젝트</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalProjects ?? 15 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="text-blue-600">📁</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500">+12%</span>
                <span class="text-gray-500 ml-1">지난 달 대비</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">진행 중</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeProjects ?? 8 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-green-600">⚡</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500">+5</span>
                <span class="text-gray-500 ml-1">이번 주</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">완료</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $completedProjects ?? 23 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-purple-600">✅</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500">+3</span>
                <span class="text-gray-500 ml-1">이번 주</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">팀 멤버</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $teamMembers ?? 42 }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <span class="text-orange-600">👥</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-500">+7</span>
                <span class="text-gray-500 ml-1">지난 달 대비</span>
            </div>
        </div>
    </div>

    {{-- 최근 활동 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">최근 활동</h3>
            <div class="space-y-4">
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 text-sm">{{ $i }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">프로젝트 {{ $i }} 업데이트</p>
                            <p class="text-xs text-gray-500">{{ rand(1, 30) }}분 전</p>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">프로젝트 진행률</h3>
            <div class="space-y-4">
                @for($i = 1; $i <= 5; $i++)
                    @php $progress = rand(30, 95); @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">프로젝트 {{ $i }}</span>
                            <span class="text-gray-500">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>