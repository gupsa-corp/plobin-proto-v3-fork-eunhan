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

                // 임시 블레이드 파일 생성 및 렌더링
                $tempViewPath = 'sandbox-renderer-temp-' . time() . '-' . rand(1000, 9999);
                $tempViewFile = resource_path('views/' . $tempViewPath . '.blade.php');

                // 템플릿을 단순히 wrapper div로 감싸기 (Livewire 호환성을 위해)
                $template = $this->screenData['blade_template'];
                $wrappedTemplate = '<div class="livewire-template-wrapper">' . $template . '</div>';
                File::put($tempViewFile, $wrappedTemplate);

                try {
                    // 블레이드 템플릿 렌더링
                    $renderedContent = view($tempViewPath, $sampleData)->render();
                    
                    // 렌더링된 HTML이 이미 wrapper div 안에 있지만, Livewire가 여전히 multiple root elements를 감지하는 경우가 있음
                    // 추가적인 wrapper로 확실하게 single root element 보장
                    $renderedContent = '<div class="livewire-single-root">' . $renderedContent . '</div>';
                } catch (\Exception $e) {
                    $renderedContent = '<div class="text-red-600 p-4">렌더링 오류: ' . $e->getMessage() . '</div>';
                } finally {
                    // 임시 파일 삭제
                    if (File::exists($tempViewFile)) {
                        File::delete($tempViewFile);
                    }
                }

            } catch (\Exception $e) {
                $renderedContent = '<div class="text-red-600 p-4">템플릿 처리 오류: ' . $e->getMessage() . '</div>';
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

}
