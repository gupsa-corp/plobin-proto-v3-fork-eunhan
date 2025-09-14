<?php

namespace App\Http\Controllers\Api\Sandbox\LoadScreenContent;

use App\Services\SandboxService;
use Illuminate\Http\JsonResponse;

class Controller extends \App\Http\Controllers\Controller
{
    public function __construct(
        private SandboxService $sandboxService
    ) {}

    public function __invoke(\App\Http\Controllers\Api\Sandbox\LoadScreenContent\Request $request): JsonResponse
    {
        try {
            $domain = $request->get('domain');
            $screen = $request->get('screen');
            $sandboxName = $request->get('sandbox', 'storage-sandbox-template');

            if (empty($domain) || empty($screen)) {
                return response()->json([
                    'success' => false,
                    'message' => '도메인과 화면 정보가 필요합니다'
                ], 400);
            }

            // 화면 콘텐츠 로드
            $screenContent = $this->sandboxService->loadScreenContent($sandboxName, $domain, $screen);

            return response()->json([
                'success' => true,
                'content' => $screenContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '화면 콘텐츠 로드 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}