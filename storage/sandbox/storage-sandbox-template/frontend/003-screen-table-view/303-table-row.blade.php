<td class="px-6 py-4 whitespace-nowrap">
    <input type="checkbox" class="rounded" value="<?= $project['id'] ?>">
</td>
<td class="px-6 py-4 whitespace-nowrap" @click.stop data-column="name">
    <div class="flex items-center">
        <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
            <span class="text-gray-600 text-sm"><?= $project['id'] ?></span>
        </div>
        <div class="flex-1">
            <div class="text-sm font-medium text-gray-900 editable-field" 
                 data-field="name" 
                 data-project-id="<?= $project['id'] ?>"
                 @click="startEdit($event)"><?= htmlspecialchars($project['name']) ?></div>
            <div class="text-sm text-gray-500 editable-field" 
                 data-field="description" 
                 data-project-id="<?= $project['id'] ?>"
                 @click="startEdit($event)"><?= htmlspecialchars(substr($project['description'], 0, 100)) ?><?= strlen($project['description']) > 100 ? '...' : '' ?></div>
            <?php if(!empty($project['category'])): ?>
                <div class="text-xs text-blue-600 mt-1">#<?= $project['category'] ?></div>
            <?php endif; ?>
        </div>
    </div>
</td>
<td class="px-6 py-4 whitespace-nowrap" @click.stop data-column="status">
    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusColors[$project['status']] ?? 'bg-gray-100 text-gray-800' ?> editable-select cursor-pointer hover:opacity-75"
          data-field="status" 
          data-project-id="<?= $project['id'] ?>"
          data-options='["planned", "in_progress", "completed", "on_hold"]'
          data-labels='{"planned": "계획", "in_progress": "진행 중", "completed": "완료", "on_hold": "보류"}'
          @click="startSelectEdit($event)">
        <?= $statusLabels[$project['status']] ?? $project['status'] ?>
    </span>
</td>
<td class="px-6 py-4 whitespace-nowrap" @click.stop data-column="progress">
    <div class="w-full bg-gray-200 rounded-full h-2">
        <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $project['progress'] ?>%"></div>
    </div>
    <div class="text-xs text-gray-500 mt-1 editable-field cursor-pointer hover:text-gray-700" 
         data-field="progress" 
         data-project-id="<?= $project['id'] ?>"
         @click="startEdit($event)"><?= $project['progress'] ?>%</div>
</td>
<td class="px-6 py-4 whitespace-nowrap" @click.stop data-column="team_members">
    <div class="flex items-center">
        <div class="w-8 h-8 bg-gray-300 rounded-full mr-2"></div>
        <div class="text-sm text-gray-900 editable-field cursor-pointer hover:text-gray-700" 
             data-field="team_members" 
             data-project-id="<?= $project['id'] ?>"
             @click="startEdit($event)"><?= $project['team_members'] ?>명</div>
    </div>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="start_date">
    <?php if($project['start_date']): ?>
        <?= date('Y-m-d', strtotime($project['start_date'])) ?>
        <?php if($project['end_date']): ?>
            <br><small>~ <?= date('Y-m-d', strtotime($project['end_date'])) ?></small>
        <?php endif; ?>
    <?php else: ?>
        <?= date('Y-m-d', strtotime($project['created_at'])) ?>
    <?php endif; ?>
</td>
<td class="px-6 py-4 whitespace-nowrap" @click.stop data-column="priority">
    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $priorityColors[$project['priority']] ?? 'bg-gray-100 text-gray-800' ?> editable-select cursor-pointer hover:opacity-75"
          data-field="priority" 
          data-project-id="<?= $project['id'] ?>"
          data-options='["low", "medium", "high"]'
          data-labels='{"low": "낮음", "medium": "보통", "high": "높음"}'
          @click="startSelectEdit($event)">
        <?= $priorityLabels[$project['priority']] ?? $project['priority'] ?>
    </span>
</td>
<?php foreach ($dynamicColumns ?? [] as $column):
    // 해당 프로젝트의 커스텀 데이터 조회
    $customValue = '';
    if (isset($project['custom_data']) && is_array($project['custom_data'])) {
        $customValue = $project['custom_data'][$column['column_name']] ?? '';
    }
?>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" @click.stop data-column="custom_<?= $column['column_name'] ?>">
    <?php if ($column['display_type'] === 'checkbox'): ?>
        <input type="checkbox" 
               <?= $customValue ? 'checked' : '' ?> 
               class="rounded cursor-pointer editable-checkbox"
               data-field="custom_<?= $column['column_name'] ?>" 
               data-project-id="<?= $project['id'] ?>"
               @change="updateCustomField($event)">
    <?php elseif ($column['display_type'] === 'date' && !empty($customValue)): ?>
        <span class="editable-field cursor-pointer hover:text-gray-700"
              data-field="custom_<?= $column['column_name'] ?>" 
              data-project-id="<?= $project['id'] ?>"
              data-type="date"
              @click="startEdit($event)"><?= date('Y-m-d', strtotime($customValue)) ?></span>
    <?php else: ?>
        <span class="editable-field cursor-pointer hover:text-gray-700"
              data-field="custom_<?= $column['column_name'] ?>" 
              data-project-id="<?= $project['id'] ?>"
              @click="startEdit($event)"><?= htmlspecialchars($customValue ?: '-') ?></span>
    <?php endif; ?>
</td>
<?php endforeach; ?>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" @click.stop data-column="client">
    <div class="editable-field cursor-pointer hover:text-gray-700" 
         data-field="client" 
         data-project-id="<?= $project['id'] ?>"
         @click="startEdit($event)"><?= htmlspecialchars($project['client']) ?: '-' ?></div>
    <?php if($project['budget'] > 0): ?>
        <div class="editable-field cursor-pointer hover:text-green-700 text-green-600" 
             data-field="budget" 
             data-project-id="<?= $project['id'] ?>"
             @click="startEdit($event)">₩<?= number_format($project['budget']) ?></div>
    <?php endif; ?>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium" @click.stop>
    <div class="flex space-x-2">
        <button class="text-blue-600 hover:text-blue-900"
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
                })">편집</button>
        <button class="text-red-600 hover:text-red-900" onclick="deleteProject(<?= $project['id'] ?>, '<?= addslashes($project['name']) ?>')">삭제</button>
    </div>
</td>
