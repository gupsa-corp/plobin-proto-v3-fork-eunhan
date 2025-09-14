<?php

namespace App\Livewire\Sandbox\FileTree;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;

class Component extends LivewireComponent
{
    public array $expandedFolders = [];
    public string $currentStoragePath = '';
    public string $selectedFile = '';
    
    protected $listeners = ['refreshFileTree'];

    public function mount()
    {
        $this->currentStoragePath = $this->getCurrentStoragePath();
        $this->createDefaultFiles();
    }

    private function getCurrentStoragePath()
    {
        $currentStorage = Session::get('sandbox_storage', '1');
        $path = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $currentStorage);

        // 디렉토리가 없으면 생성
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return $path;
    }

    private function createDefaultFiles()
    {
        $defaultFiles = [
            'index.html' => "<div class=\"container\">\n  <h1 class=\"title\">Hello, World!</h1>\n  <p class=\"description\">실시간 파일 에디터입니다.</p>\n</div>",
            'style.css' => ".container {\n  max-width: 800px;\n  margin: 0 auto;\n  padding: 20px;\n  font-family: system-ui, sans-serif;\n}\n\n.title {\n  color: #333;\n  font-size: 2em;\n  margin-bottom: 16px;\n}\n\n.description {\n  color: #666;\n  line-height: 1.5;\n}",
            'script.js' => "document.addEventListener('DOMContentLoaded', function() {\n  const title = document.querySelector('.title');\n  if (title) {\n    title.addEventListener('click', function() {\n      alert('제목을 클릭했습니다!');\n    });\n  }\n});",
        ];

        foreach ($defaultFiles as $filename => $content) {
            $filePath = $this->currentStoragePath . '/' . $filename;
            if (!File::exists($filePath)) {
                File::put($filePath, $content);
            }
        }
    }

    public function toggleFolder($folderPath)
    {
        $relativePath = str_replace($this->currentStoragePath . '/', '', $folderPath);
        
        if (in_array($relativePath, $this->expandedFolders)) {
            $this->expandedFolders = array_values(array_filter($this->expandedFolders, fn($path) => $path !== $relativePath));
        } else {
            $this->expandedFolders[] = $relativePath;
        }
    }

    public function openFile($filePath)
    {
        $this->selectedFile = $filePath;
        $this->dispatch('file-selected', path: $filePath);
    }

    public function createFile($fileName, $parentPath = '')
    {
        if (!$fileName) return;

        $fullPath = $parentPath ? $this->currentStoragePath . '/' . $parentPath . '/' . $fileName : $this->currentStoragePath . '/' . $fileName;

        if (!File::exists($fullPath)) {
            File::put($fullPath, '');
            $this->dispatch('file-created', path: $fullPath);
        }
    }

    public function createFolder($folderName, $parentPath = '')
    {
        if (!$folderName) return;

        $fullPath = $parentPath ? $this->currentStoragePath . '/' . $parentPath . '/' . $folderName : $this->currentStoragePath . '/' . $folderName;

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
            $this->dispatch('folder-created', path: $fullPath);
        }
    }

    public function deleteFile($filePath)
    {
        if (File::exists($filePath)) {
            File::delete($filePath);
            $this->dispatch('file-deleted', path: $filePath);
        }
    }

    public function deleteFolder($folderPath)
    {
        if (File::exists($folderPath) && File::isDirectory($folderPath)) {
            File::deleteDirectory($folderPath);
            
            // 확장된 폴더 목록에서 제거
            $relativePath = str_replace($this->currentStoragePath . '/', '', $folderPath);
            $this->expandedFolders = array_values(array_filter($this->expandedFolders, function($path) use ($relativePath) {
                return !str_starts_with($path, $relativePath);
            }));
            
            $this->dispatch('folder-deleted', path: $folderPath);
        }
    }

    public function renameFile($oldPath, $newName)
    {
        if (!File::exists($oldPath) || !$newName) {
            return;
        }

        $directory = dirname($oldPath);
        $newPath = $directory . '/' . $newName;

        if (!File::exists($newPath)) {
            File::move($oldPath, $newPath);
            $this->dispatch('file-renamed', oldPath: $oldPath, newPath: $newPath);
        }
    }

    #[Computed]
    public function fileTree()
    {
        return $this->getFileTree();
    }

    private function getFileTree($path = null)
    {
        $path = $path ?? $this->currentStoragePath;
        $files = [];

        if (!File::exists($path)) {
            return $files;
        }

        $items = File::glob($path . '/*');

        foreach ($items as $item) {
            // .versions 디렉토리는 숨김
            if (basename($item) === '.versions') {
                continue;
            }

            $name = basename($item);
            $relativePath = str_replace($this->currentStoragePath . '/', '', $item);

            if (File::isDirectory($item)) {
                $files[] = [
                    'name' => $name,
                    'path' => $item,
                    'relativePath' => $relativePath,
                    'type' => 'folder',
                    'expanded' => in_array($relativePath, $this->expandedFolders),
                    'children' => in_array($relativePath, $this->expandedFolders) ? $this->getFileTree($item) : []
                ];
            } else {
                $files[] = [
                    'name' => $name,
                    'path' => $item,
                    'relativePath' => $relativePath,
                    'type' => 'file',
                    'extension' => pathinfo($name, PATHINFO_EXTENSION),
                    'size' => File::size($item),
                    'modified' => File::lastModified($item)
                ];
            }
        }

        // 폴더를 먼저, 그 다음 파일을 정렬
        usort($files, function($a, $b) {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'folder' ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $files;
    }

    public function refreshFileTree()
    {
        // 파일 트리 새로고침을 위해 computed 프로퍼티가 다시 계산되도록 함
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('700-page-sandbox.704-page-file-editor.230-file-tree-component');
    }
}