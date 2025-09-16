<!-- 샌드박스 선택 시 커스텀 화면 설정 폼 -->
<div x-show="sandboxSelected">
    <form action="{{ route('project.dashboard.page.settings.custom-screen.post', ['id' => request()->route('id'), 'projectId' => request()->route('projectId'), 'pageId' => request()->route('pageId')]) }}" method="POST" class="space-y-6" id="customScreenForm">
        @csrf

        <!-- 커스텀 화면 사용 안함 옵션 -->
        <div>
            <!-- 새로고침 버튼 -->
            <div class="flex items-center p-3 border border-blue-200 rounded-lg bg-blue-25">
                <div class="flex-1">
                    <div class="font-medium text-blue-700 text-sm">화면을 새로 만들거나 배포했나요?</div>
                    <div class="text-xs text-blue-600">새로고침하여 최신 화면 목록을 확인하세요.</div>
                </div>
                <button type="button"
                        @click="location.reload()"
                        class="ml-3 inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    새로고침
                </button>&nbsp;
                <button
                    type="button"
                    @click="handleSave()"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    저장
                </button>
            </div>

            @include('300-page-service.311-page-settings-custom-screen.006-custom-screen-options')
        </div>
        <!-- 하위 페이지 생성 확인을 위한 숨겨진 필드 -->
        <input type="hidden" name="create_sub_pages" id="create_sub_pages" value="no">
    </form>
</div>