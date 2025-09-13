<div class="container-fluid px-6 py-8">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('message') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">총 Cron Job</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_jobs'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">활성 Job</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['active_jobs'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">24시간 실행</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_runs_24h'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 rounded-full">
                    <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">성공률</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['success_rate'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                    <input wire:model.live="searchTerm" 
                           type="text" 
                           placeholder="Job 이름, 설명, 대상 검색..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상태 필터</label>
                    <select wire:model.live="statusFilter" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">전체</option>
                        <option value="active">활성</option>
                        <option value="inactive">비활성</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">타입 필터</label>
                    <select wire:model.live="typeFilter" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">전체</option>
                        <option value="url">URL</option>
                        <option value="command">Command</option>
                        <option value="class">Class</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button wire:click="$refresh" 
                            class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        새로고침
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cron Jobs Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job 정보</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">스케줄</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">타입</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">다음 실행</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">최근 실행</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">액션</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($jobs as $job)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $job->name }}</div>
                                @if($job->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($job->description, 50) }}</div>
                                @endif
                                <div class="text-xs text-gray-400">{{ Str::limit($job->target, 40) }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-mono">{{ $job->schedule }}</div>
                            <div class="text-xs text-gray-500">{{ $job->schedule_description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $job->type === 'url' ? 'bg-blue-100 text-blue-800' : 
                                   ($job->type === 'command' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ strtoupper($job->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="toggleJobStatus({{ $job->id }})" 
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $job->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $job->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($job->next_run_at)
                                {{ $job->next_run_at->format('Y-m-d H:i') }}
                            @else
                                <span class="text-gray-400">없음</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($job->logs->count() > 0)
                                @php $latestLog = $job->logs->first() @endphp
                                <div class="text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $latestLog->status === 'success' ? 'bg-green-100 text-green-800' : 
                                           ($latestLog->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($latestLog->status) }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $latestLog->created_at->format('m-d H:i') }}
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">실행 없음</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button wire:click="viewJobDetails({{ $job->id }})" 
                                        class="text-blue-600 hover:text-blue-900">
                                    상세
                                </button>
                                <button wire:click="runJobManually({{ $job->id }})" 
                                        class="text-green-600 hover:text-green-900">
                                    실행
                                </button>
                                <button wire:click="deleteJob({{ $job->id }})"
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('정말 삭제하시겠습니까?')">
                                    삭제
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            등록된 Cron Job이 없습니다.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($jobs->hasPages())
        <div class="mt-6">
            {{ $jobs->links() }}
        </div>
    @endif

    <!-- Job Details Modal -->
    @if($selectedJob)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Cron Job 상세 정보</h3>
                        <button wire:click="closeJobDetails" 
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Job Info -->
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">기본 정보</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">이름</dt>
                                    <dd class="text-sm text-gray-900">{{ $selectedJob->name }}</dd>
                                </div>
                                @if($selectedJob->description)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">설명</dt>
                                        <dd class="text-sm text-gray-900">{{ $selectedJob->description }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">스케줄</dt>
                                    <dd class="text-sm text-gray-900">{{ $selectedJob->schedule }} ({{ $selectedJob->schedule_description }})</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">타입</dt>
                                    <dd class="text-sm text-gray-900">{{ strtoupper($selectedJob->type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">대상</dt>
                                    <dd class="text-sm text-gray-900 break-all">{{ $selectedJob->target }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Job Statistics -->
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-3">실행 통계</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">총 성공</dt>
                                    <dd class="text-sm text-gray-900">{{ $selectedJob->success_count }}회</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">총 실패</dt>
                                    <dd class="text-sm text-gray-900">{{ $selectedJob->failure_count }}회</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">마지막 실행</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $selectedJob->last_run_at ? $selectedJob->last_run_at->format('Y-m-d H:i:s') : '없음' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">다음 실행</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $selectedJob->next_run_at ? $selectedJob->next_run_at->format('Y-m-d H:i:s') : '없음' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Recent Logs -->
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-700 mb-3">최근 실행 로그</h4>
                        <div class="bg-gray-50 rounded-lg p-4 max-h-60 overflow-y-auto">
                            @if($selectedJob->logs->count() > 0)
                                @foreach($selectedJob->logs as $log)
                                    <div class="flex justify-between items-start py-2 border-b border-gray-200 last:border-b-0">
                                        <div class="flex-1">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $log->status === 'success' ? 'bg-green-100 text-green-800' : 
                                                   ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                            <span class="text-xs text-gray-500 ml-2">
                                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                                            </span>
                                            @if($log->duration_ms)
                                                <span class="text-xs text-gray-500 ml-2">
                                                    ({{ $log->duration_ms }}ms)
                                                </span>
                                            @endif
                                        </div>
                                        @if($log->error_message)
                                            <div class="text-xs text-red-600 mt-1">{{ $log->error_message }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500 text-sm">실행 로그가 없습니다.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>