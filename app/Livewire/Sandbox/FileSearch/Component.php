<?php

namespace App\Livewire\Sandbox\FileSearch;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;

class Component extends LivewireComponent
{
    public string $searchTerm = '';
    public array $searchResults = [];
    public bool $isSearching = false;
    public string $currentStoragePath = '';
    
    protected $listeners = ['performSearch'];

    public function mount()
    {
        $this->currentStoragePath = $this->getCurrentStoragePath();
    }

    private function getCurrentStoragePath()
    {
        $currentStorage = Session::get('sandbox_storage', '1');
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $currentStorage);
    }

    public function updatedSearchTerm()
    {
        if (strlen($this->searchTerm) >= 2) {
            $this->performSearch();
        } else {
            $this->searchResults = [];
        }
    }

    public function performSearch()
    {
        if (strlen($this->searchTerm) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->isSearching = true;
        $this->searchResults = [];

        try {
            $this->searchInDirectory($this->currentStoragePath);
        } catch (\Exception $e) {
            // 에러 처리
        }

        $this->isSearching = false;
    }

    private function searchInDirectory($directory, $relativePath = '')
    {
        if (!File::exists($directory) || !File::isDirectory($directory)) {
            return;
        }

        $items = File::glob($directory . '/*');

        foreach ($items as $item) {
            $name = basename($item);
            $currentRelativePath = $relativePath ? $relativePath . '/' . $name : $name;

            if (File::isDirectory($item)) {
                // 폴더명 검색
                if (stripos($name, $this->searchTerm) !== false) {
                    $this->addSearchResult($item, $currentRelativePath, 'folder', 'filename');
                }
                
                // 하위 폴더 재귀 검색
                $this->searchInDirectory($item, $currentRelativePath);
            } else {
                // 파일명 검색
                if (stripos($name, $this->searchTerm) !== false) {
                    $this->addSearchResult($item, $currentRelativePath, 'file', 'filename');
                }

                // 파일 내용 검색 (텍스트 파일만)
                if ($this->isTextFile($item)) {
                    $this->searchInFileContent($item, $currentRelativePath);
                }
            }
        }
    }

    private function searchInFileContent($filePath, $relativePath)
    {
        try {
            $content = File::get($filePath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                if (stripos($line, $this->searchTerm) !== false) {
                    $this->addSearchResult($filePath, $relativePath, 'file', 'content', [
                        'line' => $lineNumber + 1,
                        'preview' => trim($line),
                        'highlighted' => $this->highlightSearchTerm($line)
                    ]);
                }
            }
        } catch (\Exception $e) {
            // 파일 읽기 실패는 무시
        }
    }

    private function addSearchResult($fullPath, $relativePath, $type, $matchType, $extra = [])
    {
        $this->searchResults[] = [
            'fullPath' => $fullPath,
            'relativePath' => $relativePath,
            'name' => basename($fullPath),
            'type' => $type,
            'matchType' => $matchType,
            'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
            'extra' => $extra
        ];
    }

    private function highlightSearchTerm($text)
    {
        return preg_replace('/(' . preg_quote($this->searchTerm, '/') . ')/i', '<mark>$1</mark>', htmlspecialchars($text));
    }

    private function isTextFile($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $textExtensions = ['txt', 'html', 'css', 'js', 'php', 'json', 'md', 'xml', 'yml', 'yaml', 'ini', 'conf'];
        
        return in_array($extension, $textExtensions);
    }

    public function openSearchResult($fullPath)
    {
        $this->dispatch('file-selected', path: $fullPath);
    }

    public function clearSearch()
    {
        $this->searchTerm = '';
        $this->searchResults = [];
    }

    public function render()
    {
        return view('700-page-sandbox.704-page-file-editor.210-file-search-component');
    }
}