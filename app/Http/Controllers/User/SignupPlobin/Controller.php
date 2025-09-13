<?php

namespace App\Http\Controllers\User\SignupPlobin;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        try {
            DB::beginTransaction();

            // SignupRequest에서 이미 모든 검증이 완료됨
            $validatedData = $request->validated();

            $user = User::create([
                'email' => strtolower(trim($validatedData['email'])),
                'password' => Hash::make($validatedData['password']),
                'country_code' => $validatedData['country_code'] ?? null,
                'phone_number' => $validatedData['phone_number'] ?? null,
                'nickname' => $validatedData['nickname'] ?? null,
                'first_name' => $validatedData['first_name'] ?? null,
                'last_name' => $validatedData['last_name'] ?? null,
            ]);

            // 회원가입과 동시에 로그인 토큰 발급
            $token = $user->createToken('auth-token')->plainTextToken;

            DB::commit();

            return $this->created([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'country_code' => $user->country_code,
                    'phone_number' => $user->phone_number,
                    'formatted_phone' => $user->formatted_phone,
                    'e164_phone' => $user->e164_phone,
                    'international_phone' => $user->international_phone,
                    'phone_type' => $user->phone_type,
                    'is_valid_phone' => $user->isValidPhone(),
                    'nickname' => $user->nickname,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'redirect_url' => '/dashboard'
            ], '회원가입이 완료되었습니다.');

        } catch (Exception $e) {
            DB::rollBack();

            return $this->internalServerError([], '회원가입 중 오류가 발생했습니다. 다시 시도해주세요.');
        }
    }
}
