<?php

namespace App\Livewire\Sandbox\ApiCreator;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\File;

class Component extends LivewireComponent
{
    public $apiCode = '';
    public $apiName = '';
    public $apiDescription = '';
    public $selectedTemplate = '';
    public $httpMethod = 'GET';
    public $apiRoute = '';
    public $generateController = false;

    public function mount()
    {
        $this->apiRoute = 'api/' . strtolower(str_replace(' ', '-', $this->apiName));
    }

    public function render()
    {
        return view('700-page-sandbox.703-livewire-api-creator', [
            'templates' => $this->getTemplates()
        ]);
    }

    public function updatedApiName()
    {
        $this->apiRoute = 'api/' . strtolower(str_replace(' ', '-', $this->apiName));
    }

    public function loadTemplate()
    {
        if (empty($this->selectedTemplate)) {
            return;
        }

        $templates = $this->getTemplates();
        if (isset($templates[$this->selectedTemplate])) {
            $this->apiCode = $templates[$this->selectedTemplate]['code'];
            $this->apiDescription = $templates[$this->selectedTemplate]['description'];
            $this->httpMethod = $templates[$this->selectedTemplate]['method'];
            
            session()->flash('message', "템플릿 '{$templates[$this->selectedTemplate]['name']}'이 로드되었습니다.");
            $this->dispatch('template-loaded');
        }
    }

    public function refreshPreview()
    {
        // 실시간 미리보기 새로고침 - 현재는 클라이언트사이드에서 처리
        session()->flash('message', '미리보기가 새로고침되었습니다.');
    }

    private function getTemplates()
    {
        return [
            'basic_get' => [
                'name' => '기본 GET API',
                'description' => '간단한 데이터 조회 API',
                'method' => 'GET',
                'category' => 'basic',
                'code' => $this->generateBasicGetTemplate()
            ],
            'crud_resource' => [
                'name' => 'CRUD 리소스 API',
                'description' => '완전한 CRUD 작업을 지원하는 리소스 API',
                'method' => 'RESOURCE',
                'category' => 'crud',
                'code' => $this->generateCrudResourceTemplate()
            ],
            'auth_api' => [
                'name' => '인증 API',
                'description' => '로그인/회원가입 등 인증 관련 API',
                'method' => 'POST',
                'category' => 'auth',
                'code' => $this->generateAuthApiTemplate()
            ],
            'file_upload' => [
                'name' => '파일 업로드 API',
                'description' => '파일 업로드를 처리하는 API',
                'method' => 'POST',
                'category' => 'file',
                'code' => $this->generateFileUploadTemplate()
            ],
            'pagination_api' => [
                'name' => '페이지네이션 API',
                'description' => '페이지네이션이 포함된 목록 조회 API',
                'method' => 'GET',
                'category' => 'advanced',
                'code' => $this->generatePaginationTemplate()
            ],
            'search_api' => [
                'name' => '검색 API',
                'description' => '키워드 검색 기능이 있는 API',
                'method' => 'GET',
                'category' => 'advanced',
                'code' => $this->generateSearchTemplate()
            ]
        ];
    }

    private function generateBasicGetTemplate()
    {
        $className = ucfirst(str_replace(' ', '', $this->apiName ?: 'Example'));
        return "<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class {$className}Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request \$request)
    {
        // 데이터 조회 로직
        \$data = [
            \"message\" => \"Hello from {$this->apiName} API\",
            \"timestamp\" => now(),
            \"data\" => []
        ];
        
        return response()->json(\$data);
    }
}";
    }

    private function generateCrudResourceTemplate()
    {
        $className = ucfirst(str_replace(' ', '', $this->apiName ?: 'Example'));
        return "<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class {$className}Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            \"data\" => [],
            \"message\" => \"Success\"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request)
    {
        \$request->validate([
            // 유효성 검사 규칙
        ]);

        // 생성 로직
        
        return response()->json([
            \"message\" => \"Resource created successfully\",
            \"data\" => []
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string \$id)
    {
        // 단일 리소스 조회
        
        return response()->json([
            \"data\" => [],
            \"message\" => \"Success\"
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, string \$id)
    {
        \$request->validate([
            // 유효성 검사 규칙
        ]);

        // 업데이트 로직
        
        return response()->json([
            \"message\" => \"Resource updated successfully\",
            \"data\" => []
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string \$id)
    {
        // 삭제 로직
        
        return response()->json([
            \"message\" => \"Resource deleted successfully\"
        ]);
    }
}";
    }

    private function generateAuthApiTemplate()
    {
        return "<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\Hash;
use App\\Models\\User;

class AuthController extends Controller
{
    /**
     * User login
     */
    public function login(Request \$request)
    {
        \$request->validate([
            \"email\" => \"required|email\",
            \"password\" => \"required\"
        ]);

        if (Auth::attempt(\$request->only(\"email\", \"password\"))) {
            \$user = Auth::user();
            \$token = \$user->createToken(\"API Token\")->plainTextToken;

            return response()->json([
                \"message\" => \"Login successful\",
                \"token\" => \$token,
                \"user\" => \$user
            ]);
        }

        return response()->json([
            \"message\" => \"Invalid credentials\"
        ], 401);
    }

    /**
     * User registration
     */
    public function register(Request \$request)
    {
        \$request->validate([
            \"name\" => \"required|string|max:255\",
            \"email\" => \"required|string|email|max:255|unique:users\",
            \"password\" => \"required|string|min:8|confirmed\"
        ]);

        \$user = User::create([
            \"name\" => \$request->name,
            \"email\" => \$request->email,
            \"password\" => Hash::make(\$request->password)
        ]);

        \$token = \$user->createToken(\"API Token\")->plainTextToken;

        return response()->json([
            \"message\" => \"Registration successful\",
            \"token\" => \$token,
            \"user\" => \$user
        ], 201);
    }

    /**
     * User logout
     */
    public function logout(Request \$request)
    {
        \$request->user()->currentAccessToken()->delete();

        return response()->json([
            \"message\" => \"Logged out successfully\"
        ]);
    }
}";
    }

    private function generateFileUploadTemplate()
    {
        return "<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Storage;

class FileUploadController extends Controller
{
    /**
     * Handle file upload
     */
    public function upload(Request \$request)
    {
        \$request->validate([
            \"file\" => \"required|file|max:2048\", // 2MB 제한
            \"folder\" => \"nullable|string\"
        ]);

        try {
            \$file = \$request->file(\"file\");
            \$folder = \$request->input(\"folder\", \"uploads\");
            
            \$filename = time() . \"_\" . \$file->getClientOriginalName();
            \$path = \$file->storeAs(\$folder, \$filename, \"public\");

            return response()->json([
                \"message\" => \"File uploaded successfully\",
                \"data\" => [
                    \"filename\" => \$filename,
                    \"path\" => \$path,
                    \"url\" => Storage::url(\$path),
                    \"size\" => \$file->getSize(),
                    \"mime_type\" => \$file->getMimeType()
                ]
            ]);

        } catch (\\Exception \$e) {
            return response()->json([
                \"message\" => \"File upload failed\",
                \"error\" => \$e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete uploaded file
     */
    public function delete(Request \$request)
    {
        \$request->validate([
            \"path\" => \"required|string\"
        ]);

        if (Storage::disk(\"public\")->exists(\$request->path)) {
            Storage::disk(\"public\")->delete(\$request->path);
            
            return response()->json([
                \"message\" => \"File deleted successfully\"
            ]);
        }

        return response()->json([
            \"message\" => \"File not found\"
        ], 404);
    }
}";
    }

    private function generatePaginationTemplate()
    {
        $className = ucfirst(str_replace(' ', '', $this->apiName ?: 'Example'));
        return "<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class {$className}Controller extends Controller
{
    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request \$request)
    {
        \$perPage = \$request->input('per_page', 15);
        \$page = \$request->input('page', 1);
        
        // 실제 데이터 조회 로직을 여기에 구현
        // \$data = Model::paginate(\$perPage);
        
        \$mockData = [
            'current_page' => \$page,
            'data' => [],
            'first_page_url' => url('api/{$this->apiRoute}?page=1'),
            'from' => 1,
            'last_page' => 1,
            'last_page_url' => url('api/{$this->apiRoute}?page=1'),
            'links' => [],
            'next_page_url' => null,
            'path' => url('api/{$this->apiRoute}'),
            'per_page' => \$perPage,
            'prev_page_url' => null,
            'to' => 0,
            'total' => 0
        ];
        
        return response()->json([
            'message' => 'Success',
            'data' => \$mockData
        ]);
    }
}";
    }

    private function generateSearchTemplate()
    {
        $className = ucfirst(str_replace(' ', '', $this->apiName ?: 'Example'));
        return "<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;

class {$className}Controller extends Controller
{
    /**
     * Search resources
     */
    public function search(Request \$request)
    {
        \$request->validate([
            'query' => 'required|string|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        \$query = \$request->input('query');
        \$page = \$request->input('page', 1);
        \$perPage = \$request->input('per_page', 15);
        
        // 실제 검색 로직을 여기에 구현
        // \$results = Model::where('title', 'like', \"%{\$query}%\")
        //                ->orWhere('description', 'like', \"%{\$query}%\")
        //                ->paginate(\$perPage);
        
        return response()->json([
            'message' => 'Search completed',
            'query' => \$query,
            'results' => [
                'data' => [],
                'total' => 0,
                'per_page' => \$perPage,
                'current_page' => \$page
            ]
        ]);
    }
}";
    }

    public function saveApi()
    {
        $this->validate([
            'apiName' => 'required|min:3',
            'apiCode' => 'required|min:10',
            'apiDescription' => 'required|min:5',
        ], [
            'apiName.required' => 'API 이름을 입력하세요.',
            'apiName.min' => 'API 이름은 최소 3자 이상이어야 합니다.',
            'apiCode.required' => 'API 코드를 입력하세요.',
            'apiCode.min' => 'API 코드는 최소 10자 이상이어야 합니다.',
            'apiDescription.required' => 'API 설명을 입력하세요.',
            'apiDescription.min' => 'API 설명은 최소 5자 이상이어야 합니다.',
        ]);

        try {
            $fileName = $this->sanitizeFileName($this->apiName);
            $results = [];

            // 1. Sandbox 디렉토리에 저장 (기존 기능)
            $sandboxPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/api/' . $fileName . '.php');
            
            if (!File::exists(dirname($sandboxPath))) {
                File::makeDirectory(dirname($sandboxPath), 0755, true);
            }

            $fileContent = "<?php\n\n";
            $fileContent .= "/**\n";
            $fileContent .= " * API Name: {$this->apiName}\n";
            $fileContent .= " * Description: {$this->apiDescription}\n";
            $fileContent .= " * HTTP Method: {$this->httpMethod}\n";
            $fileContent .= " * Route: {$this->apiRoute}\n";
            $fileContent .= " * Created: " . now()->format('Y-m-d H:i:s') . "\n";
            $fileContent .= " */\n\n";
            $fileContent .= $this->apiCode;

            File::put($sandboxPath, $fileContent);
            $results[] = "샌드박스: {$sandboxPath}";

            // 2. 실제 컨트롤러 생성 (선택사항)
            if ($this->generateController) {
                $controllerName = ucfirst(str_replace(' ', '', $this->apiName)) . 'Controller';
                $controllerPath = app_path('Http/Controllers/Api/' . $controllerName . '.php');
                
                if (!File::exists(dirname($controllerPath))) {
                    File::makeDirectory(dirname($controllerPath), 0755, true);
                }

                File::put($controllerPath, $this->apiCode);
                $results[] = "컨트롤러: {$controllerPath}";

                // 3. 라우트 파일에 추가 제안
                $routeContent = $this->generateRouteCode();
                $results[] = "제안된 라우트:\n{$routeContent}";
            }

            $message = "API '{$this->apiName}'가 성공적으로 저장되었습니다.\n" . implode("\n", $results);
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            session()->flash('error', 'API 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function testApi()
    {
        $this->validate([
            'apiCode' => 'required|min:10',
        ], [
            'apiCode.required' => 'API 코드를 입력하세요.',
            'apiCode.min' => 'API 코드는 최소 10자 이상이어야 합니다.',
        ]);

        try {
            // 기본적인 PHP 문법 검사
            if (!$this->validatePhpSyntax($this->apiCode)) {
                session()->flash('error', 'PHP 문법 오류가 있습니다. 코드를 확인하세요.');
                return;
            }

            session()->flash('message', 'API 코드 문법 검사가 완료되었습니다. 문법적 오류가 없습니다.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'API 테스트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    private function sanitizeFileName($filename)
    {
        // 파일명에서 특수문자 제거하고 안전한 이름으로 변환
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        return trim($filename, '_');
    }

    private function validatePhpSyntax($code)
    {
        // PHP 문법 검사를 위한 임시 파일 생성
        $tempFile = tempnam(sys_get_temp_dir(), 'php_syntax_check');
        file_put_contents($tempFile, $code);
        
        $output = [];
        $return_var = 0;
        exec("php -l {$tempFile} 2>&1", $output, $return_var);
        
        unlink($tempFile);
        
        return $return_var === 0;
    }

    private function generateRouteCode()
    {
        $controllerName = ucfirst(str_replace(' ', '', $this->apiName)) . 'Controller';
        $route = $this->apiRoute;
        
        $routeCode = "// routes/api.php에 추가할 라우트 코드:\n";
        
        switch ($this->httpMethod) {
            case 'GET':
                $routeCode .= "Route::get('{$route}', [App\\Http\\Controllers\\Api\\{$controllerName}::class, 'index']);";
                break;
                
            case 'POST':
                $routeCode .= "Route::post('{$route}', [App\\Http\\Controllers\\Api\\{$controllerName}::class, 'store']);";
                break;
                
            case 'RESOURCE':
                $routeCode .= "Route::apiResource('{$route}', App\\Http\\Controllers\\Api\\{$controllerName}::class);";
                break;
                
            default:
                $routeCode .= "Route::{$this->httpMethod}('{$route}', [App\\Http\\Controllers\\Api\\{$controllerName}::class, 'handle']);";
        }
        
        return $routeCode;
    }

    public function generateApiDoc()
    {
        if (empty($this->apiCode) || empty($this->apiName)) {
            session()->flash('error', 'API 코드와 이름을 입력하세요.');
            return;
        }

        $doc = "# {$this->apiName} API 문서\n\n";
        $doc .= "## 설명\n{$this->apiDescription}\n\n";
        $doc .= "## 엔드포인트\n";
        $doc .= "- **URL**: `/{$this->apiRoute}`\n";
        $doc .= "- **Method**: `{$this->httpMethod}`\n\n";
        
        if ($this->httpMethod === 'RESOURCE') {
            $doc .= "### 사용 가능한 엔드포인트\n";
            $doc .= "- `GET /{$this->apiRoute}` - 목록 조회\n";
            $doc .= "- `POST /{$this->apiRoute}` - 새 리소스 생성\n";
            $doc .= "- `GET /{$this->apiRoute}/{id}` - 단일 리소스 조회\n";
            $doc .= "- `PUT /{$this->apiRoute}/{id}` - 리소스 업데이트\n";
            $doc .= "- `DELETE /{$this->apiRoute}/{id}` - 리소스 삭제\n\n";
        }
        
        $doc .= "## 응답 예시\n";
        $doc .= "```json\n";
        $doc .= "{\n";
        $doc .= "  \"message\": \"Success\",\n";
        $doc .= "  \"data\": []\n";
        $doc .= "}\n";
        $doc .= "```\n\n";
        
        $doc .= "## 생성일시\n" . now()->format('Y-m-d H:i:s') . "\n";

        // 문서 저장
        $fileName = $this->sanitizeFileName($this->apiName);
        $docPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/api/docs/' . $fileName . '.md');
        
        if (!File::exists(dirname($docPath))) {
            File::makeDirectory(dirname($docPath), 0755, true);
        }
        
        File::put($docPath, $doc);
        
        session()->flash('message', "API 문서가 생성되었습니다: {$docPath}");
    }
}