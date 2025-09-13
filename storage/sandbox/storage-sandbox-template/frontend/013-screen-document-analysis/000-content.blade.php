{{-- AI 문서 에셋 분석 결과 화면 --}}
<?php 
    $commonPath = dirname(__DIR__, 2) . '/common.php';
    require_once $commonPath;
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
    
    // URL에서 file_id 파라미터 가져오기
    $fileId = $_GET['file_id'] ?? null;
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100 p-6" 
     x-data="documentAnalysisData(<?= intval($fileId) ?>)" 
     x-init="init()"
     x-cloak>
    
    {{-- 헤더 --}}
    <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center">
                        <span class="text-white text-xl">🧠</span>
                    </div>
                    <div>
                        <div class="flex items-center space-x-4">
                            <h1 class="text-2xl font-bold text-gray-900">AI 문서 분석 결과</h1>
                            <div class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full" x-text="documentVersion"></div>
                        </div>
                        <p class="text-gray-600">팔란티어 온톨로지 기반 에셋 분류 및 분석</p>
                        <div class="flex items-center space-x-3 mt-2">
                            <p x-show="documentData.file" class="text-sm text-indigo-600" x-text="documentData.file?.original_name"></p>
                            <div class="flex items-center space-x-2">
                                <label for="file-selector" class="text-xs text-gray-500">파일 선택:</label>
                                <select id="file-selector" 
                                        @change="changeFile($event.target.value)"
                                        :value="fileId"
                                        class="text-xs bg-white border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <template x-for="(name, id) in fileNames" :key="id">
                                        <option :value="id" x-text="`${id}. ${name}`" :selected="id == fileId"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right space-y-2">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <label for="json-version-selector" class="text-xs text-gray-500">JSON 버전:</label>
                            <select id="json-version-selector" 
                                    @change="loadJsonVersion($event.target.value)"
                                    :value="currentJsonVersion"
                                    class="text-xs bg-white border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-indigo-500">
                                <template x-for="version in availableJsonVersions" :key="version.id">
                                    <option :value="version.id" x-text="version.name"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="showJsonManager = true" 
                                class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                            📁 JSON 관리
                        </button>
                        <button @click="saveCurrentJson()" 
                                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                            💾 저장
                        </button>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">섹션 표시</div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">1-30</span>
                            <span class="text-sm font-medium text-indigo-600" x-text="`${displayedSections || 30}개`"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JSON 관리 모달 --}}
    <div x-show="showJsonManager" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click="showJsonManager = false">
        
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden"
             @click.stop>
            
            {{-- 모달 헤더 --}}
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">📁</span>
                        <h2 class="text-xl font-bold">JSON 데이터 관리</h2>
                    </div>
                    <button @click="showJsonManager = false" 
                            class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- 모달 내용 --}}
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                
                {{-- 저장 섹션 --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="text-green-500 mr-2">💾</span>
                        현재 데이터 저장
                    </h3>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="flex items-center space-x-4 mb-3">
                            <input type="text" 
                                   x-model="saveFileName" 
                                   placeholder="파일명을 입력하세요 (예: 프로젝트_분석_v1)"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <button @click="saveToLocalStorage()" 
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center space-x-2">
                                <span>💾</span>
                                <span>로컬 저장</span>
                            </button>
                            <button @click="downloadCurrentJson()" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center space-x-2">
                                <span>⬇️</span>
                                <span>다운로드</span>
                            </button>
                        </div>
                        <p class="text-sm text-gray-600">
                            JSON 버전: <span class="font-medium text-green-700" x-text="currentJsonVersion"></span> | 
                            문서 버전: <span class="font-medium text-green-700" x-text="documentVersion"></span> | 
                            파일: <span class="font-medium text-green-700" x-text="fileNames[fileId]"></span> |
                            섹션 수: <span class="font-medium text-green-700" x-text="documentData.assets?.length || 0"></span>개
                        </p>
                    </div>
                </div>
                
                {{-- 불러오기 섹션 --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="text-blue-500 mr-2">📂</span>
                        저장된 데이터 불러오기
                    </h3>
                    
                    {{-- 로컬 저장소 목록 --}}
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-800 mb-3">로컬 저장소</h4>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div x-show="savedJsonFiles.length === 0" class="text-center text-gray-500 py-4">
                                저장된 파일이 없습니다
                            </div>
                            <div x-show="savedJsonFiles.length > 0" class="space-y-2">
                                <template x-for="(file, index) in savedJsonFiles" :key="file.id">
                                    <div class="flex items-center justify-between bg-white p-3 rounded border hover:bg-gray-50">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900" x-text="file.fileName"></div>
                                            <div class="text-sm text-gray-500">
                                                <span x-text="file.version"></span> | 
                                                <span x-text="file.documentVersion || 'v1.0'"></span> | 
                                                <span x-text="file.originalFileName"></span> | 
                                                <span x-text="file.sectionsCount"></span>개 섹션 |
                                                <span x-text="new Date(file.createdAt).toLocaleString('ko-KR')"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button @click="loadFromLocalStorage(file.id)" 
                                                    class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                                불러오기
                                            </button>
                                            <button @click="deleteFromLocalStorage(file.id)" 
                                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                                삭제
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    {{-- 파일 업로드 --}}
                    <div>
                        <h4 class="text-md font-medium text-gray-800 mb-3">파일에서 불러오기</h4>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <input type="file" 
                                   accept=".json"
                                   @change="handleFileUpload($event)"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-600 file:text-white hover:file:bg-yellow-700">
                            <p class="text-sm text-gray-600 mt-2">JSON 파일을 선택하여 데이터를 불러올 수 있습니다</p>
                        </div>
                    </div>
                </div>
                
                {{-- 통계 섹션 --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <span class="text-purple-500 mr-2">📊</span>
                        저장소 통계
                    </h3>
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-purple-600" x-text="savedJsonFiles.length"></div>
                                <div class="text-sm text-gray-600">저장된 파일</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-purple-600" x-text="getTotalStorageSize()"></div>
                                <div class="text-sm text-gray-600">사용 용량 (KB)</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-purple-600" x-text="getUniqueVersionsCount()"></div>
                                <div class="text-sm text-gray-600">버전 종류</div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            {{-- 모달 푸터 --}}
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button @click="clearAllLocalStorage()" 
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                        x-confirm="정말로 모든 저장된 데이터를 삭제하시겠습니까?">
                    🗑️ 전체 삭제
                </button>
                <button @click="showJsonManager = false" 
                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                    닫기
                </button>
            </div>
        </div>
    </div>

    {{-- 로딩 상태 --}}
    <div x-show="isLoading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mx-auto mb-4"></div>
        <p class="text-gray-600">분석 결과를 불러오는 중...</p>
    </div>

    {{-- 에셋이 없는 경우 --}}
    <div x-show="!isLoading && (!documentData.assets || documentData.assets.length === 0)" class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-gray-400 text-2xl">📄</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">분석된 에셋이 없습니다</h3>
        <p class="text-gray-500 mb-4">문서가 아직 분석되지 않았거나 분석에 실패했을 수 있습니다.</p>
        <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            파일 목록으로 돌아가기
        </a>
    </div>

    {{-- 연속된 섹션 표시 (1-30) --}}
    <div x-show="!isLoading && documentData.assets && documentData.assets.length > 0" class="space-y-4">
        
        {{-- 섹션 리스트 --}}
        <template x-for="(asset, index) in documentData.assets.slice(0, 30)" :key="asset.id">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden border-l-4"
                 :class="getAssetBorderColor(asset.asset_type)">
                
                {{-- 섹션 헤더 --}}
                <div class="bg-gray-50 px-6 py-3 border-b">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-lg" x-text="asset.asset_type_icon"></span>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="asset.section_title"></h3>
                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full" 
                                  x-text="asset.asset_type_name"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500" x-text="`섹션 ${index + 1}`"></span>
                            <span x-text="asset.summary?.status_icon"></span>
                        </div>
                    </div>
                </div>
                
                {{-- 섹션 내용 --}}
                <div class="p-6 space-y-4">
                    
                    {{-- 원문 --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <span class="text-blue-500 mr-2">📄</span>
                            원문
                        </h4>
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap" x-text="asset.content"></p>
                        </div>
                    </div>
                    
                    {{-- AI 요약 --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">🤖</span>
                                AI 요약
                            </div>
                            <button @click="toggleEditMode(index, 'ai_summary')" 
                                    class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                                    x-text="isEditing(index, 'ai_summary') ? '취소' : '편집'">
                            </button>
                        </h4>
                        <div class="bg-green-50 p-3 rounded-lg">
                            {{-- 읽기 모드 --}}
                            <p x-show="!isEditing(index, 'ai_summary')" 
                               class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap" 
                               x-text="asset.summary?.ai_summary"></p>
                            
                            {{-- 편집 모드 --}}
                            <div x-show="isEditing(index, 'ai_summary')" class="space-y-3">
                                <textarea x-model="editingContent[index] && editingContent[index]['ai_summary']"
                                          class="w-full p-2 border border-gray-300 rounded resize-vertical min-h-[100px] text-sm"
                                          placeholder="AI 요약을 입력하세요..."></textarea>
                                <div class="flex space-x-2">
                                    <button @click="saveEdit(index, 'ai_summary')" 
                                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                        💾 저장 (새 버전)
                                    </button>
                                    <button @click="cancelEdit(index, 'ai_summary')" 
                                            class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 transition-colors">
                                        ❌ 취소
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- 도움되는 내용 --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <span class="text-purple-500 mr-2">💡</span>
                            도움되는 내용
                        </h4>
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap" x-text="asset.summary?.helpful_content"></p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- 맨 위로 스크롤 버튼 --}}
    <div class="fixed bottom-6 right-6">
        <button @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                class="px-4 py-2 bg-indigo-600 text-white shadow-lg rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>
        </button>
    </div>
</div>

<script>
function documentAnalysisData(fileId) {
    return {
        fileId: fileId,
        isLoading: true,
        documentData: {
            file: null,
            assets: [],
            analysis_progress: 0,
            analysis_status: 'not_analyzed'
        },
        selectedAssetIndex: 0,
        selectedAsset: null,
        fileNames: {
            1: 'AI 기술 동향 보고서 2024.pdf',
            2: '프로젝트 제안서 - 스마트 시티 플랫폼.docx', 
            3: '시장 분석 리포트 - AI 솔루션 트렌드.pdf',
            4: '대규모 시스템 설계서 - 35개 섹션.pdf',
            5: '블록체인 기술 백서 - 30개 챕터.pdf',
            6: '클라우드 네이티브 아키텍처 가이드.pdf',
            7: 'DevOps 베스트 프랙티스 매뉴얼.pdf'
        },
        availableJsonVersions: [
            { id: 'v1', name: 'v1 - 기본 데이터셋' },
            { id: 'v2', name: 'v2 - 확장 데이터셋' },
            { id: 'v3', name: 'v3 - 테스트 데이터셋' }
        ],
        currentJsonVersion: 'v1',
        displayedSections: 30,
        editMode: {
            ai_summary: false,
            helpful_content: false
        },
        editContent: {
            ai_summary: '',
            helpful_content: ''
        },
        // JSON 관리 모달 상태
        showJsonManager: false,
        saveFileName: '',
        savedJsonFiles: [],
        
        // 편집 상태 관리 데이터
        editingStates: {},     // 각 섹션별 편집 상태 (예: {"0_ai_summary": true})
        editingContent: {},    // 편집 중인 임시 내용 (예: {0: {ai_summary: "편집 중인 내용"}})
        
        // 문서 버전 관리
        documentVersion: 'v1.0',
        documentVersionHistory: [],    // 문서 전체 버전 기록
        documentMajorVersion: 1,       // 주 버전 (파일 자체의 큰 변화)
        documentMinorVersion: 0,       // 부 버전 (섹션 편집으로 증가)

        // 초기화
        async init() {
            if (!this.fileId) {
                // 기본값으로 file_id=1 설정
                this.showNotification('파일 ID가 지정되지 않아 기본 파일을 로드합니다.', 'info');
                this.fileId = 1;
                
                // URL에 file_id 파라미터 추가
                const url = new URL(window.location);
                url.searchParams.set('file_id', '1');
                window.history.replaceState({}, '', url);
            }
            
            // 저장된 JSON 파일 목록 로드
            this.loadSavedJsonFiles();
            
            await this.loadDocumentAssets();
        },

        // 문서 에셋 로드 (Mock 데이터 사용)
        async loadDocumentAssets() {
            try {
                this.isLoading = true;
                
                // Mock 데이터 로딩 시뮬레이션
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // Mock 데이터 생성 (fileId에 따라 다른 데이터)
                const mockData = this.generateMockData(this.fileId);
                
                this.documentData.file = mockData.file;
                this.documentData.assets = mockData.assets;
                this.documentData.analysis_progress = 100;
                this.documentData.analysis_status = 'completed';
                
                if (this.documentData.assets && this.documentData.assets.length > 0) {
                    this.selectAsset(0);
                }
                
            } catch (error) {
                console.error('Error loading document assets:', error);
                this.showNotification('에셋 정보를 불러오는데 실패했습니다: ' + error.message, 'error');
            } finally {
                this.isLoading = false;
            }
        },

        // Mock 데이터 생성
        generateMockData(fileId) {
            const mockAssets = {
                1: [ // AI 기술 동향 보고서
                    {
                        id: 1,
                        asset_type: 'introduction',
                        asset_type_name: '서론/개요',
                        asset_type_icon: '🎯',
                        asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: 'AI 기술 개요',
                        order_index: 1,
                        content: '2024년 인공지능 기술은 생성형 AI의 급속한 발전으로 전 산업에 혁신을 가져오고 있습니다. ChatGPT, Claude, Gemini 등 대형 언어 모델의 등장으로 자연어 처리, 코드 생성, 창작 분야에서 인간 수준의 성능을 보여주고 있습니다.\n\n특히 멀티모달 AI 기술의 발전으로 텍스트, 이미지, 음성, 비디오를 통합적으로 처리할 수 있게 되었으며, 이는 기존 비즈니스 프로세스의 근본적인 변화를 이끌고 있습니다.',
                        summary: {
                            id: 1,
                            ai_summary: 'AI 기술이 2024년 생성형 AI 중심으로 급속 발전하며 전 산업에 혁신을 가져오고 있다는 개요입니다. 대형 언어 모델들이 인간 수준의 성능을 보여주며, 멀티모달 AI로 발전하고 있습니다.',
                            helpful_content: '우리 회사도 생성형 AI를 활용한 업무 자동화, 고객 서비스 개선, 콘텐츠 제작 효율화를 즉시 도입할 수 있습니다. 특히 문서 작성, 번역, 요약 업무에서 즉각적인 효과를 볼 수 있습니다.',
                            versions: [
                                { id: 1, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }
                            ],
                            versions_count: 1,
                            analysis_status: 'completed',
                            status_icon: '✅'
                        }
                    },
                    {
                        id: 2,
                        asset_type: 'analysis',
                        asset_type_name: '분석',
                        asset_type_icon: '📊',
                        asset_type_color: 'bg-green-100 text-green-800',
                        section_title: 'AI 시장 분석',
                        order_index: 2,
                        content: '2024년 글로벌 AI 시장 규모는 1,847억 달러로, 전년 대비 37.3% 성장했습니다. 주요 성장 동력은 생성형 AI(45%), 자율주행(28%), 의료 AI(15%), 산업 자동화(12%) 순입니다.\n\n생성형 AI 분야에서는 OpenAI, Anthropic, Google이 선두를 달리고 있으며, 한국 기업들도 네이버 클로바X, 카카오브레인 등을 통해 경쟁력을 확보하고 있습니다.',
                        summary: {
                            id: 2,
                            ai_summary: 'AI 시장이 37.3% 성장하며 생성형 AI가 가장 큰 성장 동력(45%)으로 작용하고 있습니다. 글로벌 기업들과 한국 기업들의 경쟁 구도를 분석했습니다.',
                            helpful_content: '생성형 AI 시장 진입이 가장 유망합니다. 경쟁사 대비 2-3년의 기술 격차가 있어 빠른 투자 결정과 전문 인재 확보가 필요합니다. 네이버, 카카오와의 파트너십도 고려해볼 만합니다.',
                            versions: [
                                { id: 2, version_number: 2, version_display_name: 'v2 (사용자 편집)', edit_type: 'user_edit', is_current: true },
                                { id: 3, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: false }
                            ],
                            versions_count: 2,
                            analysis_status: 'completed',
                            status_icon: '✅'
                        }
                    },
                    {
                        id: 3,
                        asset_type: 'recommendation',
                        asset_type_name: '제안/권고',
                        asset_type_icon: '💡',
                        asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '전략적 제안',
                        order_index: 3,
                        content: 'AI 기술 도입을 위한 3단계 로드맵을 제안합니다:\n\n1단계 (0-6개월): 기존 업무 프로세스 AI 적용\n- 문서 자동화, 번역, 요약\n- 고객 문의 챗봇 구축\n- 데이터 분석 자동화\n\n2단계 (6-18개월): 고객 대면 서비스 AI 고도화\n- 개인화 추천 시스템\n- 음성/영상 기반 서비스\n- 예측 분석 서비스\n\n3단계 (18개월 이후): 신사업 모델 개발\n- AI 기반 새로운 제품/서비스\n- 플랫폼 비즈니스 모델\n- 글로벌 시장 진출',
                        summary: {
                            id: 3,
                            ai_summary: '단계적 AI 도입 전략으로 업무 효율화부터 신사업 개발까지 체계적 접근을 제안합니다. 3단계로 나누어 점진적으로 AI 역량을 확장하는 방안입니다.',
                            helpful_content: '1단계부터 즉시 시작 가능합니다. 문서 자동화, 고객 문의 챗봇부터 시작해 점진적으로 확장하는 것이 현실적입니다. 각 단계별로 ROI 측정과 성과 평가를 통해 다음 단계로 진행하면 됩니다.',
                            versions: [
                                { id: 4, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }
                            ],
                            versions_count: 1,
                            analysis_status: 'completed',
                            status_icon: '✅'
                        }
                    }
                ],
                2: [ // 스마트 시티 플랫폼
                    {
                        id: 4,
                        asset_type: 'introduction',
                        asset_type_name: '서론/개요',
                        asset_type_icon: '🎯',
                        asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '프로젝트 개요',
                        order_index: 1,
                        content: '스마트 시티 플랫폼은 IoT, AI, 빅데이터를 활용하여 도시 인프라를 지능화하고 시민 생활의 질을 향상시키는 통합 솔루션입니다.\n\n본 프로젝트는 교통 최적화, 환경 모니터링, 에너지 관리, 안전 관리 등 4개 핵심 영역을 통합적으로 관리할 수 있는 플랫폼 구축을 목표로 합니다.',
                        summary: {
                            id: 4,
                            ai_summary: 'IoT, AI, 빅데이터를 활용한 스마트 시티 통합 솔루션 제안입니다. 4개 핵심 영역(교통, 환경, 에너지, 안전)을 통합 관리하는 플랫폼 구축이 목표입니다.',
                            helpful_content: '정부의 스마트시티 정책과 완벽히 부합하며, 공공 프로젝트 수주 가능성이 높습니다. 기존 IoT 기술력을 활용할 수 있어 경쟁 우위를 확보할 수 있습니다.',
                            versions: [
                                { id: 5, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }
                            ],
                            versions_count: 1,
                            analysis_status: 'completed',
                            status_icon: '✅'
                        }
                    },
                    {
                        id: 5,
                        asset_type: 'technical_spec',
                        asset_type_name: '기술명세',
                        asset_type_icon: '⚙️',
                        asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '기술 아키텍처',
                        order_index: 2,
                        content: '마이크로서비스 아키텍처 기반으로 교통관리, 환경모니터링, 에너지관리, 안전관리 모듈을 독립적으로 구성하여 확장성과 유지보수성을 확보합니다.\n\n주요 기술 스택:\n- Backend: Spring Boot, Node.js\n- Database: PostgreSQL, MongoDB, InfluxDB\n- Message Queue: Apache Kafka\n- Container: Docker, Kubernetes\n- Monitoring: Prometheus, Grafana',
                        summary: {
                            id: 5,
                            ai_summary: '마이크로서비스 기반의 모듈형 아키텍처로 확장성과 유지보수성을 확보합니다. 현대적인 기술 스택으로 구성되어 있습니다.',
                            helpful_content: '우리의 기존 플랫폼 기술과 완벽히 호환됩니다. 개발팀의 Spring Boot, Docker 경험을 활용할 수 있어 6개월 내 MVP 구축이 가능합니다.',
                            versions: [
                                { id: 6, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }
                            ],
                            versions_count: 1,
                            analysis_status: 'completed',
                            status_icon: '✅'
                        }
                    }
                ],
                3: [ // 시장 분석 리포트
                    {
                        id: 6,
                        asset_type: 'findings',
                        asset_type_name: '주요 발견사항',
                        asset_type_icon: '🔍',
                        asset_type_color: 'bg-indigo-100 text-indigo-800',
                        section_title: '주요 발견사항',
                        order_index: 1,
                        content: 'AI 솔루션 시장에서 한국 기업들의 글로벌 경쟁력은 아직 부족하지만, 특정 분야(제조업 AI, 의료 AI)에서는 세계적 수준의 기술력을 보유하고 있습니다.\n\n특히 삼성, LG, 현대차 등 대기업들의 AI 투자가 활발해지면서 B2B AI 솔루션 수요가 급증하고 있습니다. 2024년 한국 AI 시장 규모는 전년 대비 42% 성장한 12조원에 달할 것으로 예상됩니다.',
                        summary: {
                            id: 6,
                            ai_summary: '한국 AI 기업의 글로벌 경쟁력은 제한적이나 제조업, 의료 분야에서는 강점을 보유하고 있습니다. 대기업들의 AI 투자 증가로 B2B 시장이 급성장하고 있습니다.',
                            helpful_content: '제조업 AI에 집중하여 글로벌 시장 진출 전략을 수립하는 것이 효과적입니다. 대기업들과의 파트너십을 통해 레퍼런스를 확보하고 해외 진출의 발판으로 활용할 수 있습니다.',
                            versions: [
                                { id: 7, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }
                            ],
                            versions_count: 1,
                            analysis_status: 'completed',
                            status_icon: '✅'
                        }
                    }
                ],
                4: [ // 대규모 시스템 설계서 - 35개 섹션
                    // === 1. 개요 (섹션 1-3) ===
                    {
                        id: 100, asset_type: 'introduction', asset_type_name: '서론/개요', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '1. 프로젝트 개요', order_index: 1,
                        content: '본 문서는 차세대 분산형 마이크로서비스 아키텍처 기반의 대규모 전자상거래 플랫폼 설계에 대한 종합적인 가이드입니다.\n\n• 일일 처리량: 1억 건 이상의 거래\n• 동시 사용자: 100만 명\n• 글로벌 서비스: 15개국 동시 운영\n• 99.99% 가용성 보장',
                        summary: { id: 100, ai_summary: '대규모 전자상거래 플랫폼의 시스템 설계서입니다. 마이크로서비스 아키텍처를 기반으로 고가용성과 확장성을 동시에 달성하는 것이 목표입니다.', helpful_content: '현재 우리 시스템도 마이크로서비스로 전환이 필요한 시점입니다. 특히 트래픽 급증에 대비한 확장성 설계가 중요합니다.', versions: [{ id: 100, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 101, asset_type: 'introduction', asset_type_name: '서론/개요', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '2. 비즈니스 요구사항', order_index: 2,
                        content: '글로벌 전자상거래 시장의 성장에 따른 핵심 비즈니스 요구사항을 정의합니다.\n\n• 실시간 재고 관리\n• 개인화된 추천 시스템\n• 다국가 결제 시스템 지원\n• 실시간 주문 추적\n• AI 기반 고객 지원',
                        summary: { id: 101, ai_summary: '글로벌 전자상거래 플랫폼의 핵심 비즈니스 요구사항을 정의합니다. 실시간 처리, 개인화, 글로벌화가 주요 키워드입니다.', helpful_content: '우리도 개인화 추천과 실시간 재고 관리부터 시작하면 됩니다. 특히 AI 기반 고객 지원은 즉시 적용 가능한 영역입니다.', versions: [{ id: 101, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 102, asset_type: 'introduction', asset_type_name: '서론/개개', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '3. 기술적 과제', order_index: 3,
                        content: '대규모 시스템 구축 시 직면하는 주요 기술적 과제들을 분석합니다.\n\n• 데이터 일관성 vs 가용성\n• 분산 트랜잭션 처리\n• 서비스 간 통신 최적화\n• 장애 격리 및 복구\n• 성능 모니터링 및 알림',
                        summary: { id: 102, ai_summary: '대규모 분산 시스템의 핵심 기술적 과제들을 정리합니다. CAP 정리, 분산 트랜잭션, 장애 복구가 주요 이슈입니다.', helpful_content: '현재 우리 시스템의 병목점과 직접 연관됩니다. 특히 분산 트랜잭션과 서비스 간 통신 최적화는 즉시 검토가 필요합니다.', versions: [{ id: 102, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    
                    // === 2. 시스템 아키텍처 (섹션 4-15) ===
                    {
                        id: 103, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '4. 전체 아키텍처 개요', order_index: 4,
                        content: '마이크로서비스 기반의 전체 시스템 아키텍처를 설계합니다.\n\n• API Gateway: Zuul/Kong\n• Service Mesh: Istio\n• Container: Docker + Kubernetes\n• Message Queue: Kafka + RabbitMQ\n• Database: PostgreSQL + MongoDB + Redis\n• Monitoring: Prometheus + Grafana + ELK',
                        summary: { id: 103, ai_summary: '클라우드 네이티브 마이크로서비스 아키텍처의 전체 구조를 제시합니다. 컨테이너 오케스트레이션과 서비스 메시가 핵심입니다.', helpful_content: '우리 인프라 팀의 Kubernetes 경험을 활용할 수 있습니다. Istio 도입을 통해 서비스 간 통신을 체계적으로 관리할 수 있습니다.', versions: [{ id: 103, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 104, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '5. 사용자 서비스', order_index: 5,
                        content: '사용자 관리를 담당하는 마이크로서비스 설계입니다.\n\n• 사용자 인증/인가 (OAuth 2.0 + JWT)\n• 프로필 관리\n• 소셜 로그인 연동\n• 사용자 행동 추적\n• 개인정보 보호 (GDPR 준수)',
                        summary: { id: 104, ai_summary: '사용자 관리 마이크로서비스의 세부 설계입니다. 보안성과 확장성을 동시에 고려한 인증 시스템이 핵심입니다.', helpful_content: 'OAuth 2.0 기반의 인증 시스템은 우리 현재 시스템과 호환됩니다. 특히 소셜 로그인 연동은 사용자 경험 개선에 즉시 도움이 됩니다.', versions: [{ id: 104, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 105, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '6. 상품 서비스', order_index: 6,
                        content: '상품 정보 관리를 위한 마이크로서비스입니다.\n\n• 상품 카탈로그 관리\n• 재고 관리 (실시간 동기화)\n• 가격 정책 엔진\n• 상품 검색 (Elasticsearch)\n• 이미지 및 미디어 관리',
                        summary: { id: 105, ai_summary: '상품 관리의 핵심 기능들을 마이크로서비스로 분리한 설계입니다. 특히 실시간 재고 관리와 검색 최적화에 중점을 둡니다.', helpful_content: 'Elasticsearch 기반 검색은 우리 현재 시스템의 검색 성능을 크게 개선할 수 있습니다. 실시간 재고 동기화도 고객 만족도 향상에 직결됩니다.', versions: [{ id: 105, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 106, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '7. 주문 서비스', order_index: 7,
                        content: '주문 처리의 핵심 로직을 담당합니다.\n\n• 주문 생성 및 검증\n• 재고 예약 (Saga Pattern)\n• 결제 연동\n• 배송 관리\n• 주문 상태 추적',
                        summary: { id: 106, ai_summary: '복잡한 주문 처리 워크플로우를 Saga 패턴으로 구현한 설계입니다. 분산 트랜잭션의 일관성을 보장합니다.', helpful_content: 'Saga 패턴은 우리 현재 주문 시스템의 복잡성을 해결하는 핵심 솔루션입니다. 특히 결제 실패 시 롤백 처리가 안전해집니다.', versions: [{ id: 106, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 107, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '8. 결제 서비스', order_index: 8,
                        content: '안전하고 확장가능한 결제 시스템 설계입니다.\n\n• 다중 결제 수단 지원\n• PCI DSS 준수\n• 결제 게이트웨이 통합\n• fraud Detection\n• 환불 및 취소 처리',
                        summary: { id: 107, ai_summary: '보안성과 확장성을 모두 갖춘 결제 시스템 설계입니다. 규제 준수와 보안이 최우선 고려사항입니다.', helpful_content: 'PCI DSS 준수는 글로벌 서비스에 필수입니다. fraud Detection 시스템은 손실 방지에 직접적인 효과가 있습니다.', versions: [{ id: 107, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 108, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '9. 알림 서비스', order_index: 9,
                        content: '실시간 알림 시스템 설계입니다.\n\n• Push 알림 (FCM/APNs)\n• 이메일 알림\n• SMS 알림\n• 웹소켓 기반 실시간 알림\n• 알림 설정 관리',
                        summary: { id: 108, ai_summary: '멀티채널 알림 시스템으로 사용자 경험을 향상시킵니다. 실시간성과 개인화가 핵심 특징입니다.', helpful_content: '실시간 알림은 사용자 재방문율을 크게 높일 수 있습니다. 특히 주문 상태 알림과 개인화된 상품 추천 알림이 효과적입니다.', versions: [{ id: 108, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 109, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '10. 추천 서비스', order_index: 10,
                        content: 'AI 기반 개인화 추천 시스템입니다.\n\n• 협업 필터링\n• 콘텐츠 기반 필터링\n• 딥러닝 추천 모델\n• 실시간 추천 업데이트\n• A/B 테스트 지원',
                        summary: { id: 109, ai_summary: '머신러닝과 딥러닝을 활용한 고도화된 추천 시스템 설계입니다. 개인화와 실시간성이 핵심입니다.', helpful_content: 'AI 추천 시스템은 매출 증대에 직접적인 영향을 줍니다. 우리 데이터사이언스팀의 역량을 활용할 수 있는 영역입니다.', versions: [{ id: 109, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 110, asset_type: 'analysis', asset_type_name: '분석', asset_type_icon: '📊', asset_type_color: 'bg-green-100 text-green-800',
                        section_title: '11. 데이터베이스 설계', order_index: 11,
                        content: '폴리글랏 퍼시스턴스 패턴을 적용한 데이터베이스 설계입니다.\n\n• PostgreSQL: 트랜잭션 데이터\n• MongoDB: 상품 카탈로그\n• Redis: 캐싱 및 세션\n• Elasticsearch: 검색 및 분석\n• ClickHouse: 실시간 분석',
                        summary: { id: 110, ai_summary: '각 서비스의 특성에 맞는 최적의 데이터베이스를 선택하는 폴리글랏 퍼시스턴스 설계입니다.', helpful_content: '현재 PostgreSQL 중심 아키텍처에서 점진적으로 확장할 수 있습니다. 특히 Redis 캐싱은 즉시 성능 개선 효과를 볼 수 있습니다.', versions: [{ id: 110, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 111, asset_type: 'analysis', asset_type_name: '분석', asset_type_icon: '📊', asset_type_color: 'bg-green-100 text-green-800',
                        section_title: '12. API 설계', order_index: 12,
                        content: 'RESTful API와 GraphQL을 결합한 API 설계입니다.\n\n• REST API: CRUD 작업\n• GraphQL: 복합 쿼리\n• API 버저닝 전략\n• Rate Limiting\n• API 문서화 (OpenAPI)',
                        summary: { id: 111, ai_summary: 'REST와 GraphQL의 장점을 결합한 하이브리드 API 설계입니다. 유연성과 효율성을 동시에 추구합니다.', helpful_content: 'GraphQL은 모바일 앱의 데이터 로딩 효율성을 크게 개선할 수 있습니다. 기존 REST API와의 호환성도 유지됩니다.', versions: [{ id: 111, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 112, asset_type: 'analysis', asset_type_name: '분석', asset_type_icon: '📊', asset_type_color: 'bg-green-100 text-green-800',
                        section_title: '13. 보안 아키텍처', order_index: 13,
                        content: '다층 보안 체계를 구축합니다.\n\n• 네트워크 보안 (VPC, 방화벽)\n• 애플리케이션 보안 (WAF)\n• 데이터 암호화 (TLS, AES)\n• 접근 제어 (RBAC)\n• 보안 모니터링',
                        summary: { id: 112, ai_summary: '심층 방어 전략을 적용한 포괄적인 보안 아키텍처입니다. 네트워크부터 애플리케이션까지 전 계층을 보호합니다.', helpful_content: 'GDPR과 개인정보보호법 준수에 필수적인 보안 체계입니다. 특히 데이터 암호화와 접근 제어는 즉시 강화가 필요합니다.', versions: [{ id: 112, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 113, asset_type: 'analysis', asset_type_name: '분석', asset_type_icon: '📊', asset_type_color: 'bg-green-100 text-green-800',
                        section_title: '14. 캐싱 전략', order_index: 14,
                        content: '다층 캐싱 전략으로 성능을 최적화합니다.\n\n• CDN (CloudFlare/AWS CloudFront)\n• Application Cache (Redis)\n• Database Cache (Query Cache)\n• Browser Cache\n• 캐시 무효화 전략',
                        summary: { id: 113, ai_summary: 'CDN부터 브라우저까지 전체 스택에 걸친 캐싱 전략입니다. 성능과 비용 효율성을 동시에 달성합니다.', helpful_content: 'CDN 도입만으로도 글로벌 사용자 경험을 크게 개선할 수 있습니다. Redis 캐싱은 DB 부하를 50% 이상 줄일 수 있습니다.', versions: [{ id: 113, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 114, asset_type: 'analysis', asset_type_name: '분석', asset_type_icon: '📊', asset_type_color: 'bg-green-100 text-green-800',
                        section_title: '15. 메시징 시스템', order_index: 15,
                        content: '비동기 메시징을 통한 서비스 간 통신입니다.\n\n• Apache Kafka: 이벤트 스트리밍\n• RabbitMQ: 작업 큐\n• 이벤트 소싱 패턴\n• CQRS 패턴\n• Dead Letter Queue',
                        summary: { id: 114, ai_summary: '이벤트 기반 아키텍처의 핵심인 메시징 시스템 설계입니다. 확장성과 복원력을 제공합니다.', helpful_content: 'Kafka는 실시간 데이터 처리에 필수적입니다. 현재 배치 처리 중심의 우리 시스템을 실시간으로 전환할 수 있습니다.', versions: [{ id: 114, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    
                    // === 3. 인프라 및 운영 (섹션 16-25) ===
                    {
                        id: 115, asset_type: 'methodology', asset_type_name: '방법론', asset_type_icon: '🔬', asset_type_color: 'bg-indigo-100 text-indigo-800',
                        section_title: '16. 컨테이너 오케스트레이션', order_index: 16,
                        content: 'Kubernetes 기반 컨테이너 관리 시스템입니다.\n\n• 클러스터 설계 및 관리\n• Pod 스케줄링 전략\n• 서비스 디스커버리\n• 로드 밸런싱\n• 오토스케일링',
                        summary: { id: 115, ai_summary: 'Kubernetes를 활용한 확장가능하고 복원력 있는 컨테이너 인프라 설계입니다.', helpful_content: '우리 DevOps팀의 Kubernetes 역량을 활용하여 인프라 자동화를 크게 개선할 수 있습니다.', versions: [{ id: 115, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 116, asset_type: 'methodology', asset_type_name: '방법론', asset_type_icon: '🔬', asset_type_color: 'bg-indigo-100 text-indigo-800',
                        section_title: '17. CI/CD 파이프라인', order_index: 17,
                        content: 'GitOps 기반의 지속적 통합/배포 시스템입니다.\n\n• 소스코드 관리 (Git)\n• 자동화된 테스트\n• 컨테이너 빌드\n• 스테이징 배포\n• 프로덕션 배포',
                        summary: { id: 116, ai_summary: 'GitOps를 적용한 현대적인 CI/CD 파이프라인 설계입니다. 배포의 안전성과 효율성을 보장합니다.', helpful_content: 'GitOps 도입으로 배포 과정의 투명성과 롤백 능력을 크게 개선할 수 있습니다.', versions: [{ id: 116, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 117, asset_type: 'methodology', asset_type_name: '방법론', asset_type_icon: '🔬', asset_type_color: 'bg-indigo-100 text-indigo-800',
                        section_title: '18. 모니터링 및 로깅', order_index: 18,
                        content: '통합 관측성 플랫폼을 구축합니다.\n\n• Prometheus: 메트릭 수집\n• Grafana: 시각화\n• ELK Stack: 로그 분석\n• Jaeger: 분산 추적\n• AlertManager: 알림',
                        summary: { id: 117, ai_summary: '완전한 관측성을 제공하는 모니터링 시스템입니다. 문제 예방과 신속한 대응이 가능합니다.', helpful_content: '현재 수동으로 확인하는 시스템 상태를 자동화하고, 장애 발생 전에 미리 감지할 수 있습니다.', versions: [{ id: 117, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 118, asset_type: 'methodology', asset_type_name: '방법론', asset_type_icon: '🔬', asset_type_color: 'bg-indigo-100 text-indigo-800',
                        section_title: '19. 재해 복구 계획', order_index: 19,
                        content: '비즈니스 연속성을 위한 재해 복구 시스템입니다.\n\n• 백업 전략 (3-2-1 Rule)\n• 다중 AZ 배포\n• 데이터 복제\n• 장애 시나리오 테스트\n• 복구 시간 목표 (RTO/RPO)',
                        summary: { id: 118, ai_summary: '비즈니스 연속성을 보장하는 포괄적인 재해 복구 계획입니다. 최소한의 다운타임으로 서비스 복구가 가능합니다.', helpful_content: '재해 복구는 비즈니스 연속성에 필수적입니다. 특히 금융 데이터 처리 시 법적 요구사항도 충족해야 합니다.', versions: [{ id: 118, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 119, asset_type: 'methodology', asset_type_name: '방법론', asset_type_icon: '🔬', asset_type_color: 'bg-indigo-100 text-indigo-800',
                        section_title: '20. 성능 최적화', order_index: 20,
                        content: '시스템 전반의 성능을 최적화합니다.\n\n• 응답시간 최적화\n• 처리량 개선\n• 자원 사용률 최적화\n• 병목점 제거\n• 부하 테스트',
                        summary: { id: 119, ai_summary: '시스템 성능을 체계적으로 개선하는 최적화 전략입니다. 사용자 경험과 운영 효율성을 동시에 향상시킵니다.', helpful_content: '성능 최적화는 사용자 만족도와 직결됩니다. 특히 응답시간 1초 단축으로 전환율이 7% 증가합니다.', versions: [{ id: 119, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 120, asset_type: 'findings', asset_type_name: '주요 발견사항', asset_type_icon: '🔍', asset_type_color: 'bg-orange-100 text-orange-800',
                        section_title: '21. 보안 강화 방안', order_index: 21,
                        content: '보안 위협에 대응하는 강화 방안입니다.\n\n• Zero Trust 아키텍처\n• 취약점 스캐닝\n• 침입 탐지 시스템\n• 보안 패치 관리\n• 컴플라이언스 준수',
                        summary: { id: 120, ai_summary: 'Zero Trust 원칙을 적용한 포괄적인 보안 강화 방안입니다. 내부와 외부 위협을 모두 방어합니다.', helpful_content: 'Zero Trust는 원격근무 환경에서 특히 중요합니다. 우리 보안 정책의 전면적인 재검토가 필요합니다.', versions: [{ id: 120, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 121, asset_type: 'findings', asset_type_name: '주요 발견사항', asset_type_icon: '🔍', asset_type_color: 'bg-orange-100 text-orange-800',
                        section_title: '22. 데이터 거버넌스', order_index: 22,
                        content: '데이터 관리 체계를 수립합니다.\n\n• 데이터 품질 관리\n• 메타데이터 관리\n• 데이터 라이프사이클\n• 프라이버시 보호\n• 데이터 카탈로그',
                        summary: { id: 121, ai_summary: '체계적인 데이터 관리를 위한 거버넌스 체계입니다. 데이터 품질과 규제 준수를 동시에 달성합니다.', helpful_content: 'GDPR과 개인정보보호법 준수에 필수적입니다. 데이터 카탈로그로 데이터 검색과 활용도를 크게 개선할 수 있습니다.', versions: [{ id: 121, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 122, asset_type: 'findings', asset_type_name: '주요 발견사항', asset_type_icon: '🔍', asset_type_color: 'bg-orange-100 text-orange-800',
                        section_title: '23. 비용 최적화', order_index: 23,
                        content: '클라우드 비용을 최적화합니다.\n\n• 자원 사용률 모니터링\n• 예약 인스턴스 활용\n• 스팟 인스턴스 활용\n• 자동 스케일링\n• 비용 할당 추적',
                        summary: { id: 122, ai_summary: '클라우드 환경에서의 비용 효율성을 극대화하는 전략입니다. 성능을 유지하면서 비용을 절감합니다.', helpful_content: '예약 인스턴스와 스팟 인스턴스 조합으로 클라우드 비용을 30-50% 절감할 수 있습니다.', versions: [{ id: 122, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 123, asset_type: 'findings', asset_type_name: '주요 발견사항', asset_type_icon: '🔍', asset_type_color: 'bg-orange-100 text-orange-800',
                        section_title: '24. 테스팅 전략', order_index: 24,
                        content: '포괄적인 테스트 전략을 수립합니다.\n\n• 단위 테스트 (90% 커버리지)\n• 통합 테스트\n• E2E 테스트\n• 성능 테스트\n• 카오스 엔지니어링',
                        summary: { id: 123, ai_summary: '품질 보장을 위한 다층 테스팅 전략입니다. 개발 속도와 품질을 동시에 달성합니다.', helpful_content: '카오스 엔지니어링으로 예상치 못한 장애 상황에도 시스템이 안정적으로 동작하도록 보장할 수 있습니다.', versions: [{ id: 123, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 124, asset_type: 'findings', asset_type_name: '주요 발견사항', asset_type_icon: '🔍', asset_type_color: 'bg-orange-100 text-orange-800',
                        section_title: '25. DevOps 문화', order_index: 25,
                        content: 'DevOps 문화와 실천 방법을 정의합니다.\n\n• 협업 체계 구축\n• 자동화 우선 원칙\n• 지속적 개선\n• 실패로부터 학습\n• 측정 기반 의사결정',
                        summary: { id: 124, ai_summary: '기술적 구현뿐만 아니라 조직 문화의 변화를 통해 DevOps를 성공적으로 도입하는 방안입니다.', helpful_content: '문화 변화가 기술 도입보다 더 중요합니다. 점진적이고 체계적인 변화 관리가 성공의 핵심입니다.', versions: [{ id: 124, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    
                    // === 4. 구현 계획 (섹션 26-32) ===
                    {
                        id: 125, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '26. 단계별 구현 로드맵', order_index: 26,
                        content: '3년에 걸친 단계별 구현 계획입니다.\n\n• 1단계 (6개월): 핵심 서비스 구축\n• 2단계 (12개월): 고급 기능 추가\n• 3단계 (18개월): 최적화 및 확장\n• 4단계 (24개월): AI/ML 고도화\n• 5단계 (36개월): 글로벌 확장',
                        summary: { id: 125, ai_summary: '점진적이고 체계적인 3년 구현 로드맵입니다. 비즈니스 가치를 조기에 실현하면서 안정성을 확보합니다.', helpful_content: '1단계부터 즉시 비즈니스 가치를 창출할 수 있습니다. MVP 접근법으로 리스크를 최소화하면서 학습을 극대화합니다.', versions: [{ id: 125, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 126, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '27. 팀 구성 및 역할', order_index: 27,
                        content: '프로젝트 수행을 위한 팀 구성안입니다.\n\n• 아키텍트팀 (3명)\n• 백엔드 개발팀 (8명)\n• 프론트엔드 개발팀 (5명)\n• DevOps팀 (4명)\n• QA팀 (3명)',
                        summary: { id: 126, ai_summary: '총 23명 규모의 cross-functional 팀 구성 제안입니다. 각 팀의 역할과 책임을 명확히 정의합니다.', helpful_content: '현재 팀 구조에서 점진적으로 확장 가능합니다. 특히 DevOps와 QA 역량 강화가 우선 필요합니다.', versions: [{ id: 126, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 127, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '28. 기술 스택 선정', order_index: 28,
                        content: '검증된 기술 스택을 선정합니다.\n\n• 언어: Java, TypeScript, Python\n• 프레임워크: Spring Boot, React, FastAPI\n• 데이터베이스: PostgreSQL, Redis, Elasticsearch\n• 인프라: AWS, Kubernetes, Docker\n• 모니터링: Prometheus, Grafana, ELK',
                        summary: { id: 127, ai_summary: '성숙도와 커뮤니티 지원을 고려한 기술 스택 선정입니다. 학습 곡선과 유지보수성을 동시에 고려했습니다.', helpful_content: '현재 우리 팀의 기술 스택과 높은 호환성을 가집니다. 추가 학습 비용을 최소화하면서 최신 기술을 도입할 수 있습니다.', versions: [{ id: 127, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 128, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '29. 위험 요소 관리', order_index: 29,
                        content: '프로젝트 위험 요소를 식별하고 완화 방안을 마련합니다.\n\n• 기술적 위험: 복잡성, 성능, 보안\n• 일정 위험: 의존성, 리소스\n• 비즈니스 위험: 시장 변화, 규제\n• 조직적 위험: 인력, 문화 변화\n• 완화 방안: MVP, 파일럿, 점진적 전환',
                        summary: { id: 128, ai_summary: '체계적인 위험 관리를 통해 프로젝트 성공 가능성을 높입니다. 예방과 완화에 중점을 둡니다.', helpful_content: 'MVP와 파일럿 프로젝트로 위험을 조기에 식별하고 대응할 수 있습니다. 특히 조직 문화 변화에 충분한 시간을 할당해야 합니다.', versions: [{ id: 128, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 129, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '30. 예산 및 투자', order_index: 30,
                        content: '프로젝트 예산과 투자 계획입니다.\n\n• 초기 구축비: 50억원\n• 연간 운영비: 15억원\n• 인력비: 30억원/년\n• 인프라비: 10억원/년\n• ROI: 3년 내 200% 회수',
                        summary: { id: 129, ai_summary: '총 투자비용과 예상 수익을 분석한 투자 계획서입니다. 3년 내 투자비용 회수가 가능합니다.', helpful_content: '클라우드 전환으로 초기 투자비를 30% 절감할 수 있습니다. 단계적 투자로 리스크를 분산하면서 조기 ROI 실현이 가능합니다.', versions: [{ id: 129, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 130, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '31. 성공 지표 (KPI)', order_index: 31,
                        content: '프로젝트 성공을 측정하는 핵심 지표입니다.\n\n• 기술적 KPI: 응답시간, 가용성, 처리량\n• 비즈니스 KPI: 매출, 전환율, 고객만족도\n• 운영 KPI: 배포 빈도, 복구 시간, 변경 실패율\n• 품질 KPI: 버그율, 보안 취약점, 코드 품질\n• 목표: 99.99% 가용성, 100ms 응답시간',
                        summary: { id: 130, ai_summary: '다각도의 성공 지표를 통해 프로젝트 성과를 객관적으로 평가합니다. 지속적인 개선의 기반을 제공합니다.', helpful_content: '명확한 KPI 설정으로 팀의 목표 의식을 통일하고, 데이터 기반의 의사결정이 가능합니다. 정기적인 리뷰와 개선이 중요합니다.', versions: [{ id: 130, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 131, asset_type: 'recommendation', asset_type_name: '제안/권고', asset_type_icon: '💡', asset_type_color: 'bg-yellow-100 text-yellow-800',
                        section_title: '32. 교육 및 훈련', order_index: 32,
                        content: '팀 역량 강화를 위한 교육 프로그램입니다.\n\n• 마이크로서비스 아키텍처 교육\n• 클라우드 네이티브 기술 교육\n• DevOps 도구 및 문화 교육\n• 보안 및 컴플라이언스 교육\n• 실습 위주 워크샵 운영',
                        summary: { id: 131, ai_summary: '성공적인 시스템 구축과 운영을 위한 체계적인 교육 프로그램입니다. 이론과 실습을 균형있게 구성합니다.', helpful_content: '팀 역량 향상이 프로젝트 성공의 핵심입니다. 외부 전문가 초청과 hands-on 실습을 통해 빠른 역량 확보가 가능합니다.', versions: [{ id: 131, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    
                    // === 5. 결론 및 다음 단계 (섹션 33-35) ===
                    {
                        id: 132, asset_type: 'conclusion', asset_type_name: '결론', asset_type_icon: '🏁', asset_type_color: 'bg-red-100 text-red-800',
                        section_title: '33. 주요 성과 및 이점', order_index: 33,
                        content: '본 시스템 도입으로 얻을 수 있는 주요 성과입니다.\n\n• 확장성: 10배 트래픽 증가 대응\n• 성능: 응답시간 70% 단축\n• 안정성: 99.99% 가용성 달성\n• 효율성: 개발 생산성 50% 향상\n• 혁신: AI/ML 기반 새로운 서비스',
                        summary: { id: 132, ai_summary: '시스템 현대화를 통해 달성할 수 있는 구체적이고 측정 가능한 성과들을 제시합니다.', helpful_content: '이러한 성과는 경쟁 우위 확보에 직접적으로 기여합니다. 특히 개발 생산성 향상은 새로운 비즈니스 기회 창출로 이어집니다.', versions: [{ id: 132, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 133, asset_type: 'conclusion', asset_type_name: '결론', asset_type_icon: '🏁', asset_type_color: 'bg-red-100 text-red-800',
                        section_title: '34. 권장사항', order_index: 34,
                        content: '성공적인 프로젝트 수행을 위한 핵심 권장사항입니다.\n\n• 경영진의 강력한 지원\n• Cross-functional 팀 구성\n• MVP 우선 접근법\n• 지속적인 학습과 개선\n• 단계적 위험 관리',
                        summary: { id: 133, ai_summary: '프로젝트 성공을 위해 반드시 고려해야 할 핵심 권장사항들을 정리합니다.', helpful_content: '이 권장사항들은 다른 성공 사례에서 검증된 베스트 프랙티스입니다. 특히 경영진 지원과 팀 구성이 성패를 좌우합니다.', versions: [{ id: 133, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 134, asset_type: 'conclusion', asset_type_name: '결론', asset_type_icon: '🏁', asset_type_color: 'bg-red-100 text-red-800',
                        section_title: '35. 다음 단계', order_index: 35,
                        content: '이 설계서를 바탕으로 한 구체적인 다음 단계입니다.\n\n• 프로젝트 승인 및 예산 확보\n• 핵심 팀원 선정 및 교육\n• 파일럿 프로젝트 계획 수립\n• 기술 스택 검증 (POC)\n• 1단계 상세 설계 착수',
                        summary: { id: 134, ai_summary: '설계서 완성 후 실제 구현으로 이어지는 구체적인 액션 플랜을 제시합니다.', helpful_content: '파일럿 프로젝트와 POC를 통해 위험을 최소화하면서 실행 가능성을 검증할 수 있습니다. 빠른 시작이 성공의 열쇠입니다.', versions: [{ id: 134, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    }
                ],
                
                5: [ // 블록체인 기술 백서 - 30개 챕터
                    // === 1. 기초 이론 (챕터 1-5) ===
                    {
                        id: 200, asset_type: 'introduction', asset_type_name: '서론/개요', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '1. 블록체인 기술 개요', order_index: 1,
                        content: '블록체인은 분산 원장 기술(DLT)의 핵심으로, 중앙 집중식 권한 없이도 신뢰할 수 있는 거래를 가능하게 합니다.\n\n• 분산형 네트워크 구조\n• 암호학적 해시 함수\n• 합의 메커니즘\n• 스마트 계약\n• 토큰 이코노미',
                        summary: { id: 200, ai_summary: '블록체인의 기본 개념과 핵심 기술 요소들을 소개합니다. 탈중앙화와 투명성이 주요 특징입니다.', helpful_content: '우리 회사의 데이터 무결성과 투명성 요구사항에 직접적으로 적용할 수 있습니다. 특히 공급망 관리에서 즉시 효과를 볼 수 있습니다.', versions: [{ id: 200, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 201, asset_type: 'introduction', asset_type_name: '서론/개요', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '2. 암호학 기초', order_index: 2,
                        content: '블록체인의 보안을 담당하는 핵심 암호학 기술들입니다.\n\n• SHA-256 해시 함수\n• 머클 트리 구조\n• 디지털 서명\n• 타원곡선 암호학\n• 영지식 증명',
                        summary: { id: 201, ai_summary: '블록체인 보안의 근간이 되는 암호학적 원리들을 설명합니다. 해시함수와 디지털 서명이 핵심입니다.', helpful_content: '현재 우리 시스템의 보안을 강화하는데 직접 활용할 수 있습니다. 특히 문서 무결성 검증에 적용 가능합니다.', versions: [{ id: 201, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 202, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '3. 합의 알고리즘', order_index: 3,
                        content: '네트워크 참여자들이 거래의 유효성에 합의하는 메커니즘입니다.\n\n• Proof of Work (PoW)\n• Proof of Stake (PoS)\n• Delegated Proof of Stake (DPoS)\n• Practical Byzantine Fault Tolerance (pBFT)\n• Proof of Authority (PoA)',
                        summary: { id: 202, ai_summary: '블록체인 네트워크의 합의를 이루는 다양한 알고리즘들을 비교 분석합니다. 각각의 장단점과 적용 사례를 다룹니다.', helpful_content: '우리 시스템에서 다중 당사자 간 합의가 필요한 프로세스에 적용할 수 있습니다. 특히 승인 워크플로우 개선에 도움이 됩니다.', versions: [{ id: 202, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 203, asset_type: 'technical_spec', asset_type_name: '기술명세', asset_type_icon: '⚙️', asset_type_color: 'bg-purple-100 text-purple-800',
                        section_title: '4. 스마트 계약 개발', order_index: 4,
                        content: '자동 실행되는 계약 조건을 코드로 구현한 프로그램입니다.\n\n• Solidity 프로그래밍\n• 가상 머신(EVM)\n• 가스 최적화\n• 보안 취약점 대응\n• 업그레이드 패턴',
                        summary: { id: 203, ai_summary: '스마트 계약의 개발 방법론과 모범 사례를 제시합니다. 보안과 효율성을 동시에 고려한 개발 가이드입니다.', helpful_content: '우리의 자동화 프로세스와 비즈니스 로직을 블록체인에 구현할 수 있는 방법을 제시합니다. 계약 자동화에 즉시 적용 가능합니다.', versions: [{ id: 203, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    },
                    {
                        id: 204, asset_type: 'analysis', asset_type_name: '분석', asset_type_icon: '📊', asset_type_color: 'bg-green-100 text-green-800',
                        section_title: '5. 토큰 경제학', order_index: 5,
                        content: '블록체인 생태계의 경제적 인센티브 구조 설계입니다.\n\n• 토큰 발행과 배분\n• 인센티브 메커니즘\n• 거버넌스 토큰\n• 스테이킹 보상\n• 디플레이션 모델',
                        summary: { id: 204, ai_summary: '블록체인 프로젝트의 지속가능한 경제 모델 설계 방법을 다룹니다. 참여자 인센티브가 핵심입니다.', helpful_content: '우리 플랫폼의 사용자 참여도를 높이는 리워드 시스템 설계에 응용할 수 있습니다. 포인트 시스템을 토큰화할 수 있습니다.', versions: [{ id: 204, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    }
                ],
                
                6: [ // 클라우드 네이티브 아키텍처 가이드 - 25개 섹션
                    {
                        id: 300, asset_type: 'introduction', asset_type_name: '서론/개요', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '1. 클라우드 네이티브 개념', order_index: 1,
                        content: '클라우드 환경에 최적화된 애플리케이션 개발과 운영 방법론입니다.\n\n• 컨테이너화\n• 마이크로서비스\n• 데브옵스\n• CI/CD 파이프라인\n• 관찰가능성',
                        summary: { id: 300, ai_summary: '클라우드 네이티브의 핵심 원칙과 구현 방법을 소개합니다. 확장성과 유연성이 주요 이점입니다.', helpful_content: '현재 우리 시스템의 클라우드 마이그레이션 계획에 직접 적용할 수 있는 가이드라인을 제공합니다.', versions: [{ id: 300, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    }
                ],
                
                7: [ // DevOps 베스트 프랙티스 매뉴얼 - 20개 섹션
                    {
                        id: 400, asset_type: 'introduction', asset_type_name: '서론/개요', asset_type_icon: '🎯', asset_type_color: 'bg-blue-100 text-blue-800',
                        section_title: '1. DevOps 문화와 철학', order_index: 1,
                        content: '개발과 운영팀의 협업을 통한 소프트웨어 전달 혁신입니다.\n\n• 문화적 변화\n• 협업 강화\n• 자동화\n• 측정과 피드백\n• 지속적 개선',
                        summary: { id: 400, ai_summary: 'DevOps의 문화적 측면과 조직 변화 전략을 다룹니다. 협업과 자동화가 핵심 가치입니다.', helpful_content: '우리 개발팀과 운영팀의 협업 방식을 개선하고 배포 프로세스를 자동화할 수 있는 실용적인 방법을 제시합니다.', versions: [{ id: 400, version_number: 1, version_display_name: 'v1 (AI 생성)', edit_type: 'ai_generated', is_current: true }], versions_count: 1, analysis_status: 'completed', status_icon: '✅' }
                    }
                ]
            };
            
            const defaultFile = {
                id: fileId,
                original_name: this.fileNames[fileId] || '문서 파일.pdf',
                file_name: 'document_' + fileId + '.pdf',
                file_size: 2048576,
                mime_type: 'application/pdf',
                is_analysis_completed: true,
                analysis_status: 'completed'
            };
            
            return {
                file: defaultFile,
                assets: mockAssets[fileId] || mockAssets[1] // 기본값으로 첫 번째 문서 사용
            };
        },

        // 에셋 선택
        selectAsset(index) {
            this.selectedAssetIndex = index;
            this.selectedAsset = this.documentData.assets[index];
            this.cancelAllEdits();
        },

        // 이전 에셋
        prevAsset() {
            if (this.selectedAssetIndex > 0) {
                this.selectAsset(this.selectedAssetIndex - 1);
            }
        },

        // 다음 에셋
        nextAsset() {
            if (this.selectedAssetIndex < this.documentData.assets.length - 1) {
                this.selectAsset(this.selectedAssetIndex + 1);
            }
        },

        // 편집 모드 활성화
        enableEdit(field) {
            this.editMode[field] = true;
            this.editContent[field] = this.selectedAsset?.summary?.[field] || '';
        },

        // 편집 취소
        cancelEdit(field) {
            this.editMode[field] = false;
            this.editContent[field] = '';
        },

        // 모든 편집 취소
        cancelAllEdits() {
            this.editMode = {
                ai_summary: false,
                helpful_content: false
            };
            this.editContent = {
                ai_summary: '',
                helpful_content: ''
            };
        },

        // 편집 저장 (Mock)
        async saveEdit(field) {
            if (!this.selectedAsset?.summary) {
                this.showNotification('요약 정보가 없습니다.', 'error');
                return;
            }

            try {
                // Mock 저장 시뮬레이션
                await new Promise(resolve => setTimeout(resolve, 800));
                
                // 로컬 데이터 업데이트
                this.selectedAsset.summary[field] = this.editContent[field];
                this.documentData.assets[this.selectedAssetIndex].summary[field] = this.editContent[field];
                
                // 새 버전 생성 시뮬레이션
                const newVersionNumber = this.selectedAsset.summary.versions_count + 1;
                const newVersion = {
                    id: Date.now(), // 임시 ID
                    version_number: newVersionNumber,
                    version_display_name: `v${newVersionNumber} (사용자 편집)`,
                    edit_type: 'user_edit',
                    is_current: true
                };
                
                // 기존 버전들을 current가 아니도록 변경
                this.selectedAsset.summary.versions.forEach(v => v.is_current = false);
                
                // 새 버전을 맨 앞에 추가
                this.selectedAsset.summary.versions.unshift(newVersion);
                this.selectedAsset.summary.versions_count = newVersionNumber;
                
                this.cancelEdit(field);
                this.showNotification('성공적으로 저장되었습니다! 새로운 버전이 생성되었습니다.', 'success');
                
            } catch (error) {
                console.error('Error saving edit:', error);
                this.showNotification('저장에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 버전 전환 (실제 동작)
        async switchVersion(versionNumber) {
            if (!this.selectedAsset?.summary) {
                return;
            }

            try {
                // 선택한 버전 찾기
                const selectedVersion = this.selectedAsset.summary.versions.find(v => v.version_number == versionNumber);
                if (!selectedVersion) {
                    this.showNotification('해당 버전을 찾을 수 없습니다.', 'error');
                    return;
                }
                
                // 모든 버전의 is_current를 false로 변경
                this.selectedAsset.summary.versions.forEach(version => {
                    version.is_current = version.version_number == versionNumber;
                });
                
                // 선택된 버전의 내용으로 현재 표시 내용 업데이트
                if (selectedVersion.content) {
                    this.selectedAsset.summary.ai_summary = selectedVersion.content.ai_summary;
                    this.selectedAsset.summary.helpful_content = selectedVersion.content.helpful_content;
                    
                    // documentData.assets에도 반영
                    this.documentData.assets[this.selectedAssetIndex].summary.ai_summary = selectedVersion.content.ai_summary;
                    this.documentData.assets[this.selectedAssetIndex].summary.helpful_content = selectedVersion.content.helpful_content;
                }
                
                this.showNotification(`버전 ${versionNumber}로 성공적으로 전환되었습니다!`, 'success');
                
            } catch (error) {
                console.error('Error switching version:', error);
                this.showNotification('버전 전환에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 섹션별 버전 전환 (연속 뷰용)
        async switchSectionVersion(sectionIndex, versionNumber) {
            try {
                const asset = this.documentData.assets[sectionIndex];
                const selectedVersion = asset.summary.versions?.find(v => v.version_number == versionNumber);
                
                if (!selectedVersion) {
                    this.showNotification('해당 버전을 찾을 수 없습니다.', 'error');
                    return;
                }
                
                // 모든 버전의 is_current를 false로 변경
                asset.summary.versions.forEach(version => {
                    version.is_current = version.version_number == versionNumber;
                });
                
                // 선택된 버전의 내용으로 현재 표시 내용 업데이트
                if (selectedVersion.content) {
                    asset.summary.ai_summary = selectedVersion.content.ai_summary;
                    asset.summary.helpful_content = selectedVersion.content.helpful_content;
                }
                
                this.showNotification(`섹션 ${sectionIndex + 1}의 버전 ${versionNumber}로 전환되었습니다!`, 'success');
                
            } catch (error) {
                console.error('Error switching section version:', error);
                this.showNotification('버전 전환에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 알림 표시
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            } text-white max-w-md`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        },

        // 에셋 타입별 테두리 색상
        getAssetBorderColor(assetType) {
            const colors = {
                'introduction': 'border-blue-400',
                'analysis': 'border-green-400',
                'technical_spec': 'border-purple-400',
                'findings': 'border-orange-400',
                'conclusion': 'border-red-400'
            };
            return colors[assetType] || 'border-gray-400';
        },

        // JSON 버전 로드
        async loadJsonVersion(versionId) {
            try {
                this.isLoading = true;
                this.currentJsonVersion = versionId;
                
                // 실제로는 서버에서 JSON을 로드해야 하지만, 
                // 현재는 Mock 데이터를 다시 생성
                await this.loadDocumentAssets();
                
                this.showNotification(`JSON 버전 ${versionId}로 전환되었습니다.`, 'success');
            } catch (error) {
                console.error('Error loading JSON version:', error);
                this.showNotification('JSON 버전 로드에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 기존 저장 기능 (JSON 관리 모달 열기)
        async saveCurrentJson() {
            this.showJsonManager = true;
            this.generateDefaultFileName();
        },

        // 로컬스토리지에서 저장된 파일 목록 로드
        loadSavedJsonFiles() {
            try {
                const saved = localStorage.getItem('documentAnalysis_savedFiles');
                this.savedJsonFiles = saved ? JSON.parse(saved) : [];
            } catch (error) {
                console.error('Error loading saved files:', error);
                this.savedJsonFiles = [];
            }
        },

        // 기본 파일명 생성
        generateDefaultFileName() {
            const fileName = this.fileNames[this.fileId] || '알 수 없는 파일';
            const shortName = fileName.replace(/\.[^/.]+$/, ""); // 확장자 제거
            this.saveFileName = `${shortName}_${this.currentJsonVersion}_${new Date().toLocaleDateString('ko-KR').replace(/\./g, '-')}`;
        },

        // 로컬 스토리지에 저장
        async saveToLocalStorage() {
            try {
                if (!this.saveFileName.trim()) {
                    this.showNotification('파일명을 입력해주세요.', 'error');
                    return;
                }

                const jsonData = {
                    id: Date.now().toString(),
                    fileName: this.saveFileName.trim(),
                    version: this.currentJsonVersion,
                    fileId: this.fileId,
                    originalFileName: this.fileNames[this.fileId],
                    
                    // 문서 버전 정보
                    documentVersion: this.documentVersion,
                    documentMajorVersion: this.documentMajorVersion,
                    documentMinorVersion: this.documentMinorVersion,
                    documentVersionHistory: this.documentVersionHistory,
                    
                    // 섹션별 완전한 버전 정보 포함
                    assets: this.documentData.assets.map(asset => ({
                        ...asset,
                        summary: {
                            ...asset.summary,
                            // 모든 버전의 완전한 내용 저장
                            versions: asset.summary?.versions?.map(version => ({
                                ...version,
                                content: {
                                    ai_summary: version.content?.ai_summary || '',
                                    helpful_content: version.content?.helpful_content || ''
                                }
                            })) || []
                        }
                    })),
                    
                    sectionsCount: this.documentData.assets?.length || 0,
                    createdAt: new Date().toISOString(),
                    
                    // 현재 문서 스냅샷
                    currentSnapshot: this.createSectionsSnapshot()
                };

                // 기존 저장된 파일 목록에 추가
                this.savedJsonFiles.unshift(jsonData);
                
                // 로컬스토리지에 저장
                localStorage.setItem('documentAnalysis_savedFiles', JSON.stringify(this.savedJsonFiles));
                
                this.showNotification(`'${this.saveFileName}' 파일이 ${this.documentVersion}으로 로컬 저장소에 저장되었습니다!`, 'success');
                this.saveFileName = '';
                this.generateDefaultFileName();
            } catch (error) {
                console.error('Error saving to localStorage:', error);
                this.showNotification('로컬 저장에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 현재 JSON을 파일로 다운로드
        async downloadCurrentJson() {
            try {
                const jsonData = {
                    version: this.currentJsonVersion,
                    fileId: this.fileId,
                    fileName: this.fileNames[this.fileId],
                    
                    // 문서 버전 정보
                    documentVersion: this.documentVersion,
                    documentMajorVersion: this.documentMajorVersion,
                    documentMinorVersion: this.documentMinorVersion,
                    documentVersionHistory: this.documentVersionHistory,
                    
                    // 섹션별 완전한 버전 정보 포함
                    assets: this.documentData.assets.map(asset => ({
                        ...asset,
                        summary: {
                            ...asset.summary,
                            // 모든 버전의 완전한 내용 저장
                            versions: asset.summary?.versions?.map(version => ({
                                ...version,
                                content: {
                                    ai_summary: version.content?.ai_summary || '',
                                    helpful_content: version.content?.helpful_content || ''
                                }
                            })) || []
                        }
                    })),
                    
                    sectionsCount: this.documentData.assets?.length || 0,
                    createdAt: new Date().toISOString(),
                    
                    // 현재 문서 스냅샷
                    currentSnapshot: this.createSectionsSnapshot()
                };
                
                const blob = new Blob([JSON.stringify(jsonData, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const downloadFileName = this.saveFileName.trim() || `document-analysis-${this.documentVersion}-file${this.fileId}-${Date.now()}`;
                a.download = `${downloadFileName}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

                this.showNotification(`JSON 파일이 ${this.documentVersion}으로 성공적으로 다운로드되었습니다!`, 'success');
            } catch (error) {
                console.error('Error downloading JSON:', error);
                this.showNotification('JSON 다운로드에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 로컬 스토리지에서 불러오기
        async loadFromLocalStorage(fileId) {
            try {
                const savedFile = this.savedJsonFiles.find(file => file.id === fileId);
                if (!savedFile) {
                    this.showNotification('저장된 파일을 찾을 수 없습니다.', 'error');
                    return;
                }

                this.isLoading = true;
                
                // 데이터 로드
                this.documentData.assets = savedFile.assets;
                this.currentJsonVersion = savedFile.version;
                this.fileId = savedFile.fileId;
                
                // 문서 버전 정보 복원 (기존 파일 호환성 고려)
                this.documentVersion = savedFile.documentVersion || 'v1.0';
                this.documentMajorVersion = savedFile.documentMajorVersion || 1;
                this.documentMinorVersion = savedFile.documentMinorVersion || 0;
                this.documentVersionHistory = savedFile.documentVersionHistory || [];
                
                // URL 업데이트
                const url = new URL(window.location);
                url.searchParams.set('file_id', this.fileId.toString());
                window.history.replaceState({}, '', url);
                
                this.isLoading = false;
                this.showJsonManager = false;
                this.showNotification(`'${savedFile.fileName}' (${this.documentVersion}) 파일을 성공적으로 불러왔습니다!`, 'success');
            } catch (error) {
                this.isLoading = false;
                console.error('Error loading from localStorage:', error);
                this.showNotification('파일 불러오기에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 로컬 스토리지에서 삭제
        async deleteFromLocalStorage(fileId) {
            try {
                if (!confirm('정말로 이 파일을 삭제하시겠습니까?')) {
                    return;
                }

                this.savedJsonFiles = this.savedJsonFiles.filter(file => file.id !== fileId);
                localStorage.setItem('documentAnalysis_savedFiles', JSON.stringify(this.savedJsonFiles));
                
                this.showNotification('파일이 성공적으로 삭제되었습니다.', 'success');
            } catch (error) {
                console.error('Error deleting file:', error);
                this.showNotification('파일 삭제에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 모든 로컬 스토리지 데이터 삭제
        async clearAllLocalStorage() {
            try {
                if (!confirm('정말로 모든 저장된 데이터를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
                    return;
                }

                localStorage.removeItem('documentAnalysis_savedFiles');
                this.savedJsonFiles = [];
                
                this.showNotification('모든 저장된 데이터가 삭제되었습니다.', 'success');
            } catch (error) {
                console.error('Error clearing localStorage:', error);
                this.showNotification('데이터 삭제에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 파일 업로드 처리
        async handleFileUpload(event) {
            try {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const jsonData = JSON.parse(e.target.result);
                        
                        // JSON 데이터 검증
                        if (!jsonData.assets || !Array.isArray(jsonData.assets)) {
                            this.showNotification('올바른 문서 분석 JSON 파일이 아닙니다.', 'error');
                            return;
                        }

                        this.isLoading = true;
                        
                        // 데이터 로드
                        this.documentData.assets = jsonData.assets;
                        this.currentJsonVersion = jsonData.version || 'v1';
                        
                        if (jsonData.fileId && this.fileNames[jsonData.fileId]) {
                            this.fileId = jsonData.fileId;
                            const url = new URL(window.location);
                            url.searchParams.set('file_id', this.fileId.toString());
                            window.history.replaceState({}, '', url);
                        }
                        
                        this.isLoading = false;
                        this.showJsonManager = false;
                        this.showNotification(`JSON 파일을 성공적으로 불러왔습니다! (${jsonData.sectionsCount || jsonData.assets.length}개 섹션)`, 'success');
                        
                        // 파일 입력 초기화
                        event.target.value = '';
                    } catch (parseError) {
                        this.isLoading = false;
                        console.error('Error parsing JSON:', parseError);
                        this.showNotification('JSON 파일 파싱에 실패했습니다: ' + parseError.message, 'error');
                    }
                };
                reader.readAsText(file);
            } catch (error) {
                console.error('Error handling file upload:', error);
                this.showNotification('파일 업로드 처리에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 총 저장 용량 계산 (KB)
        getTotalStorageSize() {
            try {
                const dataString = JSON.stringify(this.savedJsonFiles);
                return Math.round(new Blob([dataString]).size / 1024);
            } catch (error) {
                return 0;
            }
        },

        // 고유 버전 수 계산
        getUniqueVersionsCount() {
            const versions = new Set(this.savedJsonFiles.map(file => file.version));
            return versions.size;
        },

        // 편집 모드 토글
        toggleEditMode(sectionIndex, field) {
            const key = `${sectionIndex}_${field}`;
            
            if (this.editingStates[key]) {
                // 편집 모드 종료 (취소)
                this.cancelEdit(sectionIndex, field);
            } else {
                // 편집 모드 시작
                this.editingStates[key] = true;
                
                // 편집용 임시 데이터 초기화
                if (!this.editingContent[sectionIndex]) {
                    this.editingContent[sectionIndex] = {};
                }
                
                // 현재 내용을 편집 임시 저장소에 복사
                this.editingContent[sectionIndex][field] = this.documentData.assets[sectionIndex].summary[field] || '';
            }
        },

        // 편집 상태 확인
        isEditing(sectionIndex, field) {
            const key = `${sectionIndex}_${field}`;
            return this.editingStates[key] || false;
        },

        // 편집 취소
        cancelEdit(sectionIndex, field) {
            const key = `${sectionIndex}_${field}`;
            delete this.editingStates[key];
            
            if (this.editingContent[sectionIndex]) {
                delete this.editingContent[sectionIndex][field];
                
                // 해당 섹션에 편집 중인 필드가 없으면 객체 자체 삭제
                if (Object.keys(this.editingContent[sectionIndex]).length === 0) {
                    delete this.editingContent[sectionIndex];
                }
            }
        },

        // 편집 저장 (새 버전 생성)
        async saveEdit(sectionIndex, field) {
            try {
                const newContent = this.editingContent[sectionIndex][field];
                
                if (!newContent || !newContent.trim()) {
                    this.showNotification('내용을 입력해주세요.', 'error');
                    return;
                }
                
                // 새 버전 생성
                this.createNewVersion(sectionIndex, field, newContent.trim());
                
                // 편집 모드 종료
                this.cancelEdit(sectionIndex, field);
                
                // 성공 알림
                this.showNotification(`AI 요약이 새 버전(v${this.getCurrentVersionNumber(sectionIndex)})으로 저장되었습니다!`, 'success');
                
            } catch (error) {
                console.error('Error saving edit:', error);
                this.showNotification('저장에 실패했습니다: ' + error.message, 'error');
            }
        },

        // 새 버전 생성 함수
        createNewVersion(sectionIndex, field, newContent) {
            const asset = this.documentData.assets[sectionIndex];
            
            // 현재 버전 찾기
            const currentVersion = asset.summary.versions?.find(v => v.is_current);
            const newVersionNumber = Math.max(...(asset.summary.versions?.map(v => v.version_number) || [1])) + 1;
            
            // versions 배열이 없으면 초기화
            if (!asset.summary.versions) {
                asset.summary.versions = [];
                // 기존 데이터를 첫 번째 버전으로 생성
                asset.summary.versions.push({
                    id: Date.now() - 1000,
                    version_number: 1,
                    version_display_name: 'v1 (AI 생성)',
                    edit_type: 'ai_generated',
                    is_current: false,
                    content: {
                        ai_summary: asset.summary.ai_summary || '',
                        helpful_content: asset.summary.helpful_content || ''
                    },
                    created_at: new Date(Date.now() - 1000).toISOString()
                });
            }
            
            // 기존 버전들을 current false로 변경
            asset.summary.versions.forEach(v => v.is_current = false);
            
            // 새 버전 생성
            const newVersion = {
                id: Date.now(),
                version_number: newVersionNumber,
                version_display_name: `v${newVersionNumber} (사용자 편집)`,
                edit_type: 'user_edit',
                is_current: true,
                content: {
                    ai_summary: field === 'ai_summary' ? newContent : (currentVersion?.content?.ai_summary || asset.summary.ai_summary),
                    helpful_content: field === 'helpful_content' ? newContent : (currentVersion?.content?.helpful_content || asset.summary.helpful_content)
                },
                created_at: new Date().toISOString()
            };
            
            // 새 버전 추가
            asset.summary.versions.push(newVersion);
            
            // 현재 표시되는 내용 업데이트
            asset.summary[field] = newContent;
            
            // 버전 카운트 업데이트
            asset.summary.versions_count = asset.summary.versions.length;
            
            // 문서 버전 증가 및 스냅샷 생성
            this.incrementDocumentVersion(sectionIndex, field, newContent);
        },

        // 현재 버전 번호 조회
        getCurrentVersionNumber(sectionIndex) {
            const asset = this.documentData.assets[sectionIndex];
            const currentVersion = asset.summary.versions?.find(v => v.is_current);
            return currentVersion?.version_number || 1;
        },

        // 문서 버전 증가 및 스냅샷 생성
        incrementDocumentVersion(sectionIndex, field, newContent) {
            // 부 버전 증가
            this.documentMinorVersion++;
            this.documentVersion = `v${this.documentMajorVersion}.${this.documentMinorVersion}`;
            
            // 문서 버전 스냅샷 생성
            const documentSnapshot = {
                id: Date.now(),
                document_version: this.documentVersion,
                major_version: this.documentMajorVersion,
                minor_version: this.documentMinorVersion,
                change_description: `섹션 ${sectionIndex + 1} - ${field} 편집`,
                changed_section_index: sectionIndex,
                changed_field: field,
                changed_content: newContent,
                created_at: new Date().toISOString(),
                sections_snapshot: this.createSectionsSnapshot()
            };
            
            // 문서 버전 히스토리에 추가
            this.documentVersionHistory.push(documentSnapshot);
        },

        // 모든 섹션의 현재 상태 스냅샷 생성
        createSectionsSnapshot() {
            return this.documentData.assets.map((asset, index) => ({
                section_index: index,
                section_title: asset.section_title,
                asset_type: asset.asset_type,
                current_version: this.getCurrentVersionNumber(index),
                ai_summary: asset.summary?.ai_summary || '',
                helpful_content: asset.summary?.helpful_content || '',
                versions_count: asset.summary?.versions_count || 1,
                last_modified: asset.summary?.versions?.find(v => v.is_current)?.created_at || new Date().toISOString()
            }));
        },

        // 파일 변경
        changeFile(newFileId) {
            if (newFileId != this.fileId) {
                const url = new URL(window.location);
                url.searchParams.set('file_id', newFileId);
                window.location.href = url.toString();
            }
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>