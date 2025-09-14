<?php

namespace App\Http\Controllers\Sandbox\FileList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class Controller extends \App\Http\Controllers\Controller
{
    /**
     * 현재 선택된 샌드박스의 파일 목록을 반환
     */
    public function getFileList(Request $request)
    {
        $currentStorage = Session::get('sandbox_storage', '1');
        $storagePath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-' . $currentStorage);

        $path = $request->get('path', '');
        $targetPath = $storagePath . '/' . ltrim($path, '/');

        // 보안 체크: 샌드박스 디렉토리를 벗어나는 경로 차단
        $realTargetPath = realpath($targetPath);
        $realStoragePath = realpath($storagePath);

        if (!$realTargetPath || !str_starts_with($realTargetPath, $realStoragePath)) {
            return response()->json(['error' => '접근할 수 없는 경로입니다.'], 403);
        }

        if (!File::exists($targetPath) || !File::isDirectory($targetPath)) {
            return response()->json(['error' => '디렉토리가 존재하지 않습니다.'], 404);
        }

        $files = [];
        $directories = [];

        try {
            $items = File::glob($targetPath . '/*');

            foreach ($items as $item) {
                $relativePath = str_replace($storagePath . '/', '', $item);
                $basename = basename($item);
                $isDirectory = File::isDirectory($item);

                $itemData = [
                    'name' => $basename,
                    'path' => $relativePath,
                    'is_directory' => $isDirectory,
                    'size' => $isDirectory ? null : $this->formatBytes(File::size($item)),
                    'modified_at' => date('Y-m-d H:i:s', File::lastModified($item)),
                    'extension' => $isDirectory ? null : File::extension($item),
                ];

                if ($isDirectory) {
                    $directories[] = $itemData;
                } else {
                    $files[] = $itemData;
                }
            }

            // 디렉토리를 먼저, 파일을 나중에 정렬
            usort($directories, fn($a, $b) => strcasecmp($a['name'], $b['name']));
            usort($files, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            return response()->json([
                'current_path' => $path,
                'current_storage' => $currentStorage,
                'items' => array_merge($directories, $files),
                'parent_path' => dirname($path) === '.' ? '' : dirname($path),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '파일 목록을 읽는 중 오류가 발생했습니다.'], 500);
        }
    }

    private function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 1) . ' ' . $units[$unitIndex];
    }
}
