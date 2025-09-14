<?php

namespace App\Livewire\Sandbox\CustomScreens;

use Livewire\Component;
use Illuminate\Support\Facades\File;

class RendererComponent extends Component
{
    public $skipMultipleRootElementDetection = true;
    public $screenData;

    public function mount($screenData = null)
    {
        $this->screenData = $screenData;
    }

    public function render()
    {
        $renderedContent = '';

        if ($this->screenData && isset($this->screenData['blade_template'])) {
            try {
                // 샘플 데이터 설정
                $sampleData = [
                    'title' => $this->screenData['title'],
                    'users' => collect([
                        ['id' => 1, 'name' => '홍길동', 'email' => 'hong@example.com', 'status' => 'active'],
                        ['id' => 2, 'name' => '김철수', 'email' => 'kim@example.com', 'status' => 'inactive'],
                        ['id' => 3, 'name' => '이영희', 'email' => 'lee@example.com', 'status' => 'active']
                    ]),
                    'projects' => collect([
                        ['id' => 1, 'name' => '프로젝트 A', 'status' => 'active', 'progress' => 75],
                        ['id' => 2, 'name' => '프로젝트 B', 'status' => 'pending', 'progress' => 30],
                        ['id' => 3, 'name' => '프로젝트 C', 'status' => 'completed', 'progress' => 100]
                    ]),
                    'organizations' => collect([
                        ['id' => 1, 'name' => '샘플 조직 1', 'members' => 15],
                        ['id' => 2, 'name' => '샘플 조직 2', 'members' => 8],
                    ])
                ];

                // 템플릿 전처리 - PHP 코드 및 Bootstrap 호출 제거
                $template = $this->screenData['blade_template'];
                $cleanedTemplate = $this->preprocessTemplate($template);

                // 임시 블레이드 파일 생성 및 렌더링
                $tempViewPath = 'sandbox-renderer-temp-' . time() . '-' . rand(1000, 9999);
                $tempViewFile = resource_path('views/' . $tempViewPath . '.blade.php');

                // Livewire 호환성을 위해 wrapper div로 감싸기
                $wrappedTemplate = '<div class="livewire-template-wrapper">' . $cleanedTemplate . '</div>';
                File::put($tempViewFile, $wrappedTemplate);

                try {
                    // 블레이드 템플릿 렌더링
                    $renderedContent = view($tempViewPath, $sampleData)->render();
                    
                    // 추가적인 wrapper로 single root element 보장
                    $renderedContent = '<div class="livewire-single-root">' . $renderedContent . '</div>';
                } catch (\Exception $e) {
                    $renderedContent = '<div class="text-red-600 p-4 border border-red-300 rounded">
                        <strong>렌더링 오류:</strong> ' . htmlspecialchars($e->getMessage()) . '
                        <details class="mt-2">
                            <summary class="cursor-pointer text-sm">상세 정보</summary>
                            <pre class="text-xs mt-1 bg-red-50 p-2 rounded overflow-x-auto">' . 
                            htmlspecialchars($e->getTraceAsString()) . '</pre>
                        </details>
                    </div>';
                } finally {
                    // 임시 파일 삭제
                    if (File::exists($tempViewFile)) {
                        File::delete($tempViewFile);
                    }
                }

            } catch (\Exception $e) {
                $renderedContent = '<div class="text-red-600 p-4 border border-red-300 rounded">
                    <strong>템플릿 처리 오류:</strong> ' . htmlspecialchars($e->getMessage()) . '
                </div>';
            }
        } else {
            $renderedContent = '<div class="text-gray-500 p-8 text-center">렌더링할 템플릿이 없습니다.</div>';
        }

        return view('livewire.sandbox.custom-screens.renderer-component', [
            'renderedContent' => $renderedContent,
            'error' => null,
            'screen' => $this->screenData
        ]);
    }

    /**
     * 템플릿 전처리 - 미리보기에서 실행 불가능한 코드 제거/대체
     */
    private function preprocessTemplate($template)
    {
        // PHP 코드 블록 제거 (require_once, use statements, service calls 등)
        $template = preg_replace('/^<\?php[\s\S]*?\?>/m', '', $template);
        
        // 주석 스타일로 된 PHP 코드도 제거
        $template = preg_replace('/{{--[\s\S]*?--}}(?=\s*<\?php)/', '', $template);
        
        // 빈 줄 정리
        $template = preg_replace('/\n\s*\n\s*\n/', "\n\n", $template);
        
        // Alpine.js 스크립트를 미리보기용으로 수정
        $template = $this->processAlpineJs($template);
        
        // API 호출을 목업 데이터로 대체
        $template = $this->mockApiCalls($template);
        
        return trim($template);
    }

    /**
     * Alpine.js 코드를 미리보기용으로 수정
     */
    private function processAlpineJs($template)
    {
        // Alpine.js 함수 내의 API 호출을 목업으로 대체
        $template = preg_replace_callback(
            '/function\s+(\w+)\(\s*\)\s*{([\s\S]*?)}/m',
            function($matches) {
                $functionName = $matches[1];
                $functionBody = $matches[2];
                
                // 목업 데이터를 위한 함수 본문 수정
                if (strpos($functionBody, 'loadProjects') !== false || strpos($functionBody, 'fetch') !== false) {
                    return $this->generateMockAlpineFunction($functionName);
                }
                
                return $matches[0];
            },
            $template
        );
        
        return $template;
    }

    /**
     * API 호출을 목업 데이터로 대체
     */
    private function mockApiCalls($template)
    {
        // fetch() 호출을 목업으로 대체
        $template = preg_replace(
            '/await\s+fetch\([^)]+\)/',
            'await this.mockFetch()',
            $template
        );
        
        return $template;
    }

    /**
     * Alpine.js 함수를 목업 데이터로 생성
     */
    private function generateMockAlpineFunction($functionName)
    {
        if ($functionName === 'tableViewData') {
            return 'function tableViewData() {
    return {
        projects: [
            {id: 1, name: "샘플 프로젝트 1", description: "첫 번째 샘플 프로젝트", status: "in_progress", priority: "high", progress: 75, created_at: "2024-01-15"},
            {id: 2, name: "샘플 프로젝트 2", description: "두 번째 샘플 프로젝트", status: "completed", priority: "medium", progress: 100, created_at: "2024-01-10"},
            {id: 3, name: "샘플 프로젝트 3", description: "세 번째 샘플 프로젝트", status: "pending", priority: "low", progress: 30, created_at: "2024-01-20"}
        ],
        loading: false,
        filters: { search: "", status: "", priority: "" },
        pagination: { total: 3, offset: 0, limit: 20, hasNext: false, hasPrev: false },
        stats: { total: 3, in_progress: 1, completed: 1, high_priority: 1 },
        showCreateModal: false,
        showEditModal: false,
        formData: { id: null, name: "", description: "", status: "pending", priority: "medium", progress: 0 },
        saving: false,
        searchTimeout: null,
        
        async loadProjects() { /* Mock function - no API calls */ },
        calculateStats() { /* Mock function */ },
        debounceSearch() { /* Mock function */ },
        nextPage() { /* Mock function */ },
        prevPage() { /* Mock function */ },
        editProject(project) { 
            this.formData = {...project}; 
            this.showEditModal = true; 
        },
        async saveProject() { 
            alert("미리보기 모드에서는 저장할 수 없습니다."); 
            this.closeModal(); 
        },
        async deleteProject(id) { 
            if (confirm("미리보기 모드입니다. 실제로 삭제되지 않습니다.")) {
                this.projects = this.projects.filter(p => p.id !== id);
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.showEditModal = false;
            this.formData = { id: null, name: "", description: "", status: "pending", priority: "medium", progress: 0 };
        },
        getStatusClass(status) {
            const classes = {
                "pending": "bg-gray-100 text-gray-800",
                "in_progress": "bg-yellow-100 text-yellow-800",
                "on_hold": "bg-orange-100 text-orange-800",
                "completed": "bg-green-100 text-green-800"
            };
            return classes[status] || "bg-gray-100 text-gray-800";
        },
        getStatusText(status) {
            const texts = { "pending": "대기", "in_progress": "진행 중", "on_hold": "보류", "completed": "완료" };
            return texts[status] || status;
        },
        getPriorityClass(priority) {
            const classes = {
                "low": "bg-green-100 text-green-800",
                "medium": "bg-yellow-100 text-yellow-800", 
                "high": "bg-red-100 text-red-800"
            };
            return classes[priority] || "bg-gray-100 text-gray-800";
        },
        getPriorityText(priority) {
            const texts = { "low": "낮음", "medium": "보통", "high": "높음" };
            return texts[priority] || priority;
        },
        formatDate(datetime) {
            return datetime ? new Date(datetime).toLocaleDateString("ko-KR") : "";
        }
    }
}';
        }
        
        return "function $functionName() { return {}; }";
    }

}
