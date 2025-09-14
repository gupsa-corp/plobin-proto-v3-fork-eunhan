<?php

namespace App\Services\RoleHierarchy\HasCircularDependency;

class Service
{
    public function __invoke(string $role, array $inheritedRoles): bool
    {
        return in_array($role, $inheritedRoles);
    }
}