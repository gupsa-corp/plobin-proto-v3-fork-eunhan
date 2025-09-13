<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name', 255); // 원본 파일명
            $table->string('stored_name', 255); // 저장된 파일명 (타임스탬프 포함)
            $table->string('file_path', 500); // 저장 경로
            $table->bigInteger('file_size'); // 파일 크기 (bytes)
            $table->string('mime_type', 100); // MIME 타입
            $table->timestamp('uploaded_at'); // 업로드 시간
            $table->unsignedBigInteger('user_id')->nullable(); // 업로드한 사용자 ID
            $table->timestamps(); // created_at, updated_at

            // 인덱스
            $table->index('user_id');
            $table->index('uploaded_at');
            $table->index('mime_type');
            $table->index('file_size');

            // 외래 키 제약조건 (선택적)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};

/*
|--------------------------------------------------------------------------
| 추가 마이그레이션 예시
|--------------------------------------------------------------------------
|
| 필요에 따라 아래와 같은 추가 테이블을 생성할 수 있습니다:
|

// 파일 태그 테이블
Schema::create('file_tags', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50)->unique();
    $table->string('color', 7)->default('#6B7280'); // HEX 색상 코드
    $table->timestamps();
});

// 파일-태그 관계 테이블
Schema::create('file_tag_relations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('file_id')->constrained('uploaded_files')->onDelete('cascade');
    $table->foreignId('tag_id')->constrained('file_tags')->onDelete('cascade');
    $table->timestamps();

    $table->unique(['file_id', 'tag_id']);
});

// 파일 공유 링크 테이블
Schema::create('file_shares', function (Blueprint $table) {
    $table->id();
    $table->foreignId('file_id')->constrained('uploaded_files')->onDelete('cascade');
    $table->string('token', 64)->unique();
    $table->timestamp('expires_at')->nullable();
    $table->integer('max_downloads')->nullable();
    $table->integer('download_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('token');
    $table->index('expires_at');
});

// 파일 업로드 세션 테이블 (대용량 파일 청킹 업로드 지원용)
Schema::create('upload_sessions', function (Blueprint $table) {
    $table->id();
    $table->string('session_id', 64)->unique();
    $table->string('original_name', 255);
    $table->string('mime_type', 100);
    $table->bigInteger('total_size');
    $table->integer('total_chunks');
    $table->json('uploaded_chunks')->nullable(); // 업로드된 청크 목록
    $table->boolean('is_completed')->default(false);
    $table->timestamp('expires_at');
    $table->unsignedBigInteger('user_id')->nullable();
    $table->timestamps();

    $table->index('session_id');
    $table->index('user_id');
    $table->index('expires_at');
});

*/
