<?php

namespace App\Services\RoleHierarchy\HasHigherAuthority;

use App\Services\RoleHierarchy\GetRoleLevel\Service as GetRoleLevelService;

class Service
{
    public function __construct(
        private GetRoleLevelService $getRoleLevelService
    ) {}

    public function __invoke(string $roleA, string $roleB): bool
    {
        return ($this->getRoleLevelService)($roleA) > ($this->getRoleLevelService)($roleB);
    }
}