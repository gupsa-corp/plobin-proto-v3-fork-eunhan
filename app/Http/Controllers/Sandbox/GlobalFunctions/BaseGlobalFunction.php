<?php

namespace App\Http\Controllers\Sandbox\GlobalFunctions;

abstract class BaseGlobalFunction
{
    /**
     * 함수의 고유 이름을 반환합니다.
     */
    abstract public function getName(): string;

    /**
     * 함수에 대한 설명을 반환합니다.
     */
    abstract public function getDescription(): string;

    /**
     * 함수가 필요로 하는 파라미터들을 배열로 반환합니다.
     * 
     * @return array 예: [
     *     'param_name' => [
     *         'required' => true,
     *         'type' => 'string',
     *         'description' => '파라미터 설명',
     *         'example' => '예시값'
     *     ]
     * ]
     */
    abstract public function getParameters(): array;

    /**
     * 함수를 실행합니다.
     */
    abstract public function execute(array $params): array;

    /**
     * 필수 파라미터 검증을 수행합니다.
     */
    protected function validateParams(array $params, array $required): void
    {
        foreach ($required as $param) {
            if (!array_key_exists($param, $params) || empty($params[$param])) {
                throw new \InvalidArgumentException("Required parameter '{$param}' is missing or empty.");
            }
        }
    }

    /**
     * 일관된 응답 형식을 생성합니다.
     */
    protected function formatResponse($data = null, bool $success = true, string $message = '', array $extra = []): array
    {
        $response = [
            'success' => $success,
            'function' => $this->getName(),
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return array_merge($response, $extra);
    }

    /**
     * 에러 응답을 생성합니다.
     */
    protected function errorResponse(string $message, \Exception $exception = null): array
    {
        $response = $this->formatResponse(null, false, $message);
        
        if ($exception && config('app.debug')) {
            $response['error_details'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return $response;
    }

    /**
     * 성공 응답을 생성합니다.
     */
    protected function successResponse($data = null, string $message = 'Success', array $extra = []): array
    {
        return $this->formatResponse($data, true, $message, $extra);
    }
}