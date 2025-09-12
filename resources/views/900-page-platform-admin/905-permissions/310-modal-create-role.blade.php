{{-- 새 역할 생성 모달 --}}
<div id="create-role-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">새 역할 생성</h3>
                <button type="button" onclick="closeCreateRoleModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">역할명</label>
                    <input type="text" placeholder="예: 프로젝트 관리자"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">설명</label>
                    <textarea rows="3" placeholder="역할에 대한 설명을 입력하세요"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">권한 선택</label>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">사용자 관리</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">조직 목록</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">프로젝트 관리</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeCreateRoleModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        취소
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                        생성
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
