<?php

namespace App\Http\Controllers\Organization\GetOrganizations;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;

class Controller extends ApiController
{
    public function __invoke()
    {
        $organizations = Organization::orderBy('created_at', 'desc')
            ->get(['id', 'name', 'url']);

        return $this->success([
            'organizations' => $organizations
        ], '조직 목록을 조회했습니다.');
    }
}
