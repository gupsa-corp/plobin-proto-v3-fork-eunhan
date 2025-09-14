<?php

namespace App\Livewire\Sandbox;

use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;

class FileEditor extends Component implements HasForms
{
    use InteractsWithForms;

    public array $openTabs = [];
    public string $activeTab = '';
    public array $fileContents = [];
    public array $expandedFolders = [];
    public string $currentStoragePath = '';

    public ?array $data = [];

    public function mount()
    {
        $this->currentStoragePath = $this->getCurrentStoragePath();
        $this->createDefaultFiles();

        // 첫 번째 파일을 기본으로 열기
        $files = $this->getFileTree();
        if (!empty($files)) {
            $firstFile = $this->findFirstFile($files);
            if ($firstFile) {
                $this->openFile($firstFile);
            }
        }

        $this->form->fill($this->data);
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
            'template.blade.php' => "@if(isset(\$user))\n<div class=\"user-info\">\n  <h3>안녕하세요, {{ \$user['name'] }}님!</h3>\n  <p>이메일: {{ \$user['email'] }}</p>\n</div>\n@else\n<div class=\"guest-info\">\n  <p>로그인이 필요합니다.</p>\n</div>\n@endif",
            'data.json' => "{\n  \"user\": {\n    \"name\": \"김개발\",\n    \"email\": \"dev@example.com\",\n    \"role\": \"developer\"\n  },\n  \"settings\": {\n    \"theme\": \"light\",\n    \"language\": \"ko\"\n  }\n}"
        ];

        foreach ($defaultFiles as $filename => $content) {
            $filePath = $this->currentStoragePath . '/' . $filename;
            if (!File::exists($filePath)) {
                File::put($filePath, $content);
            }
        }
    }

    private function findFirstFile($files)
    {
        foreach ($files as $file) {
            if ($file['type'] === 'file') {
                return $file['path'];
            }
            if ($file['type'] === 'folder' && !empty($file['children'])) {
                $found = $this->findFirstFile($file['children']);
                if ($found) return $found;
            }
        }
        return null;
    }

    public function openFile($filePath)
    {
        \Log::info('openFile called with: ' . $filePath);

        $relativePath = str_replace($this->currentStoragePath . '/', '', $filePath);
        \Log::info('Relative path: ' . $relativePath);

        // 이미 열려있는지 확인
        if (!in_array($relativePath, $this->openTabs)) {
            $this->openTabs[] = $relativePath;
        }

        $this->activeTab = $relativePath;
        \Log::info('Active tab set to: ' . $this->activeTab);

        // 파일 내용 로드
        if (File::exists($filePath)) {
            $this->fileContents[$relativePath] = File::get($filePath);
            \Log::info('File loaded, content length: ' . strlen($this->fileContents[$relativePath]));
        } else {
            $this->fileContents[$relativePath] = '';
            \Log::info('File not found, using empty content');
        }

        // 폼 데이터 업데이트
        $this->data['currentFileContent'] = $this->fileContents[$relativePath];
        $this->form->fill($this->data);
        \Log::info('Form filled with content');
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
            }
        }
    }

    public function setActiveTab($tabPath)
    {
        if (in_array($tabPath, $this->openTabs)) {
            $this->activeTab = $tabPath;
            $this->data['currentFileContent'] = $this->fileContents[$tabPath] ?? '';
            $this->form->fill($this->data);
        }
    }

    public function toggleFolder($folderPath)
    {
        if (in_array($folderPath, $this->expandedFolders)) {
            $this->expandedFolders = array_values(array_filter($this->expandedFolders, fn($path) => $path !== $folderPath));
        } else {
            $this->expandedFolders[] = $folderPath;
        }
    }

    public function updateFileContent($content)
    {
        if ($this->activeTab && isset($this->fileContents[$this->activeTab])) {
            $this->fileContents[$this->activeTab] = $content;

            // 실제 파일에 저장
            $filePath = $this->currentStoragePath . '/' . $this->activeTab;
            File::put($filePath, $content);
        }
    }

    public function createFile($fileName, $parentPath = '')
    {
        if (!$fileName) return;

        $fullPath = $parentPath ? $this->currentStoragePath . '/' . $parentPath . '/' . $fileName : $this->currentStoragePath . '/' . $fileName;

        if (!File::exists($fullPath)) {
            File::put($fullPath, '');
            $this->openFile($fullPath);
        }
    }

    public function createFolder($folderName, $parentPath = '')
    {
        if (!$folderName) return;

        $fullPath = $parentPath ? $this->currentStoragePath . '/' . $parentPath . '/' . $folderName : $this->currentStoragePath . '/' . $folderName;

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
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
                    'extension' => pathinfo($name, PATHINFO_EXTENSION)
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

    #[Computed]
    public function currentFileContent()
    {
        return $this->activeTab ? ($this->fileContents[$this->activeTab] ?? '') : '';
    }


    #[Computed]
    public function compiled(): string
    {
        // HTML, CSS, JS 파일들을 찾아서 조합
        $html = $this->getFileContentByExtension('html') ?? '<div>HTML 파일이 없습니다.</div>';
        $css = $this->getFileContentByExtension('css') ?? '';
        $js = $this->getFileContentByExtension('js') ?? '';
        $blade = $this->getFileContentByExtension('blade.php') ?? '';
        $json = $this->getFileContentByExtension('json') ?? '{}';

        // JSON 데이터 파싱
        $data = [];
        try {
            $data = json_decode($json, true) ?: [];
        } catch (\Throwable $e) {
            $data = [];
        }

        // Blade 안전 렌더링
        $bladeHtml = '';
        if ($blade) {
        }

        // 최종 HTML 조합
        return <<<HTML
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>파일 에디터 미리보기</title>
    <style>
{$css}
    </style>
</head>
<body>
{$html}
{$bladeHtml}
<script>
try {
{$js}
} catch (e) {
    console.error('JavaScript 오류:', e);
}
</script>
</body>
</html>
HTML;
    }

    private function getFileContentByExtension($extension)
    {
        foreach ($this->fileContents as $path => $content) {
            if (str_ends_with($path, '.' . $extension)) {
                return $content;
            }
        }
        return null;
    }

    public function render()
    {
        return view('700-page-sandbox.704-page-file-editor.700-livewire-file-editor');
    }
}
