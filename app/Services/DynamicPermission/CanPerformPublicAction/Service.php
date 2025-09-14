<?php

namespace App\Services\DynamicPermission\CanPerformPublicAction;

use App\Services\DynamicPermission\CanPerformAction\Service as CanPerformActionService;

class Service
{
    protected $canPerformActionService;

    public function __construct(CanPerformActionService $canPerformActionService)
    {
        $this->canPerformActionService = $canPerformActionService;
    }

    /**
     * 게스트(비로그인) 사용자가 공개 액션을 수행할 수 있는지 확인
     */
    public function __invoke(string $action, array $context = []): bool
    {
        return $this->canPerformActionService->__invoke(null, 'public_access', $action, $context);
    }
}