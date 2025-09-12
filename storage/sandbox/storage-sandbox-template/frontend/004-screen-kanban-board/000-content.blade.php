{{-- 샌드박스 칸반 보드 템플릿 --}}
<?php 
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?><div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-50 p-6" 
     x-data="kanbanData()" 
     x-init="loadKanbanBoards()"
     x-cloak>
    {{-- 헤더 --}}
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <span class="text-purple-600">📋</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">칸반 보드</h1>
                    <p class="text-gray-600">프로젝트 진행 상황을 시각적으로 관리하세요</p>
                </div>
            </div>
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">새 카드 추가</button>
        </div>
    </div>

    {{-- 칸반 보드 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div x-show="loading" class="col-span-full text-center py-12">
            <div class="text-gray-500">칸반 보드를 로딩 중...</div>
        </div>
        
        <template x-for="board in boards" :key="board.column.id">
            <div class="bg-gray-100 rounded-lg p-4 min-h-96">
                {{-- 칼럼 헤더 --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full" 
                             :class="getColumnColorClass(board.column.color)"></div>
                        <h3 class="font-semibold text-gray-900" x-text="board.column.title"></h3>
                    </div>
                    <span class="bg-gray-200 text-gray-600 text-sm px-2 py-1 rounded-full" 
                          x-text="board.count"></span>
                </div>

                {{-- 카드들 --}}
                <div class="space-y-3">
                    <template x-for="card in board.cards" :key="card.id">
                        <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-move"
                             @click="selectCard(card)"
                             :draggable="true"
                             @dragstart="startDrag($event, card)"
                             @dragover.prevent
                             @drop="handleDrop($event, board.column.id)">
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-900" x-text="card.title">
                                </h4>
                                <span class="text-xs px-2 py-1 rounded-full" 
                                      :class="getPriorityClass(card.priority)"
                                      x-text="getPriorityText(card.priority)">
                                </span>
                            </div>
                            
                            <p class="text-xs text-gray-600 mb-3" 
                               x-text="card.description || '설명이 없습니다.'">
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1">
                                    <div class="w-5 h-5 bg-gray-300 rounded-full"></div>
                                    <span class="text-xs text-gray-500" x-text="card.assignee || '미배정'"></span>
                                </div>
                                <div class="text-xs text-gray-400" x-text="formatDate(card.updated_at)">
                                </div>
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-100" x-show="card.progress > 0">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-600">진행률</span>
                                    <span class="text-gray-500" x-text="card.progress + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1">
                                    <div class="bg-blue-500 h-1 rounded-full" :style="`width: ${card.progress}%`"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    {{-- 새 카드 추가 버튼 --}}
                    <button @click="showAddCardModal(board.column.id)" 
                            class="w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-400 hover:border-gray-400 hover:text-gray-600 text-sm">
                        + 새 카드 추가
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- 안내 메시지 --}}
    <div class="mt-8 bg-white rounded-lg p-4 border border-blue-200">
        <div class="flex items-center space-x-2 text-blue-700">
            <span>💡</span>
            <span class="text-sm">카드를 드래그하여 다른 칼럼으로 이동할 수 있습니다.</span>
        </div>
    </div>
</div>

<script>
function kanbanData() {
    return {
        boards: [],
        loading: false,
        draggedCard: null,
        
        async loadKanbanBoards() {
            this.loading = true;
            try {
                const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/kanban/boards');
                const result = await response.json();
                
                if (result.success && result.data) {
                    this.boards = result.data.boards;
                } else {
                    console.error('Kanban API 오류:', result.message);
                    this.boards = [];
                }
            } catch (error) {
                console.error('칸반 보드 로딩 실패:', error);
                this.boards = [];
            } finally {
                this.loading = false;
            }
        },
        
        startDrag(event, card) {
            this.draggedCard = card;
            event.dataTransfer.effectAllowed = 'move';
        },
        
        async handleDrop(event, columnId) {
            event.preventDefault();
            if (!this.draggedCard) return;
            
            // 카드가 같은 컬럼으로 이동하는 경우 무시
            if (this.draggedCard.column_id === columnId) return;
            
            try {
                // API를 통해 카드 상태 업데이트
                const statusMap = {
                    'todo': 'pending',
                    'in-progress': 'in-progress', 
                    'review': 'review',
                    'done': 'completed'
                };
                
                const response = await fetch(`/api/sandbox/storage-sandbox-template/backend/api.php/kanban/cards/${this.draggedCard.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        status: statusMap[columnId] || columnId,
                        column_id: columnId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // UI에서 카드 이동
                    this.moveCardInUI(this.draggedCard, columnId);
                } else {
                    console.error('카드 이동 실패:', result.message);
                    alert('카드 이동에 실패했습니다.');
                }
            } catch (error) {
                console.error('카드 이동 오류:', error);
                alert('카드 이동 중 오류가 발생했습니다.');
            }
            
            this.draggedCard = null;
        },
        
        moveCardInUI(card, targetColumnId) {
            // 원래 컬럼에서 카드 제거
            this.boards.forEach(board => {
                const cardIndex = board.cards.findIndex(c => c.id === card.id);
                if (cardIndex !== -1) {
                    board.cards.splice(cardIndex, 1);
                    board.count = board.cards.length;
                }
            });
            
            // 타겟 컬럼에 카드 추가
            const targetBoard = this.boards.find(b => b.column.id === targetColumnId);
            if (targetBoard) {
                card.column_id = targetColumnId;
                targetBoard.cards.push(card);
                targetBoard.count = targetBoard.cards.length;
            }
        },
        
        selectCard(card) {
            console.log('카드 선택:', card);
            // 카드 상세 보기 모달 등을 구현할 수 있음
        },
        
        showAddCardModal(columnId) {
            console.log('새 카드 추가:', columnId);
            // 새 카드 추가 모달을 구현할 수 있음
        },
        
        getColumnColorClass(color) {
            const colorClasses = {
                'blue': 'bg-blue-400',
                'yellow': 'bg-yellow-400',
                'purple': 'bg-purple-400',
                'green': 'bg-green-400'
            };
            return colorClasses[color] || 'bg-gray-400';
        },
        
        getPriorityClass(priority) {
            const priorityClasses = {
                'high': 'bg-red-100 text-red-600',
                'medium': 'bg-yellow-100 text-yellow-600',
                'low': 'bg-green-100 text-green-600',
                'normal': 'bg-gray-100 text-gray-600'
            };
            return priorityClasses[priority] || 'bg-gray-100 text-gray-600';
        },
        
        getPriorityText(priority) {
            const priorityTexts = {
                'high': '높음',
                'medium': '보통',
                'low': '낮음',
                'normal': '일반'
            };
            return priorityTexts[priority] || priority || '일반';
        },
        
        formatDate(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            return date.toLocaleDateString('ko-KR', { month: 'numeric', day: 'numeric' });
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>