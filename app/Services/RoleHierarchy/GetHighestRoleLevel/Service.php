<?php

namespace App\Services\RoleHierarchy\GetHighestRoleLevel;

use App\Services\RoleHierarchy\GetRoleLevel\Service as GetRoleLevelService;

class Service
{
    public function __construct(
        private GetRoleLevelService $getRoleLevelService
    ) {}

    public function __invoke(array $roleNames): int
    {
        $maxLevel = 0;
        
        foreach ($roleNames as $roleName) {
            $level = ($this->getRoleLevelService)($roleName);
            if ($level > $maxLevel) {
                $maxLevel = $level;
            }
        }
        
        return $maxLevel;
    }
}