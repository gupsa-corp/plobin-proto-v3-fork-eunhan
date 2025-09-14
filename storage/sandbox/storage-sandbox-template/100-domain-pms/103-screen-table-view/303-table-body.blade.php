        <tbody class="bg-white divide-y divide-gray-200">
            <?php
                $statusColors = [
                    'planned' => 'bg-purple-100 text-purple-800',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'on_hold' => 'bg-yellow-100 text-yellow-800'
                ];
                
                $statusLabels = [
                    'planned' => '계획',
                    'in_progress' => '진행 중',
                    'completed' => '완료',
                    'on_hold' => '보류'
                ];
                
                $priorityColors = [
                    'high' => 'bg-red-100 text-red-800',
                    'medium' => 'bg-yellow-100 text-yellow-800',
                    'low' => 'bg-green-100 text-green-800'
                ];
                
                $priorityLabels = [
                    'high' => '높음',
                    'medium' => '보통',
                    'low' => '낮음'
                ];
            ?>
            
            <?php if(empty($projectsData)): ?>
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                        검색 결과가 없습니다.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($projectsData as $project): ?>
                    <tr class="hover:bg-gray-50">
                        
<?php include storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/' . basename(dirname(dirname(dirname(__DIR__)))) . '/100-domain-pms/103-screen-table-view/303-table-row.blade.php'); ?>
                        
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>