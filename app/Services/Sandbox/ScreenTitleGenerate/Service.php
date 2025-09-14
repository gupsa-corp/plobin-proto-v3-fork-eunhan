<?php

namespace App\Services\Sandbox\ScreenTitleGenerate;

class Service
{
    public function __invoke(string $screenName): string
    {
        // 103-screen-table-view -> Table View
        if (preg_match('/^\d+-screen-(.+)$/', $screenName, $matches)) {
            return ucwords(str_replace('-', ' ', $matches[1]));
        }

        // 일반적인 경우
        return ucwords(str_replace('-', ' ', $screenName));
    }
}