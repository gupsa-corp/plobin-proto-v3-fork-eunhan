<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;

class TemplateCommonService
{
    /**
     * 현재 템플릿 화면 정보를 반환
     */
    public static function getCurrentTemplateScreenInfo()
    {
        $segments = Request::segments();

        return [
            'sandbox' => $segments[1] ?? 'unknown',
            'domain' => $segments[2] ?? 'unknown',
            'screen' => $segments[3] ?? 'unknown',
            'title' => ucwords(str_replace('-', ' ', $segments[3] ?? 'Unknown Screen')),
            'path' => Request::path()
        ];
    }

    /**
     * 템플릿 업로드 경로 정보를 반환
     */
    public static function getTemplateUploadPaths()
    {
        return [
            'upload' => '/sandbox/upload',
            'temp' => '/sandbox/temp',
            'download' => '/sandbox/download',
            'storage' => storage_path('app/sandbox')
        ];
    }

    /**
     * 파일 아이콘을 반환
     */
    public static function getFileIcon($extension)
    {
        $icons = [
            'pdf' => '📄',
            'doc' => '📝',
            'docx' => '📝',
            'xls' => '📊',
            'xlsx' => '📊',
            'ppt' => '📽️',
            'pptx' => '📽️',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'zip' => '📦',
            'rar' => '📦',
            'txt' => '📄',
            'csv' => '📊',
            'mp4' => '🎥',
            'mp3' => '🎵',
            'default' => '📄'
        ];

        return $icons[strtolower($extension)] ?? $icons['default'];
    }

    /**
     * 파일 크기를 포맷
     */
    public static function formatFileSize($bytes)
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . ' MB';
        } else {
            return round($bytes / 1073741824, 2) . ' GB';
        }
    }

    /**
     * 파일 타입 이름을 반환
     */
    public static function getFileTypeName($extension)
    {
        $types = [
            'pdf' => 'PDF 문서',
            'doc' => 'Word 문서',
            'docx' => 'Word 문서',
            'xls' => 'Excel 스프레드시트',
            'xlsx' => 'Excel 스프레드시트',
            'ppt' => 'PowerPoint 프레젠테이션',
            'pptx' => 'PowerPoint 프레젠테이션',
            'jpg' => '이미지',
            'jpeg' => '이미지',
            'png' => '이미지',
            'gif' => '이미지',
            'zip' => '압축 파일',
            'rar' => '압축 파일',
            'txt' => '텍스트 문서',
            'csv' => 'CSV 파일',
            'mp4' => '비디오',
            'mp3' => '오디오',
            'default' => '파일'
        ];

        return $types[strtolower($extension)] ?? $types['default'];
    }

    /**
     * 화면 URL을 생성
     */
    public static function getScreenUrl($screenType, $screenName)
    {
        $segments = Request::segments();
        $sandbox = $segments[1] ?? 'sandbox';
        $domain = $segments[2] ?? '101-domain-rfx';

        // screenName에서 숫자 접두사 제거
        $screenPath = preg_replace('/^\d+-/', '', $screenName);

        return "/{$sandbox}/{$domain}/{$screenName}";
    }

    /**
     * API URL을 생성
     */
    public static function getApiUrl($endpoint)
    {
        return "/api/{$endpoint}";
    }
}