<?php

use App\Http\Controllers\User\CheckEmail\Controller as CheckEmailController;
use App\Http\Controllers\User\SignupPlobin\Controller as SignupController;
use App\Http\Controllers\User\LoginPlobin\Controller as LoginController;
use App\Http\Controllers\User\LogoutPlobin\Controller as LogoutController;
use App\Http\Controllers\User\ForgotPassword\Controller as ForgotPasswordController;
use App\Http\Controllers\User\ResetPassword\Controller as ResetPasswordController;
use App\Http\Controllers\User\Me\Controller as MeController;
use App\Http\Controllers\User\GetProfile\Controller as GetProfileController;
use App\Http\Controllers\User\VerifyPassword\Controller as VerifyPasswordController;
use App\Http\Controllers\User\UpdateProfile\Controller as UpdateProfileController;
use App\Http\Controllers\User\ChangePassword\Controller as ChangePasswordController;
use App\Http\Controllers\User\ValidatePhone\Controller as ValidatePhoneController;
use App\Http\Controllers\User\GetCountries\Controller as GetCountriesController;
use App\Http\Controllers\User\FormatPhone\Controller as FormatPhoneController;
use App\Http\Controllers\User\GetPhoneInfo\Controller as GetPhoneInfoController;
use App\Http\Controllers\User\GetAllCountryCodes\Controller as GetAllCountryCodesController;
use App\Http\Controllers\Organization\CreateOrganization\Controller as CreateOrganizationController;
use App\Http\Controllers\Organization\GetOrganizations\Controller as GetOrganizationsController;
use App\Http\Controllers\Organization\GetOrganization\Controller as GetOrganizationController;
use App\Http\Controllers\Organization\UpdateOrganization\Controller as UpdateOrganizationController;
use App\Http\Controllers\Organization\DeleteOrganization\Controller as DeleteOrganizationController;
use App\Http\Controllers\Organization\CheckUrlPath\Controller as CheckUrlPathController;
use App\Http\Controllers\ProjectPage\Store\Controller as ProjectPageStoreController;
use App\Http\Controllers\ProjectPage\Show\Controller as ProjectPageShowController;
use App\Http\Controllers\ProjectPage\Update\Controller as ProjectPageUpdateController;
use App\Http\Controllers\ProjectPage\Destroy\Controller as ProjectPageDestroyController;
use App\Http\Controllers\ProjectPage\UpdateOrder\Controller as ProjectPageUpdateOrderController;
use App\Http\Controllers\Organization\SearchMembers\Controller as SearchMembersController;
use App\Http\Controllers\Organization\InviteMembers\Controller as InviteMembersController;
use App\Http\Controllers\OrganizationBilling\GetBillingData\Controller as GetBillingDataController;
use App\Http\Controllers\OrganizationBilling\ProcessPayment\Controller as ProcessPaymentController;
use App\Http\Controllers\OrganizationBilling\CreateBusinessInfo\Controller as CreateBusinessInfoController;
use App\Http\Controllers\OrganizationBilling\BusinessLookup\Controller as BusinessLookupController;
use App\Http\Controllers\OrganizationBilling\DownloadReceipt\Controller as DownloadReceiptController;
use App\Http\Controllers\OrganizationBilling\GetAvailablePlans\Controller as GetAvailablePlansController;
use App\Http\Controllers\User\SearchUsers\Controller as SearchUsersController;
use App\Http\Controllers\Sandbox\FileList\Controller as SandboxFileListController;
use App\Http\Controllers\Sandbox\FileUpload\Controller as SandboxFileUploadController;
use App\Http\Controllers\Sandbox\ListSandboxes\Controller as ListSandboxesController;
use App\Http\Controllers\Sandbox\ListScreens\Controller as ListScreensController;
use App\Http\Controllers\Sandbox\ProjectSandbox\Controller as ProjectSandboxController;
use App\Http\Controllers\Sandbox\SandboxTemplate\Controller as SandboxTemplateController;
use App\Http\Controllers\PlatformAdmin\Pricing\Controller as PlatformAdminPricingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - 단순하게 정리됨
|--------------------------------------------------------------------------
|
| 두 가지 인증만 지원:
| 1. 웹 세션 인증 (브라우저)
| 2. API 토큰 인증 (Bearer token)
|
*/

Route::prefix('auth')->group(function () {
    // 공개 API (인증 불필요) - 개발용으로 제한 완화
    Route::post('/check-email', CheckEmailController::class);
    Route::post('/validate-phone', ValidatePhoneController::class);
    Route::post('/format-phone', FormatPhoneController::class);
    Route::post('/phone-info', GetPhoneInfoController::class);
    Route::post('/signup', SignupController::class);
    Route::post('/login', LoginController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/reset-password', ResetPasswordController::class);
});

// 공개 API
Route::get('/countries', GetCountriesController::class);
Route::get('/country-codes', GetAllCountryCodesController::class);


Route::prefix('auth')->group(function () {
    // 인증 필요한 API (웹 세션 OR API 토큰)
    Route::middleware(['auth.web-or-token'])->group(function () {
        Route::get('/me', MeController::class);
        Route::post('/logout', LogoutController::class);
    });
});

// 사용자 프로필 관리 API (개발용 - 인증 제거)
Route::prefix('user')->group(function () {
    Route::get('/profile', GetProfileController::class);
    Route::post('/verify-password', VerifyPasswordController::class);
    Route::put('/profile', UpdateProfileController::class);
    Route::put('/password', ChangePasswordController::class);
});

// 사용자 검색 API (플랫폼 관리자용 - 개발용으로 인증 제거)
Route::get('/users/search', SearchUsersController::class);

Route::prefix('organizations')->group(function () {
    Route::get('/list', GetOrganizationsController::class);
    Route::post('/create', CreateOrganizationController::class);
    Route::get('/check-url/{url_path}', CheckUrlPathController::class);
    Route::get('/{organization}', GetOrganizationController::class);
    Route::put('/{organization}', UpdateOrganizationController::class);
    Route::delete('/{organization}', DeleteOrganizationController::class);

    // 조직 멤버 관리 API
    Route::prefix('{organization}/members')->group(function () {
        Route::get('/search', SearchMembersController::class);
        Route::post('/invite', InviteMembersController::class);
    });

    // 조직 결제 관리 API
    Route::prefix('{organization}/billing')->group(function () {
        Route::get('/data', GetBillingDataController::class);
        Route::get('/available-plans', GetAvailablePlansController::class);
        Route::post('/payment/confirm', ProcessPaymentController::class);
        Route::post('/business-info', CreateBusinessInfoController::class);
        Route::post('/business-lookup', BusinessLookupController::class);
        Route::post('/receipt/download', DownloadReceiptController::class);


        // 결제 수단 관리 API
        Route::get('/payment-methods', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'index']);
        Route::post('/payment-methods', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'store']);
        Route::put('/payment-methods/{paymentMethodId}', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'update']);
        Route::delete('/payment-methods/{paymentMethodId}', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'destroy']);
        Route::post('/payment-methods/{paymentMethodId}/set-default', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'setDefault']);

        // 결제 내역 API
        Route::get('/payment-history', [\App\Http\Controllers\OrganizationBilling\PaymentHistory\Controller::class, 'index']);
        Route::get('/payment-history/{paymentId}', [\App\Http\Controllers\OrganizationBilling\PaymentHistory\Controller::class, 'show']);

        // 라이센스 구매 API
        Route::get('/licenses', [\App\Http\Controllers\OrganizationBilling\Licenses\Controller::class, 'index']);
        Route::post('/licenses/purchase', [\App\Http\Controllers\OrganizationBilling\Licenses\Controller::class, 'purchase']);
        Route::get('/licenses/usage', [\App\Http\Controllers\OrganizationBilling\Licenses\Controller::class, 'usage']);

        // 토스페이먼츠 결제 검증 API
        Route::post('/verify-payment', [\App\Http\Controllers\OrganizationBilling\VerifyPayment\Controller::class, 'verify']);
        Route::post('/download-receipt', [\App\Http\Controllers\OrganizationBilling\DownloadReceipt\Controller::class, 'download']);
    });
});

// 프로젝트 페이지 관리 API (개발용 - 인증 제거)
Route::prefix('projects')->group(function () {
    Route::post('/{project}/pages', ProjectPageStoreController::class);
    Route::get('/{project}/pages/{page}', ProjectPageShowController::class);
    Route::put('/{project}/pages/{page}', ProjectPageUpdateController::class);
    Route::delete('/{project}/pages/{page}', ProjectPageDestroyController::class);


    // 프로젝트 샌드박스 관리 API
    Route::get('/{project}/sandboxes', [ProjectSandboxController::class, 'index']);
    Route::post('/{project}/sandboxes', [ProjectSandboxController::class, 'store']);
    Route::get('/{project}/sandboxes/{sandbox}', [ProjectSandboxController::class, 'show']);
    Route::put('/{project}/sandboxes/{sandbox}', [ProjectSandboxController::class, 'update']);
    Route::delete('/{project}/sandboxes/{sandbox}', [ProjectSandboxController::class, 'destroy']);
    Route::post('/{project}/sandboxes/{sandbox}/copy', [ProjectSandboxController::class, 'copy']);
});

// 페이지 커스텀 화면 API (개발용 - 인증 없음)
Route::prefix('pages')->group(function () {
    Route::post('/{pageId}/custom-screen', [\App\Http\CoreApi\Page\SetCustomScreen\Controller::class, 'setCustomScreen']);
});



// 테스트용 결제 API (인증 없음 - 개발용)
Route::prefix('test/organizations')->group(function () {
    Route::get('{organization}/billing/data', GetBillingDataController::class);
    Route::get('{organization}/billing/available-plans', GetAvailablePlansController::class);
    Route::post('{organization}/billing/business-info', CreateBusinessInfoController::class);
    Route::post('{organization}/billing/business-lookup', BusinessLookupController::class);
    Route::post('{organization}/billing/receipt/download', DownloadReceiptController::class);


    // 테스트용 결제 수단 관리 API
    Route::get('{organization}/billing/payment-methods', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'index']);
    Route::post('{organization}/billing/payment-methods', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'store']);
    Route::put('{organization}/billing/payment-methods/{paymentMethodId}', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'update']);
    Route::delete('{organization}/billing/payment-methods/{paymentMethodId}', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'destroy']);
    Route::post('{organization}/billing/payment-methods/{paymentMethodId}/set-default', [\App\Http\Controllers\OrganizationBilling\PaymentMethods\Controller::class, 'setDefault']);

    // 테스트용 결제 내역 API
    Route::get('{organization}/billing/payment-history', [\App\Http\Controllers\OrganizationBilling\PaymentHistory\Controller::class, 'index']);
    Route::get('{organization}/billing/payment-history/{paymentId}', [\App\Http\Controllers\OrganizationBilling\PaymentHistory\Controller::class, 'show']);

    // 테스트용 라이센스 구매 API
    Route::get('{organization}/billing/licenses', [\App\Http\Controllers\OrganizationBilling\Licenses\Controller::class, 'index']);
    Route::post('{organization}/billing/licenses/purchase', [\App\Http\Controllers\OrganizationBilling\Licenses\Controller::class, 'purchase']);
    Route::get('{organization}/billing/licenses/usage', [\App\Http\Controllers\OrganizationBilling\Licenses\Controller::class, 'usage']);

    // 플랜 변경 API
    Route::post('{organization}/billing/change-plan', [\App\Http\Controllers\OrganizationBilling\ChangePlan\Controller::class, 'changePlan']);

    // 토스페이먼츠 결제 검증 API (테스트용이므로 주석 처리 - 메인 인증 API 사용)
    // Route::post('{organization}/billing/verify-payment', [\App\Http\Controllers\OrganizationBilling\VerifyPayment\Controller::class, 'verify']);
    // Route::post('{organization}/billing/download-receipt', [\App\Http\Controllers\OrganizationBilling\DownloadReceipt\Controller::class, 'download']);
});

// 샌드박스 API (개발용 - 인증 없음)
Route::prefix('sandbox')->group(function () {
    Route::get('/list', [ListSandboxesController::class, 'listSandboxes']);
    Route::get('/files', [SandboxFileListController::class, 'getFileList']);
    Route::get('/screens', [ListScreensController::class, 'listScreens']);

    // 파일 업로드 관련 API
    Route::post('/file-upload', [SandboxFileUploadController::class, 'upload']);
    Route::get('/uploaded-files', [SandboxFileUploadController::class, 'index']);
    Route::get('/uploaded-files/{id}', [SandboxFileUploadController::class, 'show']);
    Route::get('/uploaded-files/{id}/download', [SandboxFileUploadController::class, 'download']);
    Route::delete('/uploaded-files/{id}', [SandboxFileUploadController::class, 'destroy']);
    
    // 샌드박스 템플릿 downloads 파일 목록 API
    Route::get('/sandbox-files', [SandboxFileUploadController::class, 'getSandboxFiles']);

    // 샌드박스 템플릿 관리 API
    Route::get('/templates', [SandboxTemplateController::class, 'index']);
    Route::post('/templates', [SandboxTemplateController::class, 'store']);
    Route::get('/templates/{template}', [SandboxTemplateController::class, 'show']);
    Route::put('/templates/{template}', [SandboxTemplateController::class, 'update']);
    Route::delete('/templates/{template}', [SandboxTemplateController::class, 'destroy']);
    Route::get('/templates/usage/stats', [SandboxTemplateController::class, 'usage']);


    // AI Server Callback API
    Route::prefix('storage-sandbox-1/callbacks')->group(function () {
        Route::post('/ai-server', \App\Http\Controllers\Sandbox\AIServer\Callback\Controller::class);
    });


    // Form Creator API
    Route::prefix('form-creator')->group(function () {
        Route::post('/save', \App\Http\Controllers\Sandbox\FormCreator\Save\Controller::class);
        Route::get('/list', \App\Http\Controllers\Sandbox\FormCreator\List\Controller::class);
        Route::get('/load/{filename}', \App\Http\Controllers\Sandbox\FormCreator\Load\Controller::class);
        Route::delete('/delete/{filename}', \App\Http\Controllers\Sandbox\FormCreator\Delete\Controller::class);
    });


    // Form Publisher API
    Route::prefix('form-publisher')->group(function () {
        Route::post('/save', \App\Http\Controllers\Sandbox\FormPublisher\Save\Controller::class);
    });
    
    // 샌드박스 템플릿 백엔드 API 프록시
    Route::any('storage-sandbox-template/backend/api.php/{path?}', function ($path = '') {
        $apiFile = storage_path('sandbox/storage-sandbox-template/backend/api.php');
        
        if (!file_exists($apiFile)) {
            return response()->json(['success' => false, 'message' => 'API 파일을 찾을 수 없습니다.'], 404);
        }
        
        // 원래 URI를 API 파일에 맞게 설정
        $_SERVER['REQUEST_URI'] = '/backend/api.php/' . $path;
        
        // 출력 버퍼링 시작
        ob_start();
        
        // API 파일 실행
        $result = include $apiFile;
        
        // 출력 내용 캡처
        $output = ob_get_clean();
        
        // 결과가 배열이면 JSON으로 반환
        if (is_array($result)) {
            return response()->json($result);
        }
        
        // 출력이 있으면 그대로 반환 (이미 JSON 헤더가 설정되어 있을 것)
        if (!empty($output)) {
            return response($output)->header('Content-Type', 'application/json');
        }
        
        return response()->json(['success' => false, 'message' => '응답이 없습니다.'], 500);
    })->where('path', '.*');
});

// 플랫폼 관리자 - 요금제 관리 API (개발용 - 인증 없음)
Route::prefix('platform/admin/pricing')->group(function () {
    Route::get('/statistics', [PlatformAdminPricingController::class, 'getStatistics']);
    Route::get('/plans', [PlatformAdminPricingController::class, 'getPlans']);
    Route::post('/plans', [PlatformAdminPricingController::class, 'createPlan']);
    Route::get('/plans/{id}', [PlatformAdminPricingController::class, 'showPlan']);
    Route::put('/plans/{id}', [PlatformAdminPricingController::class, 'updatePlan']);
    Route::delete('/plans/{id}', [PlatformAdminPricingController::class, 'deletePlan']);
    Route::get('/subscriptions', [PlatformAdminPricingController::class, 'getSubscriptions']);
    Route::put('/subscriptions/{id}', [PlatformAdminPricingController::class, 'updateSubscription']);
});

// 플랫폼 관리자 - 권한 관리 API (개발용 - 인증 없음)
Route::prefix('platform/admin/permissions')->group(function () {
    Route::get('/matrix', [\App\Http\Controllers\PlatformAdmin\Permissions\PermissionMatrix\Controller::class, 'getMatrix']);
    Route::get('/stats', [\App\Http\Controllers\PlatformAdmin\Permissions\PermissionStats\Controller::class, 'getStats']);
    Route::post('/roles/permissions', [\App\Http\Controllers\PlatformAdmin\Permissions\RolePermissions\Controller::class, 'updateRolePermissions']);
    Route::post('/', [\App\Http\Controllers\PlatformAdmin\Permissions\CreatePermission\Controller::class, 'create']);
    Route::get('/export', [\App\Http\Controllers\PlatformAdmin\Permissions\ExportPermissions\Controller::class, 'export']);
    Route::delete('/roles/{id}', [\App\Http\Controllers\PlatformAdmin\Permissions\DeleteRole\Controller::class, 'delete']);
});

// 코어 권한 API (개발용 - 인증 없음)
Route::get('/core/permissions', [\App\Http\Controllers\Core\Permissions\Controller::class, 'getRoles']);

// E2E 테스트 API (개발용 - 인증 없음)
Route::prefix('test/e2e')->group(function () {
    // 시스템 상태 조회
    Route::get('/status', [\App\Http\Controllers\Api\Test\Controller::class, 'status']);

    // 함수 실행 테스트
    Route::post('/execute-function', [\App\Http\Controllers\Api\Test\Controller::class, 'executeFunction']);

    // 파일 조회/쿼리 테스트
    Route::get('/query-files', [\App\Http\Controllers\Api\Test\Controller::class, 'queryFiles']);

    // 파일 수정 테스트
    Route::post('/modify-files', [\App\Http\Controllers\Api\Test\Controller::class, 'modifyFiles']);

    // 함수 정보 조회
    Route::get('/function-info/{functionName}', [\App\Http\Controllers\Api\Test\Controller::class, 'getFunctionInfo']);

    // 테스트 함수 생성
    Route::post('/create-test-function', [\App\Http\Controllers\Api\Test\Controller::class, 'createTestFunction']);
});
