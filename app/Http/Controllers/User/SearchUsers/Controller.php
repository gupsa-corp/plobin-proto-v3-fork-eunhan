<?php

namespace App\Http\Controllers\User\SearchUsers;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return $this->success([
                'users' => []
            ], '검색어를 2글자 이상 입력해주세요.');
        }

        $users = User::where('email', 'like', '%' . $query . '%')
            ->orWhere('nickname', 'like', '%' . $query . '%')
            ->orWhere('first_name', 'like', '%' . $query . '%')
            ->orWhere('last_name', 'like', '%' . $query . '%')
            ->select(['id', 'email', 'nickname', 'first_name', 'last_name', 'name'])
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'nickname' => $user->nickname,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                ];
            });

        return $this->success([
            'users' => $users
        ], '사용자 검색 결과입니다.');
    }
}
