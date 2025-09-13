<?php

namespace App\Http\Controllers\ProjectPage\GetPages;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Project;
use Illuminate\Http\Request;

class Controller extends ApiController
{
    public function __invoke(Request $request, Project $project)
    {
        $parentId = $request->query('parent_id');

        // parent_id가 'null' 문자열이나 빈 문자열인 경우 null로 처리
        if ($parentId === 'null' || $parentId === '') {
            $parentId = null;
        } else if ($parentId !== null) {
            // parent_id가 있는 경우 정수로 변환
            $parentId = (int) $parentId;
        }

        if ($parentId === null) {
            // parent_id가 null인 경우: 최상위 페이지들
            $pages = $project->rootPages()->with(['children', 'user'])->get();
        } else {
            // parent_id가 특정 값인 경우: 해당 부모의 하위 페이지들
            $pages = $project->pages()
                ->where('parent_id', $parentId)
                ->with(['children', 'user'])
                ->orderBy('sort_order')
                ->get();
        }

        return $this->success([
            'data' => $pages,
            'debug' => [
                'parent_id_param' => $request->query('parent_id'),
                'processed_parent_id' => $parentId,
                'count' => $pages->count()
            ]
        ]);
    }
}
