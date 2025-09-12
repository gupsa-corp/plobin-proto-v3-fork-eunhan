<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include('900-page-platform-admin.900-common.901-layout-head', ['title' => '조직 포인트 관리'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('900-page-platform-admin.902-organizations.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">⭐ 조직 포인트 관리</h1>
                
                <!-- 필터 섹션 -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="GET" class="flex gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">검색</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                                   placeholder="조직명으로 검색..." 
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">최소 포인트</label>
                            <input type="number" name="points_min" value="{{ $filters['points_min'] ?? '' }}" 
                                   placeholder="0" 
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm w-24">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">최대 포인트</label>
                            <input type="number" name="points_max" value="{{ $filters['points_max'] ?? '' }}" 
                                   placeholder="1000000" 
                                   class="px-3 py-2 border border-gray-300 rounded-md text-sm w-24">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                            필터 적용
                        </button>
                    </form>
                </div>

                <!-- 조직 포인트 목록 -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">조직별 포인트 현황</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">조직</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">현재 잔액</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">멤버수</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">생성일</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($organizations ?? [] as $organization)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $organization->name }}</div>
                                                <div class="text-sm text-gray-500">ID: {{ $organization->id }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $organization->getFormattedPointsBalance() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $organization->members_count }}명</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $organization->created_at->format('Y.m.d') }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <a href="{{ route('platform.admin.organizations.points.detail', $organization) }}" 
                                               class="text-blue-600 hover:text-blue-900 mr-3">상세보기</a>
                                            <button onclick="showAdjustModal({{ $organization->id }})" 
                                                    class="text-green-600 hover:text-green-900">조정</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            조직이 없습니다.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($organizations) && $organizations->hasPages())
                        <div class="px-6 py-3 border-t border-gray-200">
                            {{ $organizations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 포인트 조정 모달 (기본 구조) -->
    <div id="adjustModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">포인트 조정</h3>
                <form id="adjustForm">
                    @csrf
                    <input type="hidden" id="organizationId" name="organization_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">조정 포인트</label>
                        <input type="number" name="amount" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                               placeholder="양수면 적립, 음수면 차감">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">사유</label>
                        <input type="text" name="description" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md" 
                               placeholder="조정 사유를 입력하세요">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideAdjustModal()" 
                                class="px-4 py-2 text-gray-500 hover:text-gray-700">취소</button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">적용</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAdjustModal(organizationId) {
            document.getElementById('organizationId').value = organizationId;
            document.getElementById('adjustModal').classList.remove('hidden');
        }

        function hideAdjustModal() {
            document.getElementById('adjustModal').classList.add('hidden');
        }
    </script>

    @livewireScripts
</body>
</html>