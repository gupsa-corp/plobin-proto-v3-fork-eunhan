<td class="px-6 py-4 whitespace-nowrap">
    <input type="checkbox" class="rounded" value="<?= $project['id'] ?>">
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <div class="flex items-center">
        <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
            <span class="text-gray-600 text-sm"><?= $project['id'] ?></span>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($project['name']) ?></div>
            <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($project['description'], 0, 100)) ?><?= strlen($project['description']) > 100 ? '...' : '' ?></div>
            <?php if(!empty($project['category'])): ?>
                <div class="text-xs text-blue-600 mt-1">#<?= $project['category'] ?></div>
            <?php endif; ?>
        </div>
    </div>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $statusColors[$project['status']] ?? 'bg-gray-100 text-gray-800' ?>">
        <?= $statusLabels[$project['status']] ?? $project['status'] ?>
    </span>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <div class="w-full bg-gray-200 rounded-full h-2">
        <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $project['progress'] ?>%"></div>
    </div>
    <div class="text-xs text-gray-500 mt-1"><?= $project['progress'] ?>%</div>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <div class="flex items-center">
        <div class="w-8 h-8 bg-gray-300 rounded-full mr-2"></div>
        <div class="text-sm text-gray-900"><?= $project['team_members'] ?>명</div>
    </div>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
    <?php if($project['start_date']): ?>
        <?= date('Y-m-d', strtotime($project['start_date'])) ?>
        <?php if($project['end_date']): ?>
            <br><small>~ <?= date('Y-m-d', strtotime($project['end_date'])) ?></small>
        <?php endif; ?>
    <?php else: ?>
        <?= date('Y-m-d', strtotime($project['created_at'])) ?>
    <?php endif; ?>
</td>
<td class="px-6 py-4 whitespace-nowrap">
    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $priorityColors[$project['priority']] ?? 'bg-gray-100 text-gray-800' ?>">
        <?= $priorityLabels[$project['priority']] ?? $project['priority'] ?>
    </span>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
    <?= htmlspecialchars($project['client']) ?: '-' ?>
    <?php if($project['budget'] > 0): ?>
        <br><small class="text-green-600">₩<?= number_format($project['budget']) ?></small>
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
                })">상세</button>
        <button class="text-green-600 hover:text-green-900"
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
        <button class="text-red-600 hover:text-red-900" onclick="confirm('정말 삭제하시겠습니까?')">삭제</button>
    </div>
</td>
