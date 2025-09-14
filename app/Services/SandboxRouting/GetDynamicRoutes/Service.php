<?php

namespace App\Services\SandboxRouting\GetDynamicRoutes;

class Service
{
    public function __invoke(string $sandboxName = null): array
    {
        if (!$sandboxName) {
            $sandboxContextService = app(\App\Services\SandboxContextService::class);
            $sandboxName = $sandboxContextService->getCurrentSandbox();
        }

        $routes = [];

        $domainListService = app(\App\Services\SandboxRouting\GetDomainList\Service::class);
        $screenListService = app(\App\Services\SandboxRouting\GetScreenList\Service::class);
        $generateRouteService = app(\App\Services\SandboxRouting\GenerateRoute\Service::class);
        $generateViewPathService = app(\App\Services\SandboxRouting\GenerateViewPath\Service::class);
        $generateRouteNameService = app(\App\Services\SandboxRouting\GenerateRouteName\Service::class);

        $domains = $domainListService($sandboxName);

        foreach ($domains as $domainInfo) {
            $domainName = $domainInfo['name'];
            $screens = $screenListService($domainName, $sandboxName);

            foreach ($screens as $screenInfo) {
                $screenName = $screenInfo['name'];
                $route = $generateRouteService($domainName, $screenName, $sandboxName);
                $viewPath = $generateViewPathService($domainName, $screenName, $sandboxName);
                $routeName = $generateRouteNameService($domainName, $screenName, $sandboxName);

                $routes[] = [
                    'path' => $route,
                    'view' => $viewPath,
                    'name' => $routeName,
                    'sandbox' => $sandboxName,
                    'domain' => $domainName,
                    'screen' => $screenName,
                    'metadata' => [
                        'domain_path' => $domainInfo['path'],
                        'screen_path' => $screenInfo['path'],
                        'has_blade_file' => $screenInfo['has_blade_file'] ?? false,
                        'created_at' => $screenInfo['created_at'] ?? null,
                        'modified_at' => $screenInfo['modified_at'] ?? null,
                    ]
                ];
            }
        }

        return $routes;
    }
}