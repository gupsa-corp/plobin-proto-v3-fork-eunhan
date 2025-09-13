<?php

namespace App\Http\Controllers\User\CheckEmail;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        // 이메일 정규화
        $email = strtolower(trim($request->email));
        $cacheKey = 'email_check:' . $email;

        $exists = Cache::remember($cacheKey, 300, function () use ($email) {
            return User::whereRaw('LOWER(email) = ?', [$email])->exists();
        });

        return $this->success([
            'available' => !$exists,
            'message' => $exists ? '이미 사용중인 이메일입니다.' : '사용 가능한 이메일입니다.'
        ]);
    }
}
