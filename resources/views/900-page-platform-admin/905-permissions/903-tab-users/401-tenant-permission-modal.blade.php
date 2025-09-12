{{-- 테넌트별 권한 관리 모달 --}}
<div id="tenantPermissionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="tenant-permission-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="tenant-permission-title">
                            조직별 권한 관리
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                <span id="selectedUserNameTenant"></span>의 조직별 권한을 관리합니다.
                            </p>
                        </div>

                        <div class="mt-4 space-y-4">
                            {{-- 기존 조직 권한 목록 --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">현재 조직 권한</h4>
                                <div id="currentTenantPermissions" class="space-y-2">
                                    {{-- 동적으로 채워짐 --}}
                                </div>
                            </div>

                            {{-- 새 조직 권한 추가 --}}
                            <div class="border-t pt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">새 조직 권한 추가</h4>
                                <div class="flex space-x-3">
                                    <div class="flex-1">
                                        <label for="newOrganization" class="block text-sm font-medium text-gray-700">조직</label>
                                        <select id="newOrganization" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="">조직 선택</option>
                                            @foreach($organizations as $organization)
                                                <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <label for="newPermissionLevel" class="block text-sm font-medium text-gray-700">권한 레벨</label>
                                        <select id="newPermissionLevel" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="">권한 선택</option>
                                            <option value="0">초대됨</option>
                                            <option value="100">사용자</option>
                                            <option value="150">고급 사용자</option>
                                            <option value="200">서비스 매니저</option>
                                            <option value="250">선임 서비스 매니저</option>
                                            <option value="300">조직 관리자</option>
                                            <option value="350">선임 조직 관리자</option>
                                            <option value="400">조직 소유자</option>
                                            <option value="450">조직 창립자</option>
                                        </select>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" onclick="addTenantPermission()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            추가
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="saveTenantPermissions()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    저장
                </button>
                <button type="button" onclick="closeTenantPermissionModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>
