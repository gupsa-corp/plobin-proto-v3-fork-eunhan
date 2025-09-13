<?php

namespace App\Http\Controllers\ProjectPage\DeletePage;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Page;
use App\Models\Project;

class Controller extends ApiController
{
    public function __invoke(Request $request, Project $project, Page $page)
    {
        if ($page->project_id !== $project->id) {
            abort(404, '페이지를 찾을 수 없습니다.');
        }

        $page->delete();

        return $this->deleted('페이지가 삭제되었습니다.');
    }
}
