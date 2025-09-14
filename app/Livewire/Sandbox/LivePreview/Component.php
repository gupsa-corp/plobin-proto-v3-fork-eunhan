<?php

namespace App\Livewire\Sandbox\LivePreview;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;

class Component extends LivewireComponent
{
    public array $fileContents = [];
    public string $currentStoragePath = '';
    public string $previewMode = 'combined'; // combined, html-only, mobile
    public bool $autoRefresh = true;
    
    protected $listeners = ['content-updated', 'updateFileContents'];

    public function mount()
    {
        $this->currentStoragePath = $this->getCurrentStoragePath();
        $this->loadAllFiles();
    }

    private function getCurrentStoragePath()
    {
        $currentStorage = Session::get('sandbox_storage', '1');
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $currentStorage);
    }

    private function loadAllFiles()
    {
        if (!File::exists($this->currentStoragePath)) {
            return;
        }

        $files = File::allFiles($this->currentStoragePath);
        
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            
            // .versions 디렉토리 제외
            if (str_starts_with($relativePath, '.versions/')) {
                continue;
            }
            
            // 텍스트 파일만 로드
            if ($this->isTextFile($file->getPathname())) {
                $this->fileContents[$relativePath] = File::get($file->getPathname());
            }
        }
    }

    public function updateFileContents($contents)
    {
        $this->fileContents = $contents;
    }

    public function refreshPreview()
    {
        $this->loadAllFiles();
    }

    public function setPreviewMode($mode)
    {
        $this->previewMode = $mode;
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    #[Computed]
    public function compiled(): string
    {
        // HTML, CSS, JS 파일들을 찾아서 조합
        $html = $this->getFileContentByExtension('html') ?? '<div class="placeholder">HTML 파일이 없습니다.</div>';
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

        // Blade 렌더링 (간단한 구현)
        $bladeHtml = $this->renderSimpleBlade($blade, $data);

        // 미리보기 모드에 따른 처리
        return match($this->previewMode) {
            'html-only' => $this->compileHtmlOnly($html),
            'mobile' => $this->compileMobileView($html, $css, $js, $bladeHtml),
            default => $this->compileCombinedView($html, $css, $js, $bladeHtml)
        };
    }

    private function compileHtmlOnly($html)
    {
        return <<<HTML
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>HTML 전용 미리보기</title>
</head>
<body>
{$html}
</body>
</html>
HTML;
    }

    private function compileMobileView($html, $css, $js, $bladeHtml)
    {
        return <<<HTML
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
    <title>모바일 미리보기</title>
    <style>
body { font-size: 16px; padding: 10px; }
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

    private function compileCombinedView($html, $css, $js, $bladeHtml)
    {
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

    private function renderSimpleBlade($blade, $data)
    {
        if (!$blade) return '';

        // 간단한 Blade 문법 처리
        $html = $blade;
        
        // {{ $variable }} 처리
        $html = preg_replace_callback('/\{\{\s*\$(\w+)(?:\[\'(\w+)\'\])?\s*\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            $subKey = $matches[2] ?? null;
            
            if ($subKey) {
                return $data[$key][$subKey] ?? '';
            }
            
            return is_array($data[$key] ?? null) ? json_encode($data[$key]) : ($data[$key] ?? '');
        }, $html);

        // @if/@else/@endif 처리 (매우 간단한 구현)
        $html = preg_replace('/@if\s*\([^)]+\)\s*/', '', $html);
        $html = preg_replace('/@else\s*/', '', $html);
        $html = preg_replace('/@endif\s*/', '', $html);

        return $html;
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

    private function isTextFile($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $textExtensions = ['txt', 'html', 'css', 'js', 'php', 'json', 'md', 'xml', 'yml', 'yaml'];
        
        return in_array($extension, $textExtensions);
    }

    public function render()
    {
        return view('700-page-sandbox.704-page-file-editor.250-live-preview-component');
    }
}