<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectPage;

class SandboxService
{
    /**
     * 프로젝트 페이지의 샌드박스 정보를 가져옵니다.
     */
    public function getPageSandboxInfo(ProjectPage $page): array
    {
        return app(\App\Services\Sandbox\PageInfo\Service::class)($page);
    }

    /**
     * 사용 가능한 커스텀 스크린 목록을 가져옵니다.
     */
    public function getAvailableCustomScreens(?string $sandboxName = null): array
    {
        return app(\App\Services\Sandbox\CustomScreenList\Service::class)($sandboxName);
    }

    /**
     * 커스텀 스크린 콘텐츠를 렌더링합니다.
     */
    public function renderCustomScreen(ProjectPage $page): ?array
    {
        return app(\App\Services\Sandbox\CustomScreenRender\Service::class)($page);
    }

    /**
     * 프로젝트에 샌드박스가 설정되어 있는지 확인합니다.
     */
    public function hasProjectSandbox(Project $project): bool
    {
        return app(\App\Services\Sandbox\ProjectSandboxCheck\Service::class)($project);
    }

    /**
     * 페이지의 커스텀 스크린을 설정합니다.
     */
    public function setCustomScreen(ProjectPage $page, ?string $customScreenFolder): bool
    {
        return app(\App\Services\Sandbox\CustomScreenSet\Service::class)($page, $customScreenFolder);
    }

    /**
     * 사용 가능한 도메인 목록을 가져옵니다.
     */
    public function getAvailableDomains(?string $sandboxName = null): array
    {
        return app(\App\Services\Sandbox\DomainList\Service::class)($sandboxName);
    }

    /**
     * 특정 도메인의 사용 가능한 화면 목록을 반환합니다.
     */
    public function getScreensByDomain(?string $sandboxName = null, string $domainName = null): array
    {
        return app(\App\Services\Sandbox\ScreensByDomain\Service::class)($sandboxName, $domainName);
    }

    /**
     * 특정 화면의 콘텐츠를 로드합니다.
     */
    public function loadScreenContent(?string $sandboxName, string $domainName, string $screenName): array
    {
        return app(\App\Services\Sandbox\ScreenContentLoad\Service::class)($sandboxName, $domainName, $screenName);
    }

    /**
     * 화면 제목 생성
     */
    private function generateScreenTitle(string $screenName): string
    {
        return app(\App\Services\Sandbox\ScreenTitleGenerate\Service::class)($screenName);
    }

    /**
     * 커스텀 화면 폴더명에서 도메인 정보를 추출합니다.
     */
    private function extractDomainFromScreenFolder(?string $customScreenFolder): ?string
    {
        return app(\App\Services\Sandbox\DomainExtract\Service::class)($customScreenFolder);
    }
}