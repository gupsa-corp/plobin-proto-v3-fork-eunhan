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
                    <tr class="hover:bg-gray-50 cursor-pointer" 
                        @click="openSidebar({
                            id: <?= $project['id'] ?>,
                            name: '<?= addslashes($project['name']) ?>',
                            description: '<?= addslashes($project['description']) ?>',
                            status: '<?= $project['status'] ?>',
                            progress: <?= $project['progress'] ?>,
                            team_members: <?= $project['team_members'] ?>,
                            priority: '<?= $project['priority'] ?>',
                            client: '<?= addslashes($project['client']) ?>',
                            budget: <?= $project['budget'] ?>,
                            start_date: '<?= $project['start_date'] ?>',
                            end_date: '<?= $project['end_date'] ?>',
                            category: '<?= $project['category'] ?>'
                        })"
                        title="클릭하여 편집">
                        
<?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/303-table-row.blade.php'); ?>
                        
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>