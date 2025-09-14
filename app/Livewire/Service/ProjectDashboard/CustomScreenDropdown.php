<?php

namespace App\Livewire\Service\ProjectDashboard;

use App\Models\ProjectPage;
use App\Services\SandboxService;
use App\Services\SandboxTemplateService;
use Livewire\Component;

class CustomScreenDropdown extends Component
{
    public $orgId;
    public $projectId;
    public $pageId;
    public $page;
    public $hasSandbox = false;
    public $availableScreens = [];
    public $selectedCustomScreen = '';
    public $currentCustomScreenFolder = '';
    public $currentDomain = '';
    public $availableDomains = [];
    public $currentDomainScreens = [];
    public $dropdownOpen = false;
    public $domainDropdownOpen = false;


    public function mount($orgId, $projectId, $pageId)
    {
        $this->orgId = $orgId;
        $this->projectId = $projectId;
        $this->pageId = $pageId;

        $this->page = ProjectPage::find($pageId);

        if ($this->page) {
            $sandboxService = app(SandboxService::class);
            $sandboxInfo = $sandboxService->getPageSandboxInfo($this->page);

            $this->hasSandbox = $sandboxInfo['has_sandbox'] ?? false;

            if ($this->hasSandbox) {
                $this->availableScreens = $sandboxService->getAvailableCustomScreens($sandboxInfo['sandbox_name']);
                $this->currentCustomScreenFolder = $sandboxInfo['custom_screen_folder'] ?? $this->page->sandbox_custom_screen_folder;
                $this->currentDomain = $this->page->sandbox_domain;
                $this->selectedCustomScreen = $this->currentCustomScreenFolder;
                
                // 도메인별로 화면 그룹화
                $this->groupScreensByDomain();
            }
        }
    }

    public function selectScreen($screenFolder)
    {
        $this->selectedCustomScreen = $screenFolder;
        $this->dropdownOpen = false; // 드롭다운 닫기
        $this->updateCustomScreen();
    }

    public function updateCustomScreen()
    {
        try {
            if (!$this->page) {
                session()->flash('error', '페이지를 찾을 수 없습니다.');
                return;
            }

            $sandboxService = app(SandboxService::class);
            if ($sandboxService->setCustomScreen($this->page, $this->selectedCustomScreen)) {
                $this->currentCustomScreenFolder = $this->selectedCustomScreen;

                // 페이지를 다시 로드하여 현재 정보 업데이트
                $this->page = $this->page->fresh();
                $this->currentDomain = $this->page->sandbox_domain;

                session()->flash('success', '커스텀 화면 설정이 저장되었습니다.');

                // Livewire 컴포넌트 리렌더링만 수행
                $this->dispatch('screen-updated');
            } else {
                session()->flash('error', '설정 저장 중 오류가 발생했습니다.');
            }
        } catch (\Exception $e) {
            session()->flash('error', '오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function closeDropdown()
    {
        $this->dropdownOpen = false;
    }

    public function closeDomainDropdown()
    {
        $this->domainDropdownOpen = false;
    }

    public function selectDomain($domain)
    {
        $this->currentDomain = $domain;
        $this->domainDropdownOpen = false;
        $this->updateCurrentDomainScreens();
    }

    private function groupScreensByDomain()
    {
        // 사용 가능한 도메인 목록 추출
        $domains = [];
        foreach ($this->availableScreens as $screen) {
            if (!in_array($screen['domain'], $domains)) {
                $domains[] = $screen['domain'];
            }
        }
        
        // 도메인을 표시용으로 변환
        $this->availableDomains = array_map(function($domain) {
            return [
                'id' => $domain,
                'name' => str_replace('-', ' ', ucfirst($domain)),
                'display_name' => str_replace(['-domain-', '-'], [' ', ' '], ucwords($domain, '-'))
            ];
        }, $domains);

        // 현재 도메인의 화면들 업데이트
        $this->updateCurrentDomainScreens();
    }

    private function updateCurrentDomainScreens()
    {
        if (empty($this->currentDomain)) {
            $this->currentDomainScreens = $this->availableScreens;
        } else {
            $this->currentDomainScreens = array_filter($this->availableScreens, function($screen) {
                return $screen['domain'] === $this->currentDomain;
            });
        }
    }

    public function render()
    {
        return view('service.project-dashboard.700-livewire-custom-screen-dropdown');
    }
}
