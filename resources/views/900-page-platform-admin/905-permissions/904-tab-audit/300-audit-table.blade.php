{{-- 권한 로그 테이블 --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">시간</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업자</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">대상 사용자</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">변경 내용</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">결과</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-09-07 10:45</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">김관리자</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">박조직장</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    역할 변경: 일반사용자 → 조직관리자
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        성공
                    </span>
                </td>
            </tr>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2024-09-07 09:30</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">김관리자</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">이사용자</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    권한 부여: 조직 생성 권한 추가
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        성공
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>