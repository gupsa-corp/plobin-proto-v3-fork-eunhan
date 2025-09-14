<?php

namespace App\Services\FunctionMetadata\AddVersion;

use App\Services\FunctionMetadata\GetFunction\Service as GetFunctionService;
use App\Services\FunctionMetadata\UpdateFunction\Service as UpdateFunctionService;

class Service
{
    protected $getFunctionService;
    protected $updateFunctionService;

    public function __construct(
        GetFunctionService $getFunctionService,
        UpdateFunctionService $updateFunctionService
    ) {
        $this->getFunctionService = $getFunctionService;
        $this->updateFunctionService = $updateFunctionService;
    }

    /**
     * Add new version to function
     */
    public function __invoke(string $functionName, string $version): bool
    {
        try {
            $function = $this->getFunctionService->__invoke($functionName);

            if (!$function) {
                return false;
            }

            $versions = $function['versions'] ?? [];

            if (!in_array($version, $versions)) {
                $versions[] = $version;

                // Sort versions (release first, then by name)
                usort($versions, function($a, $b) {
                    if ($a === 'release') return -1;
                    if ($b === 'release') return 1;
                    return strcmp($b, $a);
                });

                return $this->updateFunctionService->__invoke($functionName, ['versions' => $versions]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}