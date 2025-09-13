<?php

namespace App\Http\Requests\Api\Sandbox;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 기본적으로 모든 인증된 사용자가 파일 업로드를 허용
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB (킬로바이트 단위)
                File::types([
                    'jpg', 'jpeg', 'png', 'gif', 'webp', // 이미지
                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', // 문서
                    'txt', 'csv', 'json', 'xml', // 텍스트 파일
                    'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', // 비디오
                    'mp3', 'wav', 'flac', 'aac', 'ogg', // 오디오
                    'zip', 'rar', '7z', 'tar', 'gz' // 압축 파일
                ])
                ->max(10 * 1024) // 10MB (킬로바이트 단위)
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => '업로드할 파일을 선택해주세요.',
            'file.file' => '올바른 파일 형식이 아닙니다.',
            'file.max' => '파일 크기가 너무 큽니다. 최대 10MB까지 허용됩니다.',
            'file.mimes' => '지원하지 않는 파일 형식입니다.',
            'file.types' => '지원하지 않는 파일 형식입니다. 허용되는 형식: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, TXT, CSV, MP4, MP3, ZIP 등'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file' => '파일'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');

            if ($file) {
                // 추가적인 보안 검증
                $this->validateFileSecurity($validator, $file);

                // 파일명 검증
                $this->validateFileName($validator, $file);
            }
        });
    }

    /**
     * 파일 보안 검증
     */
    private function validateFileSecurity($validator, $file): void
    {
        $originalName = $file->getClientOriginalName();

        // 위험한 파일명 패턴 검증
        $dangerousPatterns = [
            '..', // 디렉토리 트래버설
            '/', // 경로 구분자
            '\\', // 윈도우 경로 구분자
            '<script', // XSS 시도
            'javascript:', // JavaScript 프로토콜
            'data:', // Data URL
            'vbscript:', // VBScript
            'onload=', // 이벤트 핸들러
            'onerror=' // 이벤트 핸들러
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (stripos($originalName, $pattern) !== false) {
                $validator->errors()->add('file', '보안상 허용되지 않는 파일명입니다.');
                break;
            }
        }

        // MIME 타입과 파일 확장자 일치 검증
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        $mimeToExt = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'text/plain' => ['txt'],
            'text/csv' => ['csv'],
            'application/json' => ['json'],
            'application/xml' => ['xml'],
            'video/mp4' => ['mp4'],
            'audio/mpeg' => ['mp3'],
            'application/zip' => ['zip'],
            'application/x-rar-compressed' => ['rar'],
        ];

        if (isset($mimeToExt[$mimeType])) {
            if (!in_array($extension, $mimeToExt[$mimeType])) {
                $validator->errors()->add('file', '파일 형식과 확장자가 일치하지 않습니다.');
            }
        }
    }

    /**
     * 파일명 검증
     */
    private function validateFileName($validator, $file): void
    {
        $originalName = $file->getClientOriginalName();

        // 파일명 길이 검증 (255자 제한)
        if (strlen($originalName) > 255) {
            $validator->errors()->add('file', '파일명이 너무 깁니다.');
            return;
        }

        // 파일명에 허용되지 않는 문자 검증
        if (preg_match('/[<>:"\/\\|?*\x00-\x1f]/', $originalName)) {
            $validator->errors()->add('file', '파일명에 허용되지 않는 문자가 포함되어 있습니다.');
        }

        // 빈 파일명 검증
        if (empty(trim($originalName))) {
            $validator->errors()->add('file', '파일명이 비어있습니다.');
        }

        // 숨겨진 파일 검증 (점으로 시작하는 파일)
        if (strpos($originalName, '.') === 0) {
            $validator->errors()->add('file', '숨겨진 파일은 업로드할 수 없습니다.');
        }
    }

    /**
     * 파일 크기 포맷팅 헬퍼
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
