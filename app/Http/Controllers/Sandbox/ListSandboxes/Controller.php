<?php

namespace App\Http\Controllers\Sandbox\ListSandboxes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class Controller extends \App\Http\Controllers\Controller
{
    /**
     * 샌드박스 디렉토리 목록을 반환
     */
    public function listSandboxes(Request $request)
    {
        try {
            $sandboxPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox'));

            // 샌드박스 디렉토리가 존재하지 않는 경우
            if (!File::exists($sandboxPath) || !File::isDirectory($sandboxPath)) {
                return response()->json([
                    'sandboxes' => [],
                    'message' => '샌드박스 디렉토리가 존재하지 않습니다.'
                ], 200);
            }

            $sandboxes = [];
            $directories = File::directories($sandboxPath);

            foreach ($directories as $directory) {
                $basename = basename($directory);
                $sandboxes[] = [
                    'name' => $basename
                ];
            }

            // 이름순으로 정렬
            usort($sandboxes, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            return response()->json([
                'sandboxes' => $sandboxes,
                'total_count' => count($sandboxes)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => '샌드박스 목록을 읽는 중 오류가 발생했습니다.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 디렉토리 내 파일 개수 계산
     */
    private function countFiles($directory)
    {
        try {
            $files = File::allFiles($directory);
            return count($files);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 디렉토리 내 하위 디렉토리 개수 계산
     */
    private function countDirectories($directory)
    {
        try {
            $directories = File::directories($directory);
            return count($directories);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
