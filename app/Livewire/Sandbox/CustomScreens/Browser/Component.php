<?php

namespace App\Livewire\Sandbox\CustomScreens\Browser;

use Livewire\Component as LivewireComponent;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\File;
use App\Services\SandboxContextService;

class Component extends LivewireComponent
{
    protected $sandboxContextService;
    public $screens = [];

    #[Url]
    public $search = '';

    #[Url]
    public $filterType = '';

    #[Url]
    public $filterDomain = '';

    #[Url(as: 'screen')]
    public $selectedScreenId = null;

    public $selectedScreen = null;

    #[Url]
    public $previewMode = false;

    public function mount()
    {
        $this->sandboxContextService = app(SandboxContextService::class);

        // 샌드박스가 설정되어 있는 경우에만 화면을 로드
        try {
            $this->sandboxContextService->getCurrentSandbox();
            $this->loadScreens();

            // URL에서 선택된 화면이 있다면 해당 화면을 선택 (화면 목록 로딩 후)
            if ($this->selectedScreenId) {
                $this->selectScreenById($this->selectedScreenId);
            }
        } catch (\Exception $e) {
            // 샌드박스가 설정되지 않은 경우 빈 화면 목록으로 시작
            $this->screens = [];
        }
    }

    public function render()
    {
        try {
            $currentSandbox = $this->sandboxContextService->getCurrentSandbox();
        } catch (\Exception $e) {
            $currentSandbox = null;
        }

        return view('livewire.sandbox.custom-screens.browser-component', [
            'selectedSandbox' => $currentSandbox,
            'availableDomains' => $this->getAvailableDomains(),
            'availableScreenTypes' => $this->getAvailableScreenTypes()
        ]);
    }

    public function loadScreens()
    {
        try {
            $this->screens = $this->getScreensFromTemplate();
        } catch (\Exception $e) {
            $this->screens = [];
        }

        $this->applyFilters();
    }

    private function getScreensFromTemplate()
    {
        $screens = [];
        $templatePath = $this->sandboxContextService->getSandboxPath();

        if (File::exists($templatePath)) {
            // Domain 폴더들 찾기 (100-domain-pms, 101-domain-rfx 등)
            $domainFolders = File::directories($templatePath);

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);
                
                // domain으로 시작하는 폴더만 처리
                if (!preg_match('/^\d+-domain-/', $domainName)) {
                    continue;
                }

                // 각 domain 내의 screen 폴더들 찾기
                $screenFolders = File::directories($domainFolder);

                foreach ($screenFolders as $screenFolder) {
                    $screenFolderName = basename($screenFolder);
                    
                    // screen으로 시작하는 폴더만 처리
                    if (!preg_match('/^\d+-screen-/', $screenFolderName)) {
                        continue;
                    }

                    $contentFile = $screenFolder . '/000-content.blade.php';

                    if (File::exists($contentFile)) {
                        // Domain 정보 추출
                        $domainParts = explode('-', $domainName);
                        $domainId = $domainParts[0] ?? '000';
                        $domainType = $domainParts[2] ?? 'unknown';

                        // Screen 정보 추출
                        $screenParts = explode('-', $screenFolderName, 3);
                        $screenId = $screenParts[0] ?? '000';
                        $screenType = $screenParts[1] ?? 'screen';
                        $screenName = $screenParts[2] ?? 'unnamed';

                        // 파일 내용 읽기
                        $fileContent = File::get($contentFile);

                        // 고유한 ID 생성 (domain + screen)
                        $uniqueId = $domainId . '-' . $screenId;

                        $screens[] = [
                            'id' => $uniqueId,
                            'title' => str_replace('-', ' ', ucwords($screenName, '-')),
                            'description' => ucwords($domainType, '-') . ' 도메인 - ' . str_replace('-', ' ', ucwords($screenName, '-')),
                            'type' => $screenType,
                            'domain_id' => $domainId,
                            'domain_name' => $domainType,
                            'screen_id' => $screenId,
                            'screen_name' => $screenName,
                            'folder_name' => $screenFolderName,
                            'domain_folder_name' => $domainName,
                            'file_path' => $domainName . '/' . $screenFolderName . '/000-content.blade.php',
                            'created_at' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                            'file_exists' => true,
                            'full_path' => $contentFile,
                            'file_size' => File::size($contentFile),
                            'file_modified' => date('Y-m-d H:i:s', File::lastModified($contentFile)),
                            'is_template' => true,
                            'blade_template' => $fileContent, // 렌더러가 기대하는 템플릿 데이터 추가
                        ];
                    }
                }
            }
        }

        // 생성 날짜 기준 내림차순 정렬
        usort($screens, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        return $screens;
    }

    public function selectScreen($id)
    {
        $this->selectedScreenId = $id;
        $this->selectScreenById($id);

        // JavaScript를 통해 URL 업데이트
        $this->dispatch('update-url', [
            'screen' => $id,
            'previewMode' => $this->previewMode ? '1' : '0'
        ]);
    }

    protected function selectScreenById($id)
    {
        $screen = collect($this->screens)->firstWhere('id', $id);
        if ($screen) {
            $this->selectedScreen = $screen;
            $this->previewMode = true; // 화면 선택시 자동으로 미리보기 모드 활성화
        }
    }

    public function togglePreview()
    {
        $this->previewMode = !$this->previewMode;

        // URL 업데이트
        if ($this->selectedScreenId) {
            $this->dispatch('update-url', [
                'screen' => $this->selectedScreenId,
                'previewMode' => $this->previewMode ? '1' : '0'
            ]);
        }
    }

    public function getPreviewUrl($screenId)
    {
        // 선택된 화면에서 폴더명 찾기
        $screen = collect($this->screens)->firstWhere('id', $screenId);
        if (!$screen) {
            return '#';
        }
        
        // current sandbox, domain 폴더, screen 폴더로 URL 구성
        $storageName = $this->sandboxContextService->getCurrentSandbox();
        $domainFolderName = $screen['domain_folder_name'];
        $screenFolderName = $screen['folder_name'];
        
        return "/sandbox/{$storageName}/{$domainFolderName}/{$screenFolderName}";
    }

    public function openPreviewInNewWindow($screenId)
    {
        // JavaScript로 새 창을 열도록 이벤트 디스패치
        $url = $this->getPreviewUrl($screenId);
        $this->dispatch('openPreviewWindow', url: $url);
    }

    public function editScreen($id)
    {
        // 화면 생성기로 리다이렉트 (템플릿 편집 모드)
        return redirect()->route('sandbox.custom-screen-creator', ['template' => $id]);
    }

    public function duplicateScreen($id)
    {
        try {
            $originalScreen = collect($this->screens)->firstWhere('id', $id);
            if (!$originalScreen) {
                session()->flash('error', '화면을 찾을 수 없습니다.');
                return;
            }

            // 새로운 폴더명 생성 (해당 domain 내에서)
            $domainPath = $this->sandboxContextService->getSandboxPath() . '/' . $originalScreen['domain_folder_name'];
            $newFolderName = $this->generateNewFolderName($domainPath, $originalScreen['folder_name']);

            $sourcePath = $originalScreen['full_path'];
            $targetPath = $domainPath . '/' . $newFolderName . '/000-content.blade.php';

            // 새 폴더 생성
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // 파일 복사
            if (File::exists($sourcePath)) {
                File::copy($sourcePath, $targetPath);
            }

            $this->loadScreens();
            session()->flash('message', '화면이 복사되었습니다: ' . $newFolderName);
        } catch (\Exception $e) {
            session()->flash('error', '화면 복사 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    private function generateNewFolderName($basePath, $originalName)
    {
        $counter = 1;
        do {
            $newName = $originalName . '-copy-' . $counter;
            $counter++;
        } while (File::exists($basePath . '/' . $newName));

        return $newName;
    }

    public function deleteScreen($id)
    {
        try {
            $screen = collect($this->screens)->firstWhere('id', $id);
            if (!$screen) {
                session()->flash('error', '화면을 찾을 수 없습니다.');
                return;
            }

            // 파일 및 폴더 삭제 (템플릿 내에서만)
            $filePath = $screen['full_path'];
            $folderPath = dirname($filePath);

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            if (File::exists($folderPath) && File::isDirectory($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            if ($this->selectedScreen && $this->selectedScreen['id'] == $id) {
                $this->selectedScreen = null;
                $this->selectedScreenId = null;
            }

            $this->loadScreens();
            session()->flash('message', '화면이 삭제되었습니다.');
        } catch (\Exception $e) {
            session()->flash('error', '화면 삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function updatedSearch()
    {
        $this->applyFilters();
    }

    public function updatedFilterType()
    {
        $this->applyFilters();
    }

    public function updatedFilterDomain()
    {
        $this->applyFilters();
    }

    public function updatedSelectedScreenId()
    {
        if ($this->selectedScreenId) {
            $this->selectScreenById($this->selectedScreenId);
        } else {
            $this->selectedScreen = null;
        }
    }

    private function applyFilters()
    {
        $allScreens = $this->screens;

        if (!empty($this->search)) {
            $allScreens = array_filter($allScreens, function($screen) {
                return str_contains(strtolower($screen['title']), strtolower($this->search)) ||
                       str_contains(strtolower($screen['description'] ?? ''), strtolower($this->search));
            });
        }

        if (!empty($this->filterType)) {
            $allScreens = array_filter($allScreens, function($screen) {
                return ($screen['type'] ?? 'dashboard') === $this->filterType;
            });
        }

        if (!empty($this->filterDomain)) {
            $allScreens = array_filter($allScreens, function($screen) {
                return $screen['domain_name'] === $this->filterDomain;
            });
        }

        $this->screens = array_values($allScreens);
    }

    private function getAvailableDomains()
    {
        $domains = [];

        try {
            $templatePath = $this->sandboxContextService->getSandboxPath();
        } catch (\Exception $e) {
            return $domains; // 샌드박스가 설정되지 않은 경우 빈 배열 반환
        }

        if (File::exists($templatePath)) {
            $domainFolders = File::directories($templatePath);

            foreach ($domainFolders as $domainFolder) {
                $domainName = basename($domainFolder);
                
                if (preg_match('/^\d+-domain-(.+)$/', $domainName, $matches)) {
                    $domainType = $matches[1];
                    $domains[] = [
                        'value' => $domainType,
                        'label' => ucfirst(str_replace('-', ' ', $domainType)),
                        'folder' => $domainName
                    ];
                }
            }
        }

        return $domains;
    }

    private function getAvailableScreenTypes()
    {
        try {
            // Get screen types from existing screens
            $allScreens = $this->getScreensFromTemplate();
            $types = array_unique(array_column($allScreens, 'type'));
        } catch (\Exception $e) {
            return []; // 샌드박스가 설정되지 않은 경우 빈 배열 반환
        }
        
        $screenTypes = [];
        foreach ($types as $type) {
            $screenTypes[] = [
                'value' => $type,
                'label' => ucfirst($type)
            ];
        }

        return $screenTypes;
    }

}
