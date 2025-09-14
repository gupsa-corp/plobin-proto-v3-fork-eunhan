<?php

namespace App\Services\SandboxTemplate\ValidateSandboxExists;

use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(string $sandboxName): bool
    {
        if (empty($sandboxName)) {
            return false;
        }

        $getTemplatePathService = app(\App\Services\SandboxTemplate\GetTemplatePath\Service::class);
        $templatePath = $getTemplatePathService($sandboxName);

        return File::exists($templatePath);
    }
}