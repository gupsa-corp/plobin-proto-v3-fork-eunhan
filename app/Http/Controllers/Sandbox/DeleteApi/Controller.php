<?php

namespace App\Http\Controllers\Sandbox\DeleteApi;

use App\Http\Controllers\Api\Core\Controller as ApiController;

class Controller extends ApiController
{
    public function destroy($id)
    {
        // 구현필요
        return response()->json(['message' => '구현필요']);
    }
}
