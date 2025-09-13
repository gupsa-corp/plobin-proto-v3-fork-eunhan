<?php

namespace App\Http\Controllers\Organization\UpdateOrganization;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;
use App\Exceptions\ApiException;

class Controller extends ApiController
{
    public function __invoke(Request $request, $id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            throw ApiException::notFound('조직을 찾을 수 없습니다.');
        }

        // 조직의 소유자만 수정 가능
        if ($organization->user_id !== auth()->id()) {
            throw ApiException::forbidden('조직을 수정할 권한이 없습니다.');
        }

        $organization->update([
            'name' => $request->name,
            'url' => strtolower($request->url)
        ]);

        return $this->updated([
            'id' => $organization->id,
            'name' => $organization->name,
            'url' => $organization->url
        ], '조직이 수정되었습니다.');
    }
}
