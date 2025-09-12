<!-- 컬럼 선택 드롭다운 -->
<div class="relative" x-data="{ open: false }">
    <button type="button" 
            @click="open = !open"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <span>컬럼 설정</span>
        <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <div x-show="open" 
         x-transition
         @click.outside="open = false"
         class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
        <div class="p-4">
            <h3 class="font-medium text-gray-900 mb-3">표시할 컬럼 선택</h3>
            
            <!-- 필수 컬럼들 -->
            <div class="space-y-2 mb-3">
                <h4 class="text-xs text-gray-500 uppercase tracking-wider mb-2">필수 컬럼 (항상 표시)</h4>
                
                <label class="flex items-center opacity-50">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="name" 
                           checked 
                           disabled>
                    <span class="text-sm">프로젝트명</span>
                    <span class="ml-auto text-xs text-red-500">*</span>
                </label>
                
                <label class="flex items-center opacity-50">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="status" 
                           checked 
                           disabled>
                    <span class="text-sm">상태</span>
                    <span class="ml-auto text-xs text-red-500">*</span>
                </label>
                
                <label class="flex items-center opacity-50">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="start_date" 
                           checked 
                           disabled>
                    <span class="text-sm">시작일</span>
                    <span class="ml-auto text-xs text-red-500">*</span>
                </label>
            </div>
            
            <!-- 선택적 기본 컬럼들 -->
            <div class="space-y-2 mb-3">
                <h4 class="text-xs text-gray-500 uppercase tracking-wider mb-2">기본 컬럼</h4>
                
                <label class="flex items-center">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="progress" 
                           checked>
                    <span class="text-sm">진행률</span>
                </label>
                
                <label class="flex items-center">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="team_members" 
                           checked>
                    <span class="text-sm">팀 멤버</span>
                </label>
                
                <label class="flex items-center">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="priority" 
                           checked>
                    <span class="text-sm">우선순위</span>
                </label>
                
                <label class="flex items-center">
                    <input type="checkbox" 
                           class="rounded text-purple-600 mr-2" 
                           data-column="client" 
                           checked>
                    <span class="text-sm">클라이언트</span>
                </label>
            </div>
            
            <!-- 동적 컬럼들 -->
            <?php if (!empty($dynamicColumns)): ?>
                <div class="border-t pt-3 mb-3">
                    <h4 class="text-xs text-gray-500 uppercase tracking-wider mb-2">사용자 정의 컬럼</h4>
                    <?php foreach ($dynamicColumns as $column): ?>
                        <label class="flex items-center mb-2">
                            <input type="checkbox" 
                                   class="rounded text-purple-600 mr-2" 
                                   data-column="custom_<?= $column['column_name'] ?>" 
                                   checked>
                            <span class="text-sm"><?= htmlspecialchars($column['column_label']) ?></span>
                            <?php if ($column['is_required']): ?>
                                <span class="text-red-500 ml-1">*</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- 버튼들 -->
            <div class="border-t pt-3 flex justify-between">
                <button type="button" 
                        onclick="resetColumns()"
                        class="text-sm text-gray-600 hover:text-gray-800">
                    초기화
                </button>
                <button type="button" 
                        @click="open = false; saveAllColumnSettings()"
                        class="px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700">
                    완료
                </button>
            </div>
        </div>
    </div>
</div>