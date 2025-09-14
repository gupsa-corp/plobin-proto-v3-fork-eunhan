<?php

namespace App\Livewire\Sandbox\CodeEditor;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;

class Component extends LivewireComponent implements HasForms
{
    use InteractsWithForms;

    public array $openTabs = [];
    public string $activeTab = '';
    public array $fileContents = [];
    public string $currentStoragePath = '';
    
    public ?array $data = [];

    protected $listeners = ['openFile', 'closeTab', 'setActiveTab', 'file-selected', 'saveCurrentFile'];

    public function mount()
    {
        $this->currentStoragePath = $this->getCurrentStoragePath();
        $this->form->fill($this->data);
    }

    private function getCurrentStoragePath()
    {
        $currentStorage = Session::get('sandbox_storage', '1');
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $currentStorage);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('코드 에디터')
                    ->schema([
                        Textarea::make('currentFileContent')
                            ->label('')
                            ->rows(25)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->updateFileContent($state);
                            })
                            ->extraAttributes([
                                'style' => 'font-family: "Fira Code", "SF Mono", Monaco, Inconsolata, "Roboto Mono", "Source Code Pro", monospace; font-size: 14px; line-height: 1.5;',
                                'spellcheck' => 'false'
                            ])
                    ])
                    ->visible(fn () => !empty($this->activeTab))
            ])
            ->statePath('data');
    }

    public function openFile($filePath)
    {
        $relativePath = str_replace($this->currentStoragePath . '/', '', $filePath);

        // 이미 열려있는지 확인
        if (!in_array($relativePath, $this->openTabs)) {
            $this->openTabs[] = $relativePath;
        }

        $this->activeTab = $relativePath;

        // 파일 내용 로드
        if (File::exists($filePath)) {
            $this->fileContents[$relativePath] = File::get($filePath);
        } else {
            $this->fileContents[$relativePath] = '';
        }

        // 폼 데이터 업데이트
        $this->data['currentFileContent'] = $this->fileContents[$relativePath];
        $this->form->fill($this->data);

        // 버전 관리 컴포넌트에 현재 파일 알림
        $this->dispatch('setCurrentFile', filePath: $filePath);
    }

    public function closeTab($tabPath)
    {
        $this->openTabs = array_values(array_filter($this->openTabs, fn($tab) => $tab !== $tabPath));
        unset($this->fileContents[$tabPath]);

        if ($this->activeTab === $tabPath) {
            $this->activeTab = !empty($this->openTabs) ? $this->openTabs[0] : '';
            if ($this->activeTab) {
                $this->data['currentFileContent'] = $this->fileContents[$this->activeTab] ?? '';
                $this->form->fill($this->data);
                
                // 새로운 활성 탭을 버전 관리에 알림
                $filePath = $this->currentStoragePath . '/' . $this->activeTab;
                $this->dispatch('setCurrentFile', filePath: $filePath);
            }
        }
    }

    public function setActiveTab($tabPath)
    {
        if (in_array($tabPath, $this->openTabs)) {
            $this->activeTab = $tabPath;
            $this->data['currentFileContent'] = $this->fileContents[$tabPath] ?? '';
            $this->form->fill($this->data);
            
            // 활성 탭 변경을 버전 관리에 알림
            $filePath = $this->currentStoragePath . '/' . $tabPath;
            $this->dispatch('setCurrentFile', filePath: $filePath);
        }
    }

    public function updateFileContent($content)
    {
        if ($this->activeTab && isset($this->fileContents[$this->activeTab])) {
            $this->fileContents[$this->activeTab] = $content;

            // 실제 파일에 저장
            $filePath = $this->currentStoragePath . '/' . $this->activeTab;
            File::put($filePath, $content);
            
            // 미리보기 업데이트 알림
            $this->dispatch('content-updated');
        }
    }

    public function getCurrentFileContent()
    {
        return $this->activeTab ? ($this->fileContents[$this->activeTab] ?? '') : '';
    }

    public function getAllFileContents()
    {
        return $this->fileContents;
    }

    public function saveCurrentFile()
    {
        if ($this->activeTab && isset($this->fileContents[$this->activeTab])) {
            $filePath = $this->currentStoragePath . '/' . $this->activeTab;
            File::put($filePath, $this->fileContents[$this->activeTab]);
            
            session()->flash('success', '파일이 저장되었습니다.');
        }
    }

    public function render()
    {
        return view('700-page-sandbox.704-page-file-editor.240-code-editor-component');
    }
}