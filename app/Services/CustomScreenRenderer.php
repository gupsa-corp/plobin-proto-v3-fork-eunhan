<?php

namespace App\Services;

class CustomScreenRenderer
{
    /**
     * 커스텀 화면의 블레이드 파일을 렌더링합니다.
     *
     * @param string $filePath 렌더링할 블레이드 파일의 절대 경로
     * @param array $screenData 화면 데이터 (context로 사용)
     * @return string 렌더링된 HTML
     */
    public static function render($filePath, $screenData = [])
    {
        try {
            // 파일 존재 여부 확인
            if (!file_exists($filePath)) {
                return self::renderError('커스텀 화면 파일을 찾을 수 없습니다: ' . $filePath);
            }

            // 파일이 .blade.php로 끝나는지 확인
            if (!str_ends_with($filePath, '.blade.php')) {
                return self::renderError('유효하지 않은 블레이드 파일입니다: ' . $filePath);
            }

            // 샘플 데이터 준비
            $sampleData = self::prepareSampleData($screenData);
            
            // 파일에서 직접 템플릿 렌더링
            return self::renderFileTemplate($filePath, $sampleData);
            
        } catch (\Exception $e) {
            return self::renderError($e->getMessage());
        }
    }
    
    /**
     * 파일에서 직접 템플릿을 렌더링합니다.
     */
    private static function renderFileTemplate($filePath, $screenData)
    {
        try {
            // Blade 엔진을 사용하여 템플릿 컴파일 및 렌더링
            $templateContent = file_get_contents($filePath);
            
            // Laravel의 Blade 엔진 사용
            $blade = app('view')->getEngineResolver()->resolve('blade');
            $tempViewPath = 'custom_screen_' . md5($filePath . time());
            
            // 임시 파일을 생성하지 않고 직접 컴파일
            $php = $blade->getCompiler()->compileString($templateContent);
            
            // 변수를 추출하여 템플릿에서 사용 가능하게 만듦
            extract($screenData);
            
            // 출력 버퍼링 시작
            ob_start();
            
            // 컴파일된 PHP 코드 실행
            eval('?>' . $php);
            $html = ob_get_contents();
            
        } catch (\Exception $e) {
            $html = self::renderError($e->getMessage());
        } finally {
            ob_end_clean();
        }
        
        return $html;
    }

    private static function prepareSampleData($screenData)
    {
        // 기본 샘플 데이터
        $sampleData = [
            'title' => $screenData['title'] ?? '제목 없음',
            'description' => $screenData['description'] ?? '',
            'organizations' => [
                [
                    'id' => 1,
                    'name' => '테스트 조직',
                    'description' => '조직 설명',
                    'created_at' => '2025-09-01'
                ],
                [
                    'id' => 2,
                    'name' => '개발팀 조직',
                    'description' => '개발팀 조직 설명',
                    'created_at' => '2025-08-15'
                ],
                [
                    'id' => 3,
                    'name' => '관리자 조직',
                    'description' => '관리자 조직 설명',
                    'created_at' => '2025-07-20'
                ]
            ],
            'projects' => [
                [
                    'id' => 1,
                    'name' => '프로젝트 A',
                    'description' => '프로젝트 A 설명',
                    'created_at' => '2025-09-01'
                ],
                [
                    'id' => 2,
                    'name' => '프로젝트 B',
                    'description' => '프로젝트 B 설명',
                    'created_at' => '2025-08-20'
                ]
            ],
            'users' => [
                [
                    'id' => 1,
                    'name' => '홍길동',
                    'email' => 'hong@example.com',
                    'created_at' => '2025-08-01'
                ],
                [
                    'id' => 2,
                    'name' => '김철수',
                    'email' => 'kim@example.com',
                    'created_at' => '2025-07-15'
                ],
                [
                    'id' => 3,
                    'name' => '이영희',
                    'email' => 'lee@example.com',
                    'created_at' => '2025-07-01'
                ]
            ],
            'activities' => [
                [
                    'id' => 1,
                    'action' => '새로운 프로젝트 "웹 개발 프로젝트"가 생성되었습니다.',
                    'timestamp' => '2025-09-09 14:30:00',
                    'user' => '홍길동'
                ],
                [
                    'id' => 2,
                    'action' => '사용자 "김철수"가 팀에 합류했습니다.',
                    'timestamp' => '2025-09-09 13:15:00',
                    'user' => '관리자'
                ],
                [
                    'id' => 3,
                    'action' => '페이지 "커스텀 화면 설정"이 업데이트되었습니다.',
                    'timestamp' => '2025-09-09 12:45:00',
                    'user' => '이영희'
                ],
                [
                    'id' => 4,
                    'action' => '새로운 커스텀 화면 "활동 로그"가 생성되었습니다.',
                    'timestamp' => '2025-09-09 11:20:00',
                    'user' => '홍길동'
                ],
                [
                    'id' => 5,
                    'action' => '조직 설정이 변경되었습니다.',
                    'timestamp' => '2025-09-09 10:00:00',
                    'user' => '관리자'
                ]
            ]
        ];

        return $sampleData;
    }
    
    /**
     * 에러 메시지를 HTML로 렌더링합니다.
     */
    private static function renderError($message)
    {
        return '<div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                커스텀 화면 렌더링 오류
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>' . htmlspecialchars($message) . '</p>
                            </div>
                        </div>
                    </div>
                </div>';
    }
    
    /**
     * 안전한 블레이드 파일인지 검증합니다.
     */
    public static function isSecureFile($filePath)
    {
        // 파일이 샌드박스 디렉토리 내에 있는지 확인
        $realPath = realpath($filePath);
        $sandboxPath = realpath(storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox')));
        
        if (!$realPath || !str_starts_with($realPath, $sandboxPath)) {
            return false;
        }
        
        // 파일 내용에서 위험한 PHP 함수들을 체크
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $dangerousFunctions = [
                'exec', 'shell_exec', 'system', 'passthru', 'file_get_contents',
                'file_put_contents', 'unlink', 'rmdir', 'eval', '__halt_compiler',
                'fopen', 'fwrite', 'fclose'
            ];
            
            foreach ($dangerousFunctions as $function) {
                if (strpos($content, $function) !== false) {
                    return false;
                }
            }
        }
        
        return true;
    }
}