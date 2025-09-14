<?php

namespace App\Services\SandboxContext\CountScreens;

use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(string $sandboxPath): int
    {
        try {
            $screenCount = 0;
            $directories = File::directories($sandboxPath);

            foreach ($directories as $domainDirectory) {
                $dirName = basename($domainDirectory);
                // 도메인 디렉터리만 검사
                if (preg_match('/^\d+-domain-/', $dirName)) {
                    $screenDirectories = File::directories($domainDirectory);
                    foreach ($screenDirectories as $screenDirectory) {
                        $screenName = basename($screenDirectory);
                        // xxx-screen-xxx 패턴의 디렉터리만 카운트
                        if (preg_match('/^\d+-screen-/', $screenName)) {
                            $contentFile = $screenDirectory . '/000-content.blade.php';
                            if (File::exists($contentFile)) {
                                $screenCount++;
                            }
                        }
                    }
                }
            }

            return $screenCount;
        } catch (\Exception $e) {
            return 0;
        }
    }
}