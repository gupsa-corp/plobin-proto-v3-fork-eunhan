<?php

namespace App\Livewire\Sandbox\CustomScreens;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use App\Services\SandboxContextService;

class TemplateScreenViewer extends Component
{
    protected $sandboxContextService;
    public $domain;
    public $screen;
    public $screenData = null;
    public $error = null;

    public function mount($domain, $screen)
    {
        $this->sandboxContextService = app(SandboxContextService::class);
        $this->domain = $domain;
        $this->screen = $screen;
        $this->loadScreenData();
    }

    public function render()
    {
        return view('livewire.sandbox.custom-screens.template-screen-viewer');
    }

    private function loadScreenData()
    {
        try {
            $templatePath = $this->sandboxContextService->getSandboxPath();
            $screenPath = $templatePath . '/' . $this->domain . '/' . $this->screen . '/000-content.blade.php';

            if (!File::exists($screenPath)) {
                $this->error = "화면 파일을 찾을 수 없습니다: {$this->domain}/{$this->screen}";
                return;
            }

            // 화면 데이터 로드
            $fileContent = File::get($screenPath);
            
            // Domain 정보 추출
            $domainParts = explode('-', $this->domain);
            $domainId = $domainParts[0] ?? '000';
            $domainType = $domainParts[2] ?? 'unknown';

            // Screen 정보 추출
            $screenParts = explode('-', $this->screen, 3);
            $screenId = $screenParts[0] ?? '000';
            $screenType = $screenParts[1] ?? 'screen';
            $screenName = $screenParts[2] ?? 'unnamed';

            $this->screenData = [
                'domain_id' => $domainId,
                'domain_name' => $domainType,
                'domain_title' => ucwords(str_replace('-', ' ', $domainType)),
                'screen_id' => $screenId,
                'screen_name' => $screenName,
                'screen_title' => ucwords(str_replace('-', ' ', $screenName)),
                'screen_type' => $screenType,
                'file_path' => $screenPath,
                'content' => $fileContent,
                'file_size' => File::size($screenPath),
                'last_modified' => date('Y-m-d H:i:s', File::lastModified($screenPath)),
            ];

        } catch (\Exception $e) {
            $this->error = "화면 로드 중 오류가 발생했습니다: " . $e->getMessage();
        }
    }

    public function backToCustomScreens()
    {
        return redirect()->route('sandbox.custom-screens');
    }
}