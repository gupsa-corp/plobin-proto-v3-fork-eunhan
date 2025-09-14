<?php

namespace App\Http\Controllers\Api\Sandbox\GetScreensByDomain;

use App\Services\SandboxService;
use App\Services\SandboxContextService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Controller extends \App\Http\Controllers\Controller
{
    public function __construct(
        private readonly SandboxService $sandboxService,
        private readonly SandboxContextService $sandboxContextService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $domainName = $request->get('domain');

            if (!$domainName) {
                return response()->json([
                    'success' => false,
                    'message' => '도메인명이 필요합니다.'
                ], 400);
            }

            // 현재 샌드박스 정보 가져오기 - 기본값으로 storage-sandbox-template 사용
            try {
                $sandboxName = $this->sandboxContextService->getCurrentSandbox();
            } catch (\Exception $e) {
                // 기본 샌드박스 템플릿 사용
                $sandboxName = 'storage-sandbox-template';
                try {
                    $this->sandboxContextService->setCurrentSandbox($sandboxName);
                } catch (\Exception $e) {
                    // setCurrentSandbox 실패 시 직접 처리
                    $sandboxName = 'storage-sandbox-template';
                }
            }

            // 도메인별 화면 목록 조회
            $screens = $this->sandboxService->getScreensByDomain($sandboxName, $domainName);

            return response()->json([
                'success' => true,
                'screens' => $screens
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '화면 목록을 불러오는데 실패했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}