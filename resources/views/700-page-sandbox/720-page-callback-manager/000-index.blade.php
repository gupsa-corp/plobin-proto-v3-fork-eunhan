<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include($common . '.301-layout-head', ['title' => 'Callback 관리자'])
<body class="bg-gray-100">
    @include('700-page-sandbox.700-common.400-sandbox-header')

    <div class="min-h-screen sandbox-container">
        <div class="sandbox-card">
            <!-- Page Header -->
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">🔗 Callback 관리자</h1>
                    <p class="text-gray-600">웹훅과 콜백 URL 관리 및 테스트</p>
                </div>
                <button 
                    x-data="" 
                    @click="$dispatch('open-modal', { type: 'create' })" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                >
                    새 Callback 추가
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button 
                        x-data="{ active: true }"
                        :class="{ 'border-purple-500 text-purple-600': active, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': !active }"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                    >
                        Callback 목록
                    </button>
                    <button 
                        x-data="{ active: false }"
                        :class="{ 'border-purple-500 text-purple-600': active, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': !active }"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                    >
                        요청 로그
                    </button>
                    <button 
                        x-data="{ active: false }"
                        :class="{ 'border-purple-500 text-purple-600': active, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': !active }"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                    >
                        테스트 도구
                    </button>
                </nav>
            </div>

            <!-- Main Content -->
            <div x-data="callbackManager()" x-init="init()">
                <!-- Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">활성 Callback</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="stats.active">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">총 요청</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="stats.total">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">성공</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="stats.success">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">실패</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="stats.failed">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Callbacks Table -->
                <div class="bg-white border border-gray-200 rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Callback URL 목록</h3>
                            <div class="flex space-x-3">
                                <button 
                                    @click="loadCallbacks()" 
                                    class="text-gray-500 hover:text-gray-700"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이름</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">메소드</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">최근 테스트</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="callback in callbacks" :key="callback.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900" x-text="callback.name"></div>
                                                <div class="text-sm text-gray-500" x-text="callback.description"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 break-all" x-text="callback.url"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="callback.method === 'POST' ? 'bg-blue-100 text-blue-800' : callback.method === 'GET' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                x-text="callback.method"
                                            ></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="callback.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                x-text="callback.status === 'active' ? '활성' : '비활성'"
                                            ></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="callback.last_test || '-'"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button @click="testCallback(callback)" class="text-blue-600 hover:text-blue-900">테스트</button>
                                                <button @click="editCallback(callback)" class="text-purple-600 hover:text-purple-900">편집</button>
                                                <button 
                                                    @click="toggleCallback(callback)" 
                                                    :class="callback.status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                                                    x-text="callback.status === 'active' ? '비활성화' : '활성화'"
                                                ></button>
                                                <button @click="deleteCallback(callback)" class="text-red-600 hover:text-red-900">삭제</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div x-show="callbacks.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Callback이 없습니다</h3>
                        <p class="mt-1 text-sm text-gray-500">새 Callback URL을 추가하여 시작하세요.</p>
                        <div class="mt-6">
                            <button 
                                @click="openCreateModal()" 
                                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors"
                            >
                                첫 번째 Callback 만들기
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div x-show="testResult" class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">테스트 결과</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-900" x-text="testResult"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div 
        x-data="{ show: false, isEdit: false }" 
        x-show="show" 
        @open-modal.window="show = $event.detail.type === 'create'; isEdit = false"
        @edit-modal.window="show = true; isEdit = true"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        style="display: none;"
    >
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg bg-white rounded-lg shadow-lg">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="isEdit ? 'Callback 편집' : '새 Callback 추가'"></h3>
                
                <form>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">이름</label>
                        <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Callback 이름을 입력하세요">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
                        <textarea class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="3" placeholder="Callback 설명"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                        <input type="url" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="https://example.com/webhook">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">HTTP 메소드</label>
                        <select class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="POST">POST</option>
                            <option value="GET">GET</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">헤더</label>
                        <textarea class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="4" placeholder='{"Content-Type": "application/json", "Authorization": "Bearer token"}'></textarea>
                        <p class="text-xs text-gray-500 mt-1">JSON 형식으로 입력</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">테스트 페이로드</label>
                        <textarea class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="4" placeholder='{"event": "test", "data": {"message": "Hello World"}}'></textarea>
                        <p class="text-xs text-gray-500 mt-1">JSON 형식으로 입력</p>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-purple-600" checked>
                            <span class="ml-2 text-sm text-gray-900">Callback 활성화</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="show = false" 
                            type="button" 
                            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            취소
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                        >
                            저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function callbackManager() {
            return {
                callbacks: [],
                stats: {
                    active: 0,
                    total: 0,
                    success: 0,
                    failed: 0
                },
                testResult: '',
                
                init() {
                    this.loadCallbacks();
                    this.loadStats();
                },
                
                loadCallbacks() {
                    // 샘플 데이터
                    this.callbacks = [
                        {
                            id: 1,
                            name: '결제 완료 알림',
                            description: '결제가 완료되었을 때 호출되는 웹훅',
                            url: 'https://api.example.com/webhooks/payment-complete',
                            method: 'POST',
                            status: 'active',
                            last_test: '2024-01-15 14:30:00'
                        },
                        {
                            id: 2,
                            name: '사용자 등록 알림',
                            description: '새 사용자가 등록되었을 때 호출',
                            url: 'https://api.example.com/webhooks/user-registered',
                            method: 'POST',
                            status: 'active',
                            last_test: '2024-01-15 10:15:00'
                        },
                        {
                            id: 3,
                            name: '오류 알림',
                            description: '시스템 오류 발생 시 알림',
                            url: 'https://hooks.slack.com/services/error-notification',
                            method: 'POST',
                            status: 'inactive',
                            last_test: null
                        }
                    ];
                },
                
                loadStats() {
                    this.stats = {
                        active: 2,
                        total: 47,
                        success: 42,
                        failed: 5
                    };
                },
                
                openCreateModal() {
                    this.$dispatch('open-modal', { type: 'create' });
                },
                
                editCallback(callback) {
                    this.$dispatch('edit-modal', { callback });
                },
                
                toggleCallback(callback) {
                    callback.status = callback.status === 'active' ? 'inactive' : 'active';
                },
                
                deleteCallback(callback) {
                    if (confirm('정말로 이 Callback을 삭제하시겠습니까?')) {
                        this.callbacks = this.callbacks.filter(c => c.id !== callback.id);
                    }
                },
                
                testCallback(callback) {
                    // 테스트 실행 시뮬레이션
                    this.testResult = `테스트 진행 중...`;
                    
                    setTimeout(() => {
                        const isSuccess = Math.random() > 0.3;
                        if (isSuccess) {
                            this.testResult = `✅ 테스트 성공
URL: ${callback.url}
응답 코드: 200 OK
응답 시간: 245ms
응답 본문: {"status": "success", "message": "Webhook received"}`;
                        } else {
                            this.testResult = `❌ 테스트 실패
URL: ${callback.url}
오류 코드: 500 Internal Server Error
오류 메시지: Connection timeout after 30s`;
                        }
                        
                        callback.last_test = new Date().toLocaleString('ko-KR');
                    }, 2000);
                }
            }
        }
    </script>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Filament Scripts -->
    @filamentScripts
</body>
</html>