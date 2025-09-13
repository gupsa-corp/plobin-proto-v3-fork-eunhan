<?php

namespace App\Http\Controllers\Sandbox\UsingProjects;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class Controller
{
    public function index()
    {
        $currentStorage = Session::get('sandbox_storage', 'template');
        $projects = $this->getUsingProjects($currentStorage);

        return view('700-page-sandbox.716-page-using-projects.000-index', compact('projects', 'currentStorage'));
    }

    private function getUsingProjects($storage)
    {
        try {
            // 메인 데이터베이스에서 해당 샌드박스를 사용하는 프로젝트들을 조회
            $projects = DB::select("
                SELECT
                    p.id,
                    p.name,
                    p.description,
                    o.name as organization_name,
                    o.id as organization_id,
                    ps.sandbox_folder,
                    ps.created_at as sandbox_assigned_at,
                    ps.updated_at as sandbox_updated_at
                FROM projects p
                INNER JOIN organizations o ON p.organization_id = o.id
                INNER JOIN project_sandboxes ps ON p.id = ps.project_id
                WHERE ps.sandbox_folder = ?
                ORDER BY ps.updated_at DESC, p.name ASC
            ", [$storage]);

            return $projects;
        } catch (\Exception $e) {
            // 에러가 발생하면 빈 배열 반환
            return [];
        }
    }
}
