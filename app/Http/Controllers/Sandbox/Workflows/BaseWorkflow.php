<?php

namespace App\Http\Controllers\Sandbox\Workflows;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

abstract class BaseWorkflow
{
    protected $steps = [];
    protected $currentData = null;
    protected $executionLog = [];
    protected $availableFunctions = [];
    protected $globalFunctions = [];
    protected $storageFunctions = [];

    public function __construct()
    {
        $this->loadAvailableFunctions();
    }

    /**
     * 워크플로우 실행 - 하위 클래스에서 구현
     */
    abstract public function execute($input);

    /**
     * 사용 가능한 함수들 로드 (기존 시스템과 연동)
     */
    private function loadAvailableFunctions()
    {
        // GlobalFunctions 로드
        $this->loadGlobalFunctions();

        // Storage Functions 로드 (현재 선택된 샌드박스 기준)
        $this->loadStorageFunctions();

        // 전체 함수 목록 구성
        $this->availableFunctions = array_merge($this->globalFunctions, $this->storageFunctions);
    }

    /**
     * Global Functions 로드
     */
    private function loadGlobalFunctions()
    {
        $globalFunctionsPath = app_path('Http/Sandbox/GlobalFunctions');

        if (!File::exists($globalFunctionsPath)) {
            return;
        }

        $files = File::files($globalFunctionsPath);

        foreach ($files as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if ($className === 'BaseGlobalFunction') {
                continue;
            }

            $fullClassName = "App\\Http\\Sandbox\\GlobalFunctions\\{$className}";

            if (class_exists($fullClassName)) {
                try {
                    $instance = new $fullClassName();
                    $this->globalFunctions[$className] = [
                        'name' => $instance->getName(),
                        'description' => $instance->getDescription(),
                        'parameters' => $instance->getParameters(),
                        'class' => $fullClassName,
                        'type' => 'global'
                    ];
                } catch (\Exception $e) {
                    // 클래스 로드 실패 시 무시
                }
            }
        }
    }

    /**
     * Storage Functions 로드
     */
    private function loadStorageFunctions()
    {
        $currentStorage = Session::get('current_sandbox', 'storage-sandbox-template');
        $storagePath = storage_path("sandbox/{$currentStorage}/functions");

        if (!File::exists($storagePath)) {
            return;
        }

        $functionDirs = File::directories($storagePath);

        foreach ($functionDirs as $functionDir) {
            $functionName = basename($functionDir);
            $releasePath = $functionDir . '/release/Function.php';

            if (File::exists($releasePath)) {
                try {
                    // 함수 파일 내용 분석
                    $content = File::get($releasePath);

                    // 클래스명 추출
                    preg_match('/class\s+(\w+)/', $content, $matches);
                    $className = $matches[1] ?? $functionName;

                    // 네임스페이스 추출
                    preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
                    $namespace = $namespaceMatches[1] ?? "App\\Functions\\{$functionName}";

                    $this->storageFunctions[$functionName] = [
                        'name' => $functionName,
                        'description' => $this->extractFunctionDescription($content),
                        'path' => $releasePath,
                        'className' => $className,
                        'namespace' => $namespace,
                        'type' => 'storage'
                    ];
                } catch (\Exception $e) {
                    // 파일 읽기 실패 시 무시
                }
            }
        }
    }

    /**
     * 함수 파일에서 설명 추출 (주석 기반)
     */
    private function extractFunctionDescription($content)
    {
        // 간단한 설명 추출 로직
        if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
            return trim(str_replace(['*', '/'], '', $matches[1]));
        }

        return '설명 없음';
    }

    /**
     * 함수 호출 (기존 함수 시스템과 연동)
     */
    protected function callFunction($functionName, $params = [])
    {
        $this->logStep("Calling function: {$functionName}", $params);

        try {
            // Global Function인지 확인
            if (isset($this->globalFunctions[$functionName])) {
                return $this->executeGlobalFunction($functionName, $params);
            }

            // Storage Function인지 확인
            if (isset($this->storageFunctions[$functionName])) {
                return $this->executeStorageFunction($functionName, $params);
            }

            throw new \Exception("Function '{$functionName}' not found");

        } catch (\Exception $e) {
            $error = [
                'success' => false,
                'function' => $functionName,
                'message' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ];

            $this->logStep("Error in function: {$functionName}", $error);
            return $error;
        }
    }

    /**
     * Global Function 실행
     */
    private function executeGlobalFunction($functionName, $params)
    {
        $functionInfo = $this->globalFunctions[$functionName];
        $className = $functionInfo['class'];

        $instance = new $className();
        $result = $instance->execute($params);

        $this->logStep("Global function result: {$functionName}", $result);
        return $result;
    }

    /**
     * Storage Function 실행
     */
    private function executeStorageFunction($functionName, $params)
    {
        $functionInfo = $this->storageFunctions[$functionName];
        $functionPath = $functionInfo['path'];

        // 함수 파일 포함
        require_once $functionPath;

        // 추가 의존성 파일들 확인 및 포함
        $functionDir = dirname($functionPath);
        $additionalFiles = File::files($functionDir);

        foreach ($additionalFiles as $file) {
            if ($file->getFilename() !== 'Function.php' && $file->getExtension() === 'php') {
                require_once $file->getPathname();
            }
        }

        // 클래스 인스턴스 생성 및 실행
        $fullClassName = $functionInfo['namespace'] . '\\' . $functionInfo['className'];

        if (!class_exists($fullClassName)) {
            throw new \Exception("Class '{$fullClassName}' not found");
        }

        $instance = new $fullClassName();
        $result = $instance($params); // __invoke 호출

        $this->logStep("Storage function result: {$functionName}", $result);
        return $result;
    }

    /**
     * 워크플로우 스텝 체이닝
     */
    protected function step($functionName, $params = null)
    {
        // 이전 결과를 다음 단계 입력으로 사용
        if ($params === null && $this->currentData !== null) {
            $params = $this->currentData;
        }

        $result = $this->callFunction($functionName, $params);

        // 성공한 경우 결과를 현재 데이터로 설정
        if (isset($result['success']) && $result['success']) {
            $this->currentData = $result['data'] ?? $result;
        }

        return $this;
    }

    /**
     * 조건부 실행
     */
    protected function condition($conditionFunction, $params = null)
    {
        $params = $params ?? $this->currentData ?? [];
        $result = $this->callFunction($conditionFunction, $params);

        // 조건이 참이 아니면 체이닝 중단
        if (!isset($result['success']) || !$result['success']) {
            $this->currentData = null;
        }

        return $this;
    }

    /**
     * 반복 실행
     */
    protected function loop($iteratorName, callable $callback)
    {
        if ($this->currentData === null) {
            return $this;
        }

        $items = is_array($this->currentData) ? $this->currentData : [$this->currentData];
        $results = [];

        foreach ($items as $item) {
            $this->currentData = $item;
            $callback($this);
            $results[] = $this->currentData;
        }

        $this->currentData = $results;
        return $this;
    }

    /**
     * 최종 결과 반환
     */
    protected function getResult()
    {
        return $this->currentData;
    }

    /**
     * 실행 로그 기록
     */
     protected function logStep($message, $data = null)
    {
        $this->executionLog[] = [
            'timestamp' => now()->format('H:i:s.u'),
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * 실행 로그 반환
     */
    public function getExecutionLog()
    {
        return $this->executionLog;
    }

    /**
     * 사용 가능한 함수 목록 반환
     */
    public function getAvailableFunctions()
    {
        return [
            'global' => array_values($this->globalFunctions),
            'storage' => array_values($this->storageFunctions),
            'all' => array_values($this->availableFunctions)
        ];
    }

    /**
     * 워크플로우 실행 로그 초기화
     */
    protected function resetExecutionState()
    {
        $this->currentData = null;
        $this->executionLog = [];
        $this->steps = [];
    }
}
