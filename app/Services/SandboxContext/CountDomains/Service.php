<?php

namespace App\Services\SandboxContext\CountDomains;

use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(string $sandboxPath): int
    {
        try {
            $directories = File::directories($sandboxPath);
            $domainCount = 0;

            foreach ($directories as $directory) {
                $dirName = basename($directory);
                // xxx-domain-xxx 패턴의 디렉터리만 카운트
                if (preg_match('/^\d+-domain-/', $dirName)) {
                    $domainCount++;
                }
            }

            return $domainCount;
        } catch (\Exception $e) {
            return 0;
        }
    }
}