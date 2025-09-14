<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SandboxContextService
{
    const SESSION_KEY = 'current_sandbox';

    public function __construct(
        private \App\Services\SandboxContext\GetCurrentSandbox\Service $getCurrentSandboxService,
        private \App\Services\SandboxContext\SetCurrentSandbox\Service $setCurrentSandboxService,
        private \App\Services\SandboxContext\GetSandboxPath\Service $getSandboxPathService,
        private \App\Services\SandboxContext\GetSandboxUrl\Service $getSandboxUrlService,
        private \App\Services\SandboxContext\GetSandboxStoragePath\Service $getSandboxStoragePathService,
        private \App\Services\SandboxContext\GetSandboxApiUrl\Service $getSandboxApiUrlService,
        private \App\Services\SandboxContext\GetScreenUrl\Service $getScreenUrlService,
        private \App\Services\SandboxContext\ValidateSandboxExists\Service $validateSandboxExistsService,
        private \App\Services\SandboxContext\GetAvailableSandboxes\Service $getAvailableSandboxesService,
        private \App\Services\SandboxContext\GenerateDisplayName\Service $generateDisplayNameService,
        private \App\Services\SandboxContext\CountDomains\Service $countDomainsService,
        private \App\Services\SandboxContext\CountScreens\Service $countScreensService,
        private \App\Services\SandboxContext\GetCurrentContext\Service $getCurrentContextService,
        private \App\Services\SandboxContext\DetectAndSetFromUrl\Service $detectAndSetFromUrlService,
        private \App\Services\SandboxContext\Reset\Service $resetService,
        private \App\Services\SandboxContext\FindFirstAvailableSandbox\Service $findFirstAvailableSandboxService,
        private \App\Services\SandboxContext\GetDebugInfo\Service $getDebugInfoService
    ) {}

    /**
     * 현재 선택된 샌드박스 이름을 가져옵니다.
     */
    public function getCurrentSandbox(): string
    {
        return ($this->getCurrentSandboxService)();
    }

    /**
     * 현재 샌드박스를 설정합니다.
     */
    public function setCurrentSandbox(string $sandbox): void
    {
        ($this->setCurrentSandboxService)($sandbox);
    }

    /**
     * 현재 샌드박스의 컨테이너 경로를 가져옵니다.
     */
    public function getSandboxPath(): string
    {
        return ($this->getSandboxPathService)();
    }

    /**
     * 현재 샌드박스의 기본 URL 경로를 가져옵니다.
     */
    public function getSandboxUrl(): string
    {
        return ($this->getSandboxUrlService)();
    }

    /**
     * 현재 샌드박스의 스토리지 경로를 가져옵니다.
     */
    public function getSandboxStoragePath(): string
    {
        return ($this->getSandboxStoragePathService)();
    }

    /**
     * 샌드박스 API URL을 생성합니다.
     */
    public function getSandboxApiUrl(string $endpoint = ''): string
    {
        return ($this->getSandboxApiUrlService)($endpoint);
    }

    /**
     * 특정 도메인/스크린으로의 URL을 생성합니다.
     */
    public function getScreenUrl(string $domain, string $screen): string
    {
        return ($this->getScreenUrlService)($domain, $screen);
    }

    /**
     * 샌드박스가 존재하는지 확인합니다.
     */
    public function validateSandboxExists(string $sandbox): bool
    {
        return ($this->validateSandboxExistsService)($sandbox);
    }

    /**
     * 사용 가능한 모든 샌드박스 목록을 가져옵니다.
     */
    public function getAvailableSandboxes(): array
    {
        return ($this->getAvailableSandboxesService)();
    }

    /**
     * 샌드박스 표시 이름을 생성합니다.
     */
    private function generateDisplayName(string $sandboxName): string
    {
        return ($this->generateDisplayNameService)($sandboxName);
    }

    /**
     * 샌드박스의 도메인 개수를 셉니다.
     */
    private function countDomains(string $sandboxPath): int
    {
        return ($this->countDomainsService)($sandboxPath);
    }

    /**
     * 샌드박스의 스크린 개수를 셉니다.
     */
    private function countScreens(string $sandboxPath): int
    {
        return ($this->countScreensService)($sandboxPath);
    }

    /**
     * 현재 샌드박스 컨텍스트 정보를 반환합니다.
     */
    public function getCurrentContext(): array
    {
        return ($this->getCurrentContextService)();
    }

    /**
     * URL에서 샌드박스를 자동 감지하고 세션에 설정합니다.
     */
    public function detectAndSetFromUrl(?string $url = null): bool
    {
        return ($this->detectAndSetFromUrlService)($url);
    }

    /**
     * 샌드박스 컨텍스트를 초기화합니다.
     */
    public function reset(): void
    {
        ($this->resetService)();
    }

    /**
     * 사용 가능한 첫 번째 샌드박스를 찾습니다 (순환 참조 방지용)
     */
    private function findFirstAvailableSandbox(): ?string
    {
        return ($this->findFirstAvailableSandboxService)();
    }

    /**
     * 디버그 정보를 반환합니다.
     */
    public function getDebugInfo(): array
    {
        return ($this->getDebugInfoService)();
    }
}