<?php

namespace App\Http\Controllers\Organization\GetOrganization;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;
use App\Exceptions\ApiException;

class Controller extends ApiController
{
    public function __invoke($id)
    {
        $organization = Organization::with('user:id,name,email')->find($id);

        if (!$organization) {
            throw ApiException::notFound('조직을 찾을 수 없습니다.');
        }

        return $this->success([
            'id' => $organization->id,
            'name' => $organization->name,
            'url' => $organization->url,
            'created_at' => $organization->created_at,
            'creator' => [
                'id' => $organization->user->id,
                'nickname' => $organization->user->nickname,
                'email' => $organization->user->email
            ],
            'is_owner' => $organization->user_id === auth()->id()
        ], '조직 정보를 조회했습니다.');
    }
}
