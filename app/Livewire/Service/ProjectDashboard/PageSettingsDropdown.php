<?php

namespace App\Livewire\Service\ProjectDashboard;

use App\Models\ProjectPage;
use Livewire\Component;

class PageSettingsDropdown extends Component
{
    public $orgId;
    public $projectId;
    public $pageId;
    public $page;

    public function mount($orgId, $projectId, $pageId)
    {
        $this->orgId = $orgId;
        $this->projectId = $projectId;
        $this->pageId = $pageId;
        
        $this->page = ProjectPage::find($pageId);
    }

    public function render()
    {
        return view('service.project-dashboard.701-livewire-page-settings-dropdown');
    }
}
