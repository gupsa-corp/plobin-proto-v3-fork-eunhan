<?php

namespace App\Livewire\Sandbox\CustomScreens\Creator;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use App\Models\SandboxCustomScreen;

class Component extends LivewireComponent
{
    public $editMode = false;
    public $editId = null;

    // 화면 정보
    public $title = '';
    public $description = '';
    public $type = 'dashboard';

    // 코드 에디터
    public $bladeTemplate = '';
    public $livewireComponent = '';

    // 함수 연동
    public $availableFunctions = [];
    public $connectedFunctions = [];
    public $selectedFunction = '';

    // DB 쿼리
    public $dbQueries = [];
    public $newQueryName = '';
    public $newQuerySql = '';

    // 미리보기
    public $previewData = [];
    public $showPreview = false;

    public $currentStorage = '';

    public function mount($edit = null)
    {
        $this->currentStorage = Session::get('sandbox_storage', 'template');
        $this->loadAvailableFunctions();

        if ($edit) {
            $this->editMode = true;
            $this->editId = $edit;
            $this->loadScreenForEdit($edit);
        } else {
            $this->initializeNewScreen();
        }
    }

    public function render()
    {
        return view('livewire.sandbox.custom-screens.creator-component');
    }

    private function loadAvailableFunctions()
    {
        // 함수 브라우저에서 사용 가능한 함수 목록 로드
        $functionsPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->currentStorage}/functions");
        $functions = [];

        if (File::exists($functionsPath)) {
            $directories = File::directories($functionsPath);

            foreach ($directories as $dir) {
                $functionName = basename($dir);
                $releasePath = $dir . '/release/Function.php';

                if (File::exists($releasePath)) {
                    $functions[] = [
                        'name' => $functionName,
                        'path' => $dir,
                        'description' => $this->getFunctionDescription($functionName)
                    ];
                }
            }
        }

        $this->availableFunctions = $functions;
    }

    private function getFunctionDescription($functionName)
    {
        // 함수 설명을 가져오는 로직 (메타데이터 서비스 사용)
        try {
            $metadataPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->currentStorage}/functions/{$functionName}/metadata.json");
            if (File::exists($metadataPath)) {
                $metadata = json_decode(File::get($metadataPath), true);
                return $metadata['description'] ?? "함수: {$functionName}";
            }
        } catch (\Exception $e) {
            // 메타데이터가 없으면 기본 설명 반환
        }

        return "함수: {$functionName}";
    }

    private function initializeNewScreen()
    {
        $this->title = '';
        $this->description = '';
        $this->type = 'dashboard';
        $this->bladeTemplate = $this->getDefaultBladeTemplate();
        $this->livewireComponent = $this->getDefaultLivewireComponent();
        $this->connectedFunctions = [];
        $this->dbQueries = [];
        $this->previewData = [];
    }

    private function loadScreenForEdit($id)
    {
        try {
            $screen = SandboxCustomScreen::where('sandbox_folder', $this->currentStorage)->find($id);
            if ($screen) {
                $this->title = $screen->title;
                $this->description = $screen->description;
                $this->type = $screen->type;

                // 파일에서 실제 내용을 읽어옴
                $filePath = $screen->getFullFilePath();
                if (File::exists($filePath)) {
                    $this->bladeTemplate = File::get($filePath);
                } else {
                    $this->bladeTemplate = $this->getDefaultBladeTemplate();
                }

                // Livewire 컴포넌트는 기본 템플릿으로 설정 (기존 '#' 대신)
                $this->livewireComponent = $this->getDefaultLivewireComponent();

                $this->connectedFunctions = [];
                $this->dbQueries = [];
                $this->previewData = [];
            } else {
                session()->flash('error', '화면을 찾을 수 없습니다.');
                $this->initializeNewScreen();
            }
        } catch (\Exception $e) {
            session()->flash('error', '화면을 불러올 수 없습니다: ' . $e->getMessage());
            $this->initializeNewScreen();
        }
    }

    public function addFunction()
    {
        if (empty($this->selectedFunction)) {
            return;
        }

        $function = collect($this->availableFunctions)->firstWhere('name', $this->selectedFunction);
        if ($function && !collect($this->connectedFunctions)->contains('name', $this->selectedFunction)) {
            $this->connectedFunctions[] = [
                'name' => $this->selectedFunction,
                'description' => $function['description'],
                'binding' => '' // 라이브와이어 프로퍼티와의 바인딩
            ];
        }

        $this->selectedFunction = '';
    }

    public function removeFunction($index)
    {
        unset($this->connectedFunctions[$index]);
        $this->connectedFunctions = array_values($this->connectedFunctions);
    }

    public function addDbQuery()
    {
        if (empty($this->newQueryName) || empty($this->newQuerySql)) {
            return;
        }

        $this->dbQueries[] = [
            'name' => $this->newQueryName,
            'sql' => $this->newQuerySql,
            'binding' => ''
        ];

        $this->newQueryName = '';
        $this->newQuerySql = '';
    }

    public function removeDbQuery($index)
    {
        unset($this->dbQueries[$index]);
        $this->dbQueries = array_values($this->dbQueries);
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'bladeTemplate' => 'required|string',
            'livewireComponent' => 'required|string'
        ]);

        try {
            if ($this->editMode) {
                // 기존 화면 수정
                $screen = SandboxCustomScreen::where('sandbox_folder', $this->currentStorage)->find($this->editId);
                if ($screen) {
                    $screen->update([
                        'title' => $this->title,
                        'description' => $this->description,
                        'type' => $this->type
                    ]);

                    // 파일 내용 업데이트
                    $filePath = $screen->getFullFilePath();
                    if (File::exists(dirname($filePath))) {
                        File::put($filePath, $this->bladeTemplate);
                    }

                    session()->flash('message', '화면이 수정되었습니다.');
                } else {
                    session()->flash('error', '수정할 화면을 찾을 수 없습니다.');
                    return;
                }
            } else {
                // 새 화면 생성
                $folderName = $this->generateFolderName($this->title);
                $filePath = "custom-screens/{$folderName}/000-content.blade.php";

                $screen = SandboxCustomScreen::create([
                    'title' => $this->title,
                    'description' => $this->description,
                    'type' => $this->type,
                    'folder_name' => $folderName,
                    'file_path' => $filePath,
                    'sandbox_folder' => $this->currentStorage
                ]);

                // 실제 파일 생성
                $fullFilePath = $screen->getFullFilePath();
                $directory = dirname($fullFilePath);
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                File::put($fullFilePath, $this->bladeTemplate);

                session()->flash('message', '화면이 생성되었습니다.');
            }

            return redirect()->route('sandbox.custom-screens');
        } catch (\Exception $e) {
            session()->flash('error', '저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('sandbox.custom-screens');
    }

    private function generateFolderName($title)
    {
        // 타이틀을 안전한 폴더명으로 변환
        $folderName = preg_replace('/[^a-zA-Z0-9가-힣\s\-_]/', '', $title);
        $folderName = preg_replace('/\s+/', '-', trim($folderName));
        $folderName = strtolower($folderName);

        // 중복 방지를 위해 타임스탬프 추가
        return sprintf('%03d-screen-%s', $this->getNextScreenNumber(), $folderName);
    }

    private function getNextScreenNumber()
    {
        $maxNumber = SandboxCustomScreen::where('sandbox_folder', $this->currentStorage)
            ->whereRaw("folder_name REGEXP '^[0-9]{3}-screen-'")
            ->max('id');

        return $maxNumber ? $maxNumber + 1 : 1;
    }

    private function getDefaultBladeTemplate()
    {
        return '<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $title }}</h1>

    @if($users)
        <div class="space-y-4">
            @foreach($users as $user)
                <div class="border rounded p-4">
                    <h3 class="font-semibold">{{ $user[\'name\'] }}</h3>
                    <p class="text-gray-600">{{ $user[\'email\'] }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">데이터가 없습니다.</p>
    @endif
</div>';
    }

    private function getDefaultLivewireComponent()
    {
        return '<?php

namespace App\Livewire\CustomScreens;

use Livewire\Component;

class GeneratedScreen extends Component
{
    public $title = "생성된 화면";
    public $users = [];

    public function mount()
    {
        // 연결된 함수들 호출
        $this->loadData();
    }

    public function loadData()
    {
        try {
            // 함수 브라우저의 함수 호출 예제
            // $this->users = CommonFunctions::Function(\'functionName\', \'release\', []);
        } catch (\Exception $e) {
            // 에러 처리
        }
    }

    public function render()
    {
        return view(\'livewire.custom-screens.generated-screen\');
    }
}';
    }

    private function ensureDbPath($dbPath)
    {
        $directory = dirname($dbPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    private function createScreensTableIfNotExists($pdo)
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS custom_screens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                type TEXT DEFAULT 'dashboard',
                blade_template TEXT,
                livewire_component TEXT,
                connected_functions TEXT DEFAULT '[]',
                db_queries TEXT DEFAULT '[]',
                preview_data TEXT DEFAULT '[]',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $pdo->exec($sql);
    }

    private function getSandboxDbPath()
    {
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->currentStorage}/database/sqlite.db");
    }

    private function generateScreenFiles($title)
    {
        try {
            $slug = $this->generateSlug($title);
            $folderNumber = $this->getNextFolderNumber();
            $folderName = "{$folderNumber}-page-{$slug}";

            // 폴더 생성
            $folderPath = resource_path("views/700-page-sandbox/{$folderName}");
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            // 000-index.blade.php 파일 생성
            $indexContent = $this->generateIndexBladeContent($title, $slug);
            File::put("{$folderPath}/000-index.blade.php", $indexContent);

            // 라이브와이어 컴포넌트 생성
            $this->generateLivewireComponent($title, $slug);

            // 블레이드 템플릿 생성
            $this->generateBladeTemplate($slug);

        } catch (\Exception $e) {
            \Log::error('화면 파일 생성 오류: ' . $e->getMessage());
        }
    }

    private function generateSlug($title)
    {
        $slugMap = [
            '조직 목록' => 'organizations-list',
            '사용자 목록' => 'users-list',
            '프로젝트 목록' => 'projects-list',
            '대시보드' => 'dashboard',
            '설정' => 'settings',
            '프로필' => 'profile',
            '알림' => 'notifications',
        ];

        return $slugMap[$title] ?? 'custom-screen-' . time();
    }

    private function getNextFolderNumber()
    {
        $sandboxPath = resource_path('views/700-page-sandbox');
        $folders = File::directories($sandboxPath);

        $maxNumber = 712; // 현재 최대 번호

        foreach ($folders as $folder) {
            $folderName = basename($folder);
            if (preg_match('/^(\d{3})-page-/', $folderName, $matches)) {
                $number = intval($matches[1]);
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        return str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    private function generateIndexBladeContent($title, $slug)
    {
        $componentName = $this->convertSlugToComponentName($slug);

        return "<?php \$common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include(\$common . '.301-layout-head', ['title' => '{$title}'])

<body class=\"bg-gray-100\">
    @include('700-page-sandbox.700-common.400-sandbox-header')

    <div class=\"min-h-screen sandbox-container\">
        <div class=\"sandbox-card\">
            @livewire('sandbox.custom-screens.{$componentName}')
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Filament Scripts -->
    @filamentScripts
</body>
</html>";
    }

    private function generateLivewireComponent($title, $slug)
    {
        $componentName = $this->convertSlugToComponentName($slug);
        $className = $this->convertSlugToClassName($slug);

        $componentPath = app_path("Livewire/Sandbox/CustomScreens/{$className}.php");

        // 디렉토리가 없으면 생성
        $componentDir = dirname($componentPath);
        if (!File::exists($componentDir)) {
            File::makeDirectory($componentDir, 0755, true);
        }

        $componentContent = "<?php

namespace App\Livewire\Sandbox\CustomScreens;

use Livewire\Component;

class {$className} extends Component
{
    public \$title = \"{$title}\";
    {$this->generateComponentProperties()}

    public function mount()
    {
        \$this->loadData();
    }

    public function loadData()
    {
        try {
            {$this->generateSampleData()}
        } catch (\Exception \$e) {
            session()->flash('error', '데이터를 불러오는데 실패했습니다.');
        }
    }

    public function render()
    {
        return view('livewire.sandbox.custom-screens.{$componentName}');
    }
}";

        File::put($componentPath, $componentContent);
    }

    private function generateBladeTemplate($slug)
    {
        $componentName = $this->convertSlugToComponentName($slug);
        $templatePath = resource_path("views/livewire/sandbox/custom-screens/{$componentName}.blade.php");

        // 디렉토리가 없으면 생성
        $templateDir = dirname($templatePath);
        if (!File::exists($templateDir)) {
            File::makeDirectory($templateDir, 0755, true);
        }

        File::put($templatePath, $this->bladeTemplate);
    }

    private function generateComponentProperties()
    {
        // 타입에 따른 기본 프로퍼티 생성
        switch ($this->type) {
            case 'list':
                return "public \$items = [];
    public \$search = '';";
            case 'form':
                return "public \$formData = [];
    public \$isEditing = false;";
            case 'dashboard':
                return "public \$stats = [];
    public \$charts = [];";
            default:
                return "public \$data = [];";
        }
    }

    private function generateSampleData()
    {
        // 타입에 따른 샘플 데이터 생성
        switch ($this->type) {
            case 'list':
                return "// 샘플 데이터
            \$this->items = [
                ['id' => 1, 'name' => '샘플 아이템 1', 'description' => '설명 1'],
                ['id' => 2, 'name' => '샘플 아이템 2', 'description' => '설명 2'],
                ['id' => 3, 'name' => '샘플 아이템 3', 'description' => '설명 3'],
            ];";
            case 'dashboard':
                return "// 샘플 통계 데이터
            \$this->stats = [
                'total' => 100,
                'active' => 85,
                'pending' => 15,
            ];";
            default:
                return "// 샘플 데이터
            \$this->data = ['message' => 'Hello World'];";
        }
    }

    private function convertSlugToComponentName($slug)
    {
        return str_replace('-', '-', $slug);
    }

    private function convertSlugToClassName($slug)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
    }

    private function updateRoutes($title)
    {
        try {
            $slug = $this->generateSlug($title);
            $folderNumber = $this->getNextFolderNumber() - 1; // 방금 생성한 폴더 번호
            $routeName = "sandbox.{$slug}";
            $routePath = "/sandbox/{$slug}";

            // web.php 라우트 추가
            $this->addWebRoute($routePath, $folderNumber, $slug, $routeName);

            // routes-web.php 설정 추가
            $this->addRouteConfig($routePath, $folderNumber, $slug, $routeName);

        } catch (\Exception $e) {
            \Log::error('라우트 업데이트 오류: ' . $e->getMessage());
        }
    }

    private function addWebRoute($routePath, $folderNumber, $slug, $routeName)
    {
        $webRoutesPath = base_path('routes/web.php');
        $webRoutesContent = File::get($webRoutesPath);

        $newRoute = "
// {$this->title} (Generated Custom Screen)
Route::get('{$routePath}', function () {
    return view('700-page-sandbox.{$folderNumber}-page-{$slug}.000-index');
})->name('{$routeName}');";

        // 적절한 위치에 라우트 추가 (organizations-list 라우트 뒤)
        $insertPosition = strpos($webRoutesContent, "Route::get('/sandbox/organizations-list'");
        if ($insertPosition !== false) {
            $endPosition = strpos($webRoutesContent, "\n", strpos($webRoutesContent, '})->name(\'sandbox.organizations-list\')', $insertPosition));
            if ($endPosition !== false) {
                $webRoutesContent = substr_replace($webRoutesContent, $newRoute, $endPosition, 0);
                File::put($webRoutesPath, $webRoutesContent);
            }
        }
    }

    private function addRouteConfig($routePath, $folderNumber, $slug, $routeName)
    {
        $configPath = config_path('routes-web.php');
        $configContent = File::get($configPath);

        $newConfig = "    '{$routePath}' => ['view' => '700-page-sandbox.{$folderNumber}-page-{$slug}.000-index', 'name' => '{$routeName}'],";

        // 마지막 라우트 뒤에 추가
        $insertPosition = strrpos($configContent, '];');
        if ($insertPosition !== false) {
            $configContent = substr_replace($configContent, "\n" . $newConfig . "\n", $insertPosition, 0);
            File::put($configPath, $configContent);
        }
    }
}
