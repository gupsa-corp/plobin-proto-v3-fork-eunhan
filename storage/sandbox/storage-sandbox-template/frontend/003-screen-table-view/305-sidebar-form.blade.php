<!-- 프로젝트 기본 정보 -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">프로젝트명</label>
    <input type="text"
           x-model="selectedProject.name"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">설명</label>
    <textarea x-model="selectedProject.description"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">상태</label>
    <select x-model="selectedProject.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        <option value="planned">계획</option>
        <option value="in_progress">진행 중</option>
        <option value="completed">완료</option>
        <option value="on_hold">보류</option>
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">진행률 (%)</label>
    <input type="range"
           x-model="selectedProject.progress"
           min="0" max="100"
           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
    <div class="flex justify-between text-sm text-gray-500 mt-1">
        <span>0%</span>
        <span x-text="selectedProject.progress + '%'" class="font-medium text-blue-600"></span>
        <span>100%</span>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">팀원 수</label>
    <input type="number"
           x-model="selectedProject.team_members"
           min="1"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">우선순위</label>
    <select x-model="selectedProject.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
        <option value="low">낮음</option>
        <option value="medium">보통</option>
        <option value="high">높음</option>
    </select>
</div>

<!-- 동적 필드들 -->
<?php if(isset($dynamicColumns) && !empty($dynamicColumns)): ?>
    <?php foreach ($dynamicColumns as $column): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <?= htmlspecialchars($column['column_label']) ?>
                <?php if ($column['is_required']): ?>
                    <span class="text-red-500">*</span>
                <?php endif; ?>
            </label>

            <?php if ($column['display_type'] === 'textarea'): ?>
                <textarea
                    x-model="selectedProject.custom_<?= $column['column_name'] ?>"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="내용을 입력하세요"
                    <?php if ($column['is_required']): ?>required<?php endif; ?>>
                </textarea>

            <?php elseif ($column['display_type'] === 'select'): ?>
                <select
                    x-model="selectedProject.custom_<?= $column['column_name'] ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    <?php if ($column['is_required']): ?>required<?php endif; ?>>
                    <option value="">선택하세요</option>
                    <?php
                    $options = json_decode($column['options'], true);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
                        }
                    }
                    ?>
                </select>

            <?php elseif ($column['display_type'] === 'checkbox'): ?>
                <div class="flex items-center">
                    <input type="checkbox"
                           x-model="selectedProject.custom_<?= $column['column_name'] ?>"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label class="ml-2 block text-sm text-gray-900">
                        <?= htmlspecialchars($column['column_label']) ?>
                    </label>
                </div>

            <?php elseif ($column['display_type'] === 'date'): ?>
                <input type="date"
                       x-model="selectedProject.custom_<?= $column['column_name'] ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       <?php if ($column['is_required']): ?>required<?php endif; ?>>

            <?php elseif ($column['display_type'] === 'number'): ?>
                <input type="number"
                       x-model="selectedProject.custom_<?= $column['column_name'] ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       <?php if ($column['is_required']): ?>required<?php endif; ?>>

            <?php else: // 기본 input
            ?>
                <input type="text"
                       x-model="selectedProject.custom_<?= $column['column_name'] ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                       placeholder="값을 입력하세요"
                       <?php if ($column['is_required']): ?>required<?php endif; ?>>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">클라이언트</label>
    <input type="text"
           x-model="selectedProject.client"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">예산 (원)</label>
    <input type="number"
           x-model="selectedProject.budget"
           min="0"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
</div>

<!-- 추가 정보 -->
<div class="bg-gray-50 p-4 rounded-lg">
    <h4 class="text-sm font-medium text-gray-700 mb-3">프로젝트 정보</h4>
    <div class="space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">프로젝트 ID:</span>
            <span class="text-gray-900" x-text="selectedProject.id"></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">카테고리:</span>
            <span class="text-gray-900" x-text="selectedProject.category || '-'"></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">시작일:</span>
            <span class="text-gray-900" x-text="selectedProject.start_date || '-'"></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">종료일:</span>
            <span class="text-gray-900" x-text="selectedProject.end_date || '-'"></span>
        </div>
    </div>
</div>
