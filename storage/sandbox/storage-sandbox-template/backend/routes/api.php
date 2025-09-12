<?php

use App\Http\Controllers\Api\Sandbox\FileUploadController;
use App\Http\Controllers\AiSummaryController;
use App\Http\Controllers\AiSummaryResultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sandbox File Upload API Routes
|--------------------------------------------------------------------------
|
| 이 파일은 샌드박스 파일 업로드 시스템의 API 라우트를 정의합니다.
| 모든 라우트는 /api/sandbox/ 접두사를 사용합니다.
|
*/

// 파일 업로드 관련 라우트
Route::prefix('sandbox')->middleware(['auth:sanctum'])->group(function () {

    // 파일 업로드
    Route::post('/file-upload', [FileUploadController::class, 'upload'])
        ->name('api.sandbox.file.upload');

    // 파일 목록 조회
    Route::get('/files', [FileUploadController::class, 'index'])
        ->name('api.sandbox.files.index');

    // 파일 상세 정보 조회
    Route::get('/files/{id}', [FileUploadController::class, 'show'])
        ->name('api.sandbox.files.show');

    // 파일 다운로드
    Route::get('/files/{id}/download', [FileUploadController::class, 'download'])
        ->name('api.sandbox.files.download');

    // 파일 정보 업데이트
    Route::put('/files/{id}', [FileUploadController::class, 'update'])
        ->name('api.sandbox.files.update');

    // 파일 삭제
    Route::delete('/files/{id}', [FileUploadController::class, 'destroy'])
        ->name('api.sandbox.files.destroy');

    // 파일 통계 정보
    Route::get('/files-stats', [FileUploadController::class, 'stats'])
        ->name('api.sandbox.files.stats');
});

// AI 요약 관련 라우트
Route::prefix('ai-summary')->middleware(['auth:sanctum'])->group(function () {
    
    // AI 요약 요청 관리
    Route::get('/requests', [AiSummaryController::class, 'index'])
        ->name('api.ai-summary.requests.index');
    
    Route::post('/requests', [AiSummaryController::class, 'store'])
        ->name('api.ai-summary.requests.store');
    
    Route::post('/requests/{id}/refresh', [AiSummaryController::class, 'refresh'])
        ->name('api.ai-summary.requests.refresh');
    
    // AI 요약 결과 관리
    Route::get('/results', [AiSummaryResultController::class, 'index'])
        ->name('api.ai-summary.results.index');
    
    Route::post('/results', [AiSummaryResultController::class, 'store'])
        ->name('api.ai-summary.results.store');
    
    Route::get('/results/{id}', [AiSummaryResultController::class, 'show'])
        ->name('api.ai-summary.results.show');
    
    Route::put('/results/{id}', [AiSummaryResultController::class, 'update'])
        ->name('api.ai-summary.results.update');
    
    Route::delete('/results/{id}', [AiSummaryResultController::class, 'destroy'])
        ->name('api.ai-summary.results.destroy');
});

/*
|--------------------------------------------------------------------------
| 추가 라우트 예시
|--------------------------------------------------------------------------
|
| 필요에 따라 아래와 같은 추가 라우트를 사용할 수 있습니다:
|

// 다중 파일 업로드 (배치 처리)
Route::post('/files/batch', [FileUploadController::class, 'batchUpload'])
    ->name('api.sandbox.files.batch.upload');

// 파일 검색
Route::get('/files/search/{query}', [FileUploadController::class, 'search'])
    ->name('api.sandbox.files.search');

// 파일 태그 관리
Route::post('/files/{id}/tags', [FileUploadController::class, 'addTag'])
    ->name('api.sandbox.files.tags.add');

Route::delete('/files/{id}/tags/{tag}', [FileUploadController::class, 'removeTag'])
    ->name('api.sandbox.files.tags.remove');

// 파일 공유 링크 생성
Route::post('/files/{id}/share', [FileUploadController::class, 'createShareLink'])
    ->name('api.sandbox.files.share.create');

// 파일 공유 링크로 접근 (인증 불필요)
Route::get('/shared/{token}', [FileUploadController::class, 'accessSharedFile'])
    ->middleware('signed')
    ->withoutMiddleware(['auth:sanctum'])
    ->name('api.sandbox.files.shared.access');

*/
