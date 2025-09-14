{{-- AI 문서 에셋 분석 결과 화면 --}}
<?php 
    require_once __DIR__ . "/../../../../../../bootstrap.php";
use App\Services\TemplateCommonService;
    

    $screenInfo = TemplateCommonService::getCurrentTemplateScreenInfo();
    $uploadPaths = TemplateCommonService::getTemplateUploadPaths();
    
    // URL에서 file_id 파라미터 가져오기
    $fileId = $_GET['file_id'] ?? null;
?>
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-100 p-6" 
     x-data="documentAnalysisData(<?= intval($fileId) ?>)" 
     x-init="init()"
     x-cloak>
    {{-- 글로벌 네비게이션 포함 --}}
    @include('700-page-sandbox.700-common.100-sandbox-navigation')
    
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
        availableJsonVersions: [],
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
            
            // fileNames 로드
            await this.loadFileNames();
            
            // 버전 파일 목록 로드
            await this.loadAvailableVersions();
            
            await this.loadDocumentAssets();
        },
        
        // 파일명 로드
        async loadFileNames() {
            try {
                const response = await fetch('./mock-data.json');
                const mockData = await response.json();
                this.fileNames = mockData.fileNames || {};
            } catch (error) {
                console.error('Failed to load file names:', error);
                // 기본값 설정
                this.fileNames = {
                    1: 'Document 1.pdf',
                    2: 'Document 2.pdf',
                    3: 'Document 3.pdf',
                    4: 'Document 4.pdf',
                    5: 'Document 5.pdf',
                    6: 'Document 6.pdf',
                    7: 'Document 7.pdf'
                };
            }
        },
        
        // 사용 가능한 버전 파일 목록 로드
        async loadAvailableVersions() {
            try {
                // 하드코딩된 버전 목록 (실제로는 서버에서 디렉토리 스캔해야 함)
                this.availableJsonVersions = [
                    { id: 'v1.0', name: 'v1.0 - AI 기술 동향 보고서 기본 분석', file: 'v1.0-document-analysis.json' },
                    { id: 'v2.0', name: 'v2.0 - AI 기술 동향 보고서 확장 분석', file: 'v2.0-document-analysis.json' },
                    { id: 'v3.0', name: 'v3.0 - 스마트 시티 플랫폼 제안서', file: 'v3.0-smart-city-analysis.json' }
                ];
                this.currentJsonVersion = 'v1.0';
            } catch (error) {
                console.error('Failed to load available versions:', error);
                this.availableJsonVersions = [
                    { id: 'v1', name: 'v1 - 기본 데이터셋', file: 'mock-data.json' }
                ];
                this.currentJsonVersion = 'v1';
            }
        },

        // 문서 에셋 로드 (Mock 데이터 사용)
        async loadDocumentAssets() {
            try {
                this.isLoading = true;
                
                // Mock 데이터 로딩 시뮬레이션
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // Mock 데이터 로딩 (JSON 파일에서, 실패시 fallback)
                const mockData = await this.loadMockData(this.fileId);
                
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

        // Mock 데이터 로딩 (버전별)
        async loadMockData(fileId) {
            try {
                // 현재 버전에 해당하는 파일 찾기
                const currentVersion = this.availableJsonVersions.find(v => v.id === this.currentJsonVersion);
                const fileName = currentVersion ? currentVersion.file : 'mock-data.json';
                const filePath = fileName.includes('-') ? `./versions/${fileName}` : `./${fileName}`;
                
                const response = await fetch(filePath);
                const data = await response.json();
                
                // 버전별 데이터인지 확인
                if (data.file && data.assets) {
                    // 버전별 파일 형식
                    return {
                        file: data.file,
                        assets: data.assets
                    };
                } else {
                    // 기존 mock-data.json 형식
                    return {
                        file: {
                            id: fileId,
                            original_name: data.fileNames[fileId] || `파일 ${fileId}`,
                            file_path: `/uploads/${fileId}.pdf`,
                            mime_type: 'application/pdf',
                            is_analysis_completed: true,
                            analysis_status: 'completed'
                        },
                        assets: data[fileId] || data[1] || []
                    };
                }
            } catch (error) {
                console.error('Mock data loading failed:', error);
                // generateMockData 폴백 사용
                return this.generateMockData(fileId);
            }
        },
        
        // Mock 데이터 생성 (폴백용)
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
        },
        
        // JSON 버전 변경
        async loadJsonVersion(versionId) {
            try {
                this.isLoading = true;
                this.currentJsonVersion = versionId;
                
                // 새로운 버전의 데이터 로드
                const mockData = await this.loadMockData(this.fileId);
                
                this.documentData.file = mockData.file;
                this.documentData.assets = mockData.assets;
                this.documentData.analysis_progress = 100;
                this.documentData.analysis_status = 'completed';
                
                if (this.documentData.assets && this.documentData.assets.length > 0) {
                    this.selectAsset(0);
                }
                
                const versionName = this.availableJsonVersions.find(v => v.id === versionId)?.name || versionId;
                this.showNotification(`${versionName} 버전을 로드했습니다.`, 'success');
                
            } catch (error) {
                console.error('Error loading JSON version:', error);
                this.showNotification('버전 로드에 실패했습니다: ' + error.message, 'error');
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>