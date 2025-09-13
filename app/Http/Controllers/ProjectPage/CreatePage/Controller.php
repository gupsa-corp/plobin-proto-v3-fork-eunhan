<?php

namespace App\Http\Controllers\ProjectPage\CreatePage;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Controller extends ApiController
{
    public function __invoke(Request $request, Project $project)
    {
        $validated = $request->validated();

        // 슬러그 자동 생성
        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;

        // 같은 프로젝트 내에서 슬러그 중복 방지
        while ($project->pages()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // 부모 페이지가 있는 경우 같은 프로젝트에 속하는지 확인
        if (!empty($validated['parent_id'])) {
            $parent = Page::find($validated['parent_id']);
            if (!$parent || $parent->project_id !== $project->id) {
                throw ValidationException::withMessages([
                    'parent_id' => '유효하지 않은 부모 페이지입니다.'
                ]);
            }
        }

        $page = $project->pages()->create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'] ?? '',
            'status' => $validated['status'] ?? 'draft',
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => Auth::id(),
            'sort_order' => $this->getNextSortOrder($project, $validated['parent_id'] ?? null)
        ]);

        return $this->created($page->load(['parent', 'user']), '페이지가 생성되었습니다.');
    }

    private function getNextSortOrder(Project $project, $parentId = null): int
    {
        $maxSort = $project->pages()
            ->where('parent_id', $parentId)
            ->max('sort_order');

        return $maxSort ? $maxSort + 1 : 1;
    }
}
