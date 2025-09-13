<?php

namespace App\Http\Controllers\Organization\DeleteOrganization;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;
use App\Exceptions\ApiException;

class Controller extends ApiController
{
    public function __invoke($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            throw ApiException::notFound('조직을 찾을 수 없습니다.');
        }

        // 조직의 소유자만 삭제 가능
        if ($organization->user_id !== auth()->id()) {
            throw ApiException::forbidden('조직을 삭제할 권한이 없습니다.');
        }

        $organization->delete();

        return $this->deleted('조직이 삭제되었습니다.');
    }
}
