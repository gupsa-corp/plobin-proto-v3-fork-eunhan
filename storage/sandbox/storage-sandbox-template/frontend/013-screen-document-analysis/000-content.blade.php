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
                        <h1 class="text-2xl font-bold text-gray-900">AI 문서 분석 결과</h1>
                        <p class="text-gray-600">팔란티어 온톨로지 기반 에셋 분류 및 분석</p>
                        <p x-show="documentData.file" class="text-sm text-indigo-600 mt-1" x-text="documentData.file?.original_name"></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">분석 진행률</div>
                    <div class="flex items-center space-x-2">
                        <div class="w-32 h-2 bg-gray-200 rounded-full">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-300" 
                                 :style="`width: ${documentData.analysis_progress || 0}%`"></div>
                        </div>
                        <span class="text-sm font-medium text-indigo-600" x-text="`${documentData.analysis_progress || 0}%`"></span>
                    </div>
                </div>
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

    {{-- 에셋 분석 결과 --}}
    <div x-show="!isLoading && documentData.assets && documentData.assets.length > 0" class="space-y-6">
        
        {{-- 에셋 탭 네비게이션 --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">문서 에셋</h2>
            <div class="flex flex-wrap gap-2 mb-4">
                <template x-for="(asset, index) in documentData.assets" :key="asset.id">
                    <button @click="selectAsset(index)"
                            class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="selectedAssetIndex === index 
                                ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' 
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                        <span x-text="asset.asset_type_icon" class="mr-1"></span>
                        <span x-text="asset.section_title"></span>
                        <span x-show="asset.summary?.versions_count > 1" 
                              class="ml-1 px-1 py-0.5 text-xs bg-purple-100 text-purple-700 rounded"
                              x-text="`v${asset.summary?.versions_count || 1}`"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- 선택된 에셋 상세 내용 --}}
        <div x-show="selectedAsset" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 원문 --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-blue-500 mr-2">📄</span>
                        원문
                    </h3>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full" 
                          x-text="selectedAsset?.asset_type_name"></span>
                </div>
                <div class="prose prose-sm max-w-none">
                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap text-sm" 
                         x-text="selectedAsset?.content"></div>
                </div>
            </div>

            {{-- AI 요약 --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-purple-500 mr-2">🤖</span>
                        AI 요약
                    </h3>
                    <div class="flex items-center space-x-2">
                        {{-- 버전 선택 드롭다운 --}}
                        <div x-show="selectedAsset?.summary?.versions" class="relative">
                            <select @change="switchVersion($event.target.value)"
                                    class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded border-none focus:ring-2 focus:ring-purple-500">
                                <template x-for="version in selectedAsset?.summary?.versions || []" :key="version.id">
                                    <option :value="version.version_number" 
                                            :selected="version.is_current"
                                            x-text="version.version_display_name"></option>
                                </template>
                            </select>
                        </div>
                        <button @click="enableEdit('ai_summary')"
                                class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200"
                                title="편집">
                            ✏️
                        </button>
                    </div>
                </div>
                <div x-show="!editMode.ai_summary" class="prose prose-sm max-w-none">
                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap text-sm" 
                         x-text="selectedAsset?.summary?.ai_summary || '요약이 아직 생성되지 않았습니다.'"></div>
                </div>
                {{-- 편집 모드 --}}
                <div x-show="editMode.ai_summary" class="space-y-3">
                    <textarea x-model="editContent.ai_summary"
                              class="w-full h-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm"
                              placeholder="AI 요약을 수정하세요..."></textarea>
                    <div class="flex space-x-2">
                        <button @click="saveEdit('ai_summary')"
                                class="px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700">
                            저장
                        </button>
                        <button @click="cancelEdit('ai_summary')"
                                class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">
                            취소
                        </button>
                    </div>
                </div>
            </div>

            {{-- 우리에게 도움되는 내용 --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-green-500 mr-2">💡</span>
                        도움되는 내용
                    </h3>
                    <button @click="enableEdit('helpful_content')"
                            class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200"
                            title="편집">
                        ✏️
                    </button>
                </div>
                <div x-show="!editMode.helpful_content" class="prose prose-sm max-w-none">
                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap text-sm" 
                         x-text="selectedAsset?.summary?.helpful_content || '도움되는 내용이 아직 생성되지 않았습니다.'"></div>
                </div>
                {{-- 편집 모드 --}}
                <div x-show="editMode.helpful_content" class="space-y-3">
                    <textarea x-model="editContent.helpful_content"
                              class="w-full h-40 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                              placeholder="도움되는 내용을 수정하세요..."></textarea>
                    <div class="flex space-x-2">
                        <button @click="saveEdit('helpful_content')"
                                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                            저장
                        </button>
                        <button @click="cancelEdit('helpful_content')"
                                class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">
                            취소
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 에셋 메타데이터 --}}
        <div x-show="selectedAsset" class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">에셋 정보</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 block">에셋 타입</span>
                    <span class="font-medium" x-text="selectedAsset?.asset_type_name"></span>
                </div>
                <div>
                    <span class="text-gray-500 block">순서</span>
                    <span class="font-medium" x-text="selectedAsset?.order_index"></span>
                </div>
                <div x-show="selectedAsset?.summary">
                    <span class="text-gray-500 block">분석 상태</span>
                    <div class="flex items-center">
                        <span x-text="selectedAsset?.summary?.status_icon" class="mr-1"></span>
                        <span class="font-medium" x-text="selectedAsset?.summary?.analysis_status"></span>
                    </div>
                </div>
                <div x-show="selectedAsset?.summary">
                    <span class="text-gray-500 block">버전 수</span>
                    <span class="font-medium" x-text="selectedAsset?.summary?.versions_count || 1"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- 하단 네비게이션 --}}
    <div x-show="!isLoading && documentData.assets && documentData.assets.length > 0" 
         class="fixed bottom-6 right-6 flex space-x-3">
        <button @click="prevAsset()" 
                :disabled="selectedAssetIndex <= 0"
                class="px-4 py-2 bg-white shadow-lg rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button @click="nextAsset()" 
                :disabled="selectedAssetIndex >= (documentData.assets || []).length - 1"
                class="px-4 py-2 bg-white shadow-lg rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <button @click="window.history.back()"
                class="px-4 py-2 bg-indigo-600 text-white shadow-lg rounded-lg hover:bg-indigo-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
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
        editMode: {
            ai_summary: false,
            helpful_content: false
        },
        editContent: {
            ai_summary: '',
            helpful_content: ''
        },

        // 초기화
        async init() {
            if (!this.fileId) {
                this.showNotification('파일 ID가 지정되지 않았습니다.', 'error');
                return;
            }
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
            const fileNames = {
                1: 'AI 기술 동향 보고서 2024.pdf',
                2: '프로젝트 제안서 - 스마트 시티 플랫폼.docx', 
                3: '시장 분석 리포트 - AI 솔루션 트렌드.pdf'
            };
            
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
                original_name: fileNames[fileId] || '문서 파일.pdf',
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

        // 버전 전환 (Mock)
        async switchVersion(versionNumber) {
            if (!this.selectedAsset?.summary) {
                return;
            }

            try {
                // Mock 버전 전환 시뮬레이션
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // 선택한 버전 찾기
                const selectedVersion = this.selectedAsset.summary.versions.find(v => v.version_number == versionNumber);
                if (!selectedVersion) {
                    throw new Error('버전을 찾을 수 없습니다.');
                }
                
                // Mock 버전 데이터 생성 (버전별로 약간 다른 내용)
                let mockAiSummary = this.selectedAsset.summary.ai_summary;
                let mockHelpfulContent = this.selectedAsset.summary.helpful_content;
                
                if (selectedVersion.edit_type === 'user_edit') {
                    mockAiSummary = '[사용자 편집 버전] ' + mockAiSummary;
                    mockHelpfulContent = '[사용자 편집 버전] ' + mockHelpfulContent;
                } else if (selectedVersion.version_number > 1) {
                    mockAiSummary = '[개선된 버전] ' + mockAiSummary;
                    mockHelpfulContent = '[개선된 버전] ' + mockHelpfulContent;
                }
                
                // 로컬 데이터 업데이트
                this.selectedAsset.summary.ai_summary = mockAiSummary;
                this.selectedAsset.summary.helpful_content = mockHelpfulContent;
                this.documentData.assets[this.selectedAssetIndex].summary.ai_summary = mockAiSummary;
                this.documentData.assets[this.selectedAssetIndex].summary.helpful_content = mockHelpfulContent;
                
                // 버전 활성화 상태 업데이트
                this.selectedAsset.summary.versions.forEach(version => {
                    version.is_current = version.version_number == versionNumber;
                });
                
                this.showNotification(`버전 ${versionNumber}로 성공적으로 전환되었습니다!`, 'success');
                
            } catch (error) {
                console.error('Error switching version:', error);
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
        }
    }
}
</script>

<!-- Alpine.js 스크립트 -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>