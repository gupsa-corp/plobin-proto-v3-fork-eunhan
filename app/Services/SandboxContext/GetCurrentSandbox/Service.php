<?php

namespace App\Services\SandboxContext\GetCurrentSandbox;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    const SESSION_KEY = 'current_sandbox';

    public function __invoke(): string
    {
        $sandbox = Session::get(self::SESSION_KEY);

        // 새로운 세션 키에 값이 없으면 레거시 키도 확인
        if (!$sandbox) {
            $sandbox = Session::get('sandbox_storage');
        }

        // 여전히 없으면 사용 가능한 첫 번째 샌드박스를 자동으로 설정
        if (!$sandbox) {
            $firstSandbox = $this->findFirstAvailableSandbox();
            if ($firstSandbox) {
                $this->setCurrentSandbox($firstSandbox);
                return $firstSandbox;
            }
            throw new \Exception('샌드박스가 선택되지 않았습니다. setCurrentSandbox()로 샌드박스를 선택해주세요.');
        }

        return $sandbox;
    }

    private function findFirstAvailableSandbox(): ?string
    {
        $containerPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));

        if (!File::exists($containerPath)) {
            return null;
        }

        try {
            $directories = File::directories($containerPath);

            foreach ($directories as $directory) {
                $sandboxName = basename($directory);

                // 기본 구조 검증 (000-common 폴더 존재 여부)
                $commonPath = $directory . '/000-common';
                if (File::exists($commonPath)) {
                    return $sandboxName;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error finding first available sandbox', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function setCurrentSandbox(string $sandbox): void
    {
        $sandboxPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandbox}");
        if (File::exists($sandboxPath) && File::isDirectory($sandboxPath)) {
            // 새 세션 키와 레거시 키 모두 설정
            Session::put(self::SESSION_KEY, $sandbox);
            Session::put('sandbox_storage', $sandbox);
            Log::info('Sandbox context changed', ['from' => Session::get(self::SESSION_KEY), 'to' => $sandbox]);
        } else {
            throw new \InvalidArgumentException("Sandbox '{$sandbox}' does not exist");
        }
    }
}