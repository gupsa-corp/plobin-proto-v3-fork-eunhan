<?php

namespace App\Http\Controllers\Api\Page\CheckSubPages;

use App\Models\ProjectPage;
use Illuminate\Http\Request;

class Controller extends \App\Http\Controllers\Controller
{
    public function __invoke($pageId, Request $request)
    {
        $page = ProjectPage::find($pageId);

        if (!$page) {
            return response()->json(['hasAnalysisSubPages' => false]);
        }

        // 분석 관련 하위 페이지들이 있는지 확인
        $analysisScreens = [
            '103-screen-uploaded-files-list',
            '104-screen-analysis-requests',
            '105-screen-document-analysis'
        ];

        $hasAnalysisSubPages = ProjectPage::where('parent_id', $pageId)
            ->whereIn('sandbox_custom_screen_folder', $analysisScreens)
            ->exists();

        return response()->json(['hasAnalysisSubPages' => $hasAnalysisSubPages]);
    }
}