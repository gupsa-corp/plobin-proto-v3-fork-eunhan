<?php

namespace App\Http\Controllers\Project\SetSandbox;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Controller extends \App\Http\Controllers\Controller
{
    public function __invoke($id, $projectId, Request $request)
    {
        try {
            // 프로젝트 존재 여부 확인
            $project = Project::where('id', $projectId)
                ->whereHas('organization', function($query) use ($id) {
                    $query->where('id', $id);
                })->first();

            if (!$project) {
                return redirect()->back()->with('error', '프로젝트를 찾을 수 없습니다.');
            }

            // 샌드박스 설정 저장
            $sandboxName = $request->input('sandbox', '');
            if (empty($sandboxName)) {
                $sandboxName = null; // 빈 값을 null로 변환
            }

            // 페이지별 개별 샌드박스 허용 여부
            $allowIndividualSandbox = $request->boolean('allow_individual_sandbox_per_page', false);

            $project->update([
                'sandbox_folder' => $sandboxName,
                'allow_individual_sandbox_per_page' => $allowIndividualSandbox
            ]);

            return redirect()->back()->with('success', '샌드박스 설정이 저장되었습니다.');
        } catch (\Exception $e) {
            Log::error('샌드박스 설정 저장 오류', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', '설정 저장 중 오류가 발생했습니다.');
        }
    }
}
