<?php

namespace App\Http\Controllers\Sandbox\Downloads;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    private array $downloadableFiles = [
        // 테스트 데이터
        'test-users.csv' => [
            'name' => '사용자 목록',
            'category' => 'test-data',
            'content' => "name,email,role,created_at\n홍길동,hong@example.com,admin,2024-01-01\n김철수,kim@example.com,user,2024-01-02\n이영희,lee@example.com,editor,2024-01-03"
        ],
        'test-organizations.xlsx' => [
            'name' => '조직 데이터',
            'category' => 'test-data',
            'content' => 'binary_excel_data' // 실제로는 엑셀 바이너리 데이터
        ],
        'test-projects.json' => [
            'name' => '프로젝트 데이터',
            'category' => 'test-data',
            'content' => '{"projects":[{"id":1,"name":"프로젝트 A","status":"active"},{"id":2,"name":"프로젝트 B","status":"pending"}]}'
        ],
        
        // 문서 템플릿
        'project-plan-template.docx' => [
            'name' => '프로젝트 계획 템플릿',
            'category' => 'templates',
            'content' => 'binary_docx_data'
        ],
        'meeting-minutes-template.docx' => [
            'name' => '회의록 템플릿',
            'category' => 'templates',
            'content' => 'binary_docx_data'
        ],
        'requirements-template.docx' => [
            'name' => '요구사항 명세서 템플릿',
            'category' => 'templates',
            'content' => 'binary_docx_data'
        ],
        
        // 이미지 자료
        'sample-avatar.png' => [
            'name' => '샘플 아바타 이미지',
            'category' => 'images',
            'content' => 'binary_png_data'
        ],
        'company-logo.svg' => [
            'name' => '회사 로고',
            'category' => 'images',
            'content' => '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#007bff"/><text x="50" y="50" text-anchor="middle" dy=".3em" fill="white" font-family="Arial" font-size="14">LOGO</text></svg>'
        ],
        'placeholder-banner.jpg' => [
            'name' => '플레이스홀더 배너',
            'category' => 'images',
            'content' => 'binary_jpg_data'
        ],
        
        // 개발 도구
        'e2e-test-scripts.zip' => [
            'name' => 'E2E 테스트 스크립트',
            'category' => 'dev-tools',
            'content' => 'binary_zip_data'
        ],
        'database-seeds.sql' => [
            'name' => '데이터베이스 시드 파일',
            'category' => 'dev-tools',
            'content' => "INSERT INTO users (name, email) VALUES ('테스트 사용자', 'test@example.com');\nINSERT INTO organizations (name) VALUES ('테스트 조직');"
        ],
        'api-postman-collection.json' => [
            'name' => 'API 테스트 컬렉션',
            'category' => 'dev-tools',
            'content' => '{"info":{"name":"API 테스트","schema":"https://schema.getpostman.com/json/collection/v2.1.0/collection.json"},"item":[]}'
        ],
        
        // 백업 파일
        'database-backup.sql' => [
            'name' => '데이터베이스 백업',
            'category' => 'backups',
            'content' => 'database-backup-content'
        ],
        'config-backup.json' => [
            'name' => '설정 파일 백업',
            'category' => 'backups',
            'content' => 'config-backup-content'
        ],
        'storage-backup.tar.gz' => [
            'name' => '스토리지 백업',
            'category' => 'backups',
            'content' => 'binary_tar_data'
        ],
        
        // 사용자 매뉴얼
        'user-manual.pdf' => [
            'name' => '사용자 매뉴얼',
            'category' => 'manuals',
            'content' => 'binary_pdf_data'
        ],
        'admin-guide.pdf' => [
            'name' => '관리자 가이드',
            'category' => 'manuals',
            'content' => 'binary_pdf_data'
        ],
        'troubleshooting-guide.pdf' => [
            'name' => '문제해결 가이드',
            'category' => 'manuals',
            'content' => 'binary_pdf_data'
        ]
    ];

    public function download(Request $request, string $filename): mixed
    {
        if (!isset($this->downloadableFiles[$filename])) {
            return response()->json(['error' => '파일을 찾을 수 없습니다.'], 404);
        }

        $fileInfo = $this->downloadableFiles[$filename];
        $content = $fileInfo['content'];

        // 파일 확장자에 따른 MIME 타입 설정
        $mimeType = $this->getMimeType($filename);
        
        // 바이너리 파일의 경우 더미 데이터 생성
        if (str_starts_with($content, 'binary_')) {
            $content = $this->generateDummyBinary($content, $filename);
        }
        
        // 동적 콘텐츠 처리
        if ($content === 'database-backup-content') {
            $content = '-- 데이터베이스 백업 파일\n-- 생성일: ' . date('Y-m-d H:i:s') . '\n\n-- 테이블 구조 및 데이터 백업 내용이 여기에 위치합니다.';
        } elseif ($content === 'config-backup-content') {
            $content = '{"app":{"name":"Plobin","env":"local"},"database":{"default":"mysql"},"backup_date":"' . date('Y-m-d H:i:s') . '"}';
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        return response($content, 200, $headers);
    }

    private function getMimeType(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        return match($extension) {
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'json' => 'application/json',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'zip' => 'application/zip',
            'sql' => 'application/sql',
            'tar.gz' => 'application/gzip',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream'
        };
    }

    private function generateDummyBinary(string $type, string $filename): string
    {
        return match($type) {
            'binary_excel_data' => $this->generateDummyExcel(),
            'binary_docx_data' => $this->generateDummyWord(),
            'binary_png_data' => $this->generateDummyPng(),
            'binary_jpg_data' => $this->generateDummyJpg(),
            'binary_zip_data' => $this->generateDummyZip(),
            'binary_tar_data' => $this->generateDummyTar(),
            'binary_pdf_data' => $this->generateDummyPdf($filename),
            default => '더미 바이너리 데이터입니다.'
        };
    }

    private function generateDummyExcel(): string
    {
        // 간단한 CSV 형태로 엑셀 데이터 시뮬레이션
        return "조직명,멤버수,생성일\n샘플 조직 1,15,2024-01-01\n샘플 조직 2,8,2024-01-02\n샘플 조직 3,23,2024-01-03";
    }

    private function generateDummyWord(): string
    {
        return "이것은 더미 워드 문서 내용입니다.\n\n실제 환경에서는 적절한 워드 문서 템플릿이 제공됩니다.\n\n작성일: " . date('Y-m-d H:i:s');
    }

    private function generateDummyPng(): string
    {
        // 1x1 투명 PNG 데이터 (base64 디코딩)
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAFhAdkDrAAAAElFTkSuQmCC');
    }

    private function generateDummyJpg(): string
    {
        // 1x1 빨간색 JPEG 데이터 (base64 디코딩)
        return base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wA==');
    }

    private function generateDummyZip(): string
    {
        return "PK\x03\x04더미 ZIP 파일입니다.";
    }

    private function generateDummyTar(): string
    {
        return "더미 TAR 압축 파일입니다.";
    }

    private function generateDummyPdf(string $filename): string
    {
        $fileInfo = $this->downloadableFiles[$filename];
        $content = $fileInfo['name'] . " PDF 문서\n\n";
        $content .= "이것은 " . $fileInfo['name'] . "의 더미 PDF 내용입니다.\n";
        $content .= "실제 환경에서는 적절한 PDF 문서가 제공됩니다.\n\n";
        $content .= "생성일: " . date('Y-m-d H:i:s');
        
        return $content;
    }

    public function getStats(): JsonResponse
    {
        // 더미 통계 데이터
        $stats = [
            'total_downloads' => 1234,
            'monthly_downloads' => 567,
            'weekly_downloads' => 89,
            'daily_downloads' => 12,
            'popular_files' => [
                'test-users.csv' => 234,
                'database-backup.sql' => 189,
                'user-manual.pdf' => 156
            ]
        ];

        return response()->json($stats);
    }
}