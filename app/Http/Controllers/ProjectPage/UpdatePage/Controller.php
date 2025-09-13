<?php

namespace App\Http\Controllers\ProjectPage\UpdatePage;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Controller extends ApiController
{
    public function __invoke(Request $request, Project $project, Page $page)
    {
        if ($page->project_id !== $project->id) {
            abort(404, '페이지를 찾을 수 없습니다.');
        }

        $validated = $request->validated();

        // 제목이 변경된 경우 슬러그 업데이트
        if (isset($validated['title']) && $validated['title'] !== $page->title) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;

            while ($project->pages()->where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $validated['slug'] = $slug;
        }

        // 부모 페이지 검증
        if (isset($validated['parent_id'])) {
            if ($validated['parent_id']) {
                $parent = Page::find($validated['parent_id']);
                if (!$parent || $parent->project_id !== $project->id) {
                    throw ValidationException::withMessages([
                        'parent_id' => '유효하지 않은 부모 페이지입니다.'
                    ]);
                }

                // 자기 자신을 부모로 설정하는 것 방지
                if ($validated['parent_id'] == $page->id) {
                    throw ValidationException::withMessages([
                        'parent_id' => '자기 자신을 부모 페이지로 설정할 수 없습니다.'
                    ]);
                }
            }
        }

        $page->update($validated);

        return $this->updated($page->load(['parent', 'user']), '페이지가 수정되었습니다.');
    }
}
