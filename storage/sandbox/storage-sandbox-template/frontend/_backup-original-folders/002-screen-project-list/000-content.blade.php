{{-- 샌드박스 프로젝트 목록 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?><div class="min-h-screen bg-gray-50 p-6">
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="text-green-600">📝</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">프로젝트 목록</h1>
                    <p class="text-gray-600">모든 프로젝트를 관리하고 추적하세요</p>
                </div>
            </div>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                새 프로젝트 추가
            </button>
        </div>
    </div>

    {{-- 필터 및 검색 --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4">
                <div class="relative">
                    <input type="text" placeholder="프로젝트 검색..." 
                           class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg">
                    <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                </div>
                <select class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option>모든 상태</option>
                    <option>진행 중</option>
                    <option>완료</option>
                    <option>보류</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">{{ $totalProjects ?? 15 }}개 프로젝트</span>
            </div>
        </div>
    </div>

    {{-- 프로젝트 카드들 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @for($i = 1; $i <= 9; $i++)
            @php 
                $statuses = ['진행 중', '완료', '보류', '계획'];
                $status = $statuses[array_rand($statuses)];
                $progress = rand(20, 100);
                $colors = [
                    '진행 중' => 'bg-blue-100 text-blue-800',
                    '완료' => 'bg-green-100 text-green-800',
                    '보류' => 'bg-yellow-100 text-yellow-800',
                    '계획' => 'bg-gray-100 text-gray-800'
                ];
            @endphp
            <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">프로젝트 {{ $i }}</h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colors[$status] }}">
                        {{ $status }}
                    </span>
                </div>
                
                <p class="text-gray-600 text-sm mb-4">
                    프로젝트 {{ $i }}에 대한 상세 설명입니다. 이 프로젝트는 현재 {{ $status }} 상태입니다.
                </p>
                
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">진행률</span>
                        <span class="text-gray-500">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-gray-300 rounded-full"></div>
                        <span class="text-sm text-gray-600">팀 멤버 {{ rand(2, 8) }}명</span>
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded">보기</button>
                        <button class="px-3 py-1 text-sm text-green-600 hover:bg-green-50 rounded">편집</button>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="text-xs text-gray-500">
                        생성일: {{ now()->subDays(rand(1, 30))->format('Y-m-d') }}
                    </div>
                </div>
            </div>
        @endfor
    </div>

    {{-- 페이지네이션 --}}
    <div class="mt-8 flex justify-center">
        <div class="flex space-x-2">
            <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">이전</button>
            <button class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg">1</button>
            <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
            <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
            <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">다음</button>
        </div>
    </div>
</div>