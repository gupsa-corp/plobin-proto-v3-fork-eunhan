<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SandboxTemplate;
use Illuminate\Support\Facades\File;

class SandboxTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // storage/sandbox-template/ 디렉토리에서 기존 템플릿들을 찾아서 DB에 등록
        $templatePath = storage_path(env('SANDBOX_TEMPLATE_PATH', 'sandbox/template'));
        
        if (File::exists($templatePath)) {
            $templateDirectories = File::directories($templatePath);
            
            foreach ($templateDirectories as $dir) {
                $templateName = basename($dir);
                
                // 이미 존재하는 템플릿은 건너뛰기
                if (SandboxTemplate::where('name', $templateName)->exists()) {
                    continue;
                }
                
                // 템플릿 설명 생성
                $description = $this->getTemplateDescription($templateName);
                
                SandboxTemplate::create([
                    'name' => $templateName,
                    'description' => $description,
                    'type' => 'system', // 기본적으로 시스템 템플릿으로 등록
                    'status' => 'active',
                    'created_by' => null, // 시스템 템플릿은 생성자가 없음
                    'usage_count' => 0,
                ]);
                
                $this->command->info("Template '{$templateName}' has been added to database.");
            }
        } else {
            $this->command->warn("Template directory does not exist: {$templatePath}");
        }
    }
    
    /**
     * 템플릿 이름에 따른 설명 생성
     */
    private function getTemplateDescription(string $templateName): string
    {
        return match ($templateName) {
            'default' => '기본 샌드박스 템플릿 - 표준 기능이 포함된 완전한 개발 환경',
            'minimal' => '최소 기능 템플릿 - 필수 기능만 포함된 가벼운 개발 환경',
            'react' => 'React 개발 템플릿 - React 기반 프론트엔드 개발 환경',
            'vue' => 'Vue.js 개발 템플릿 - Vue.js 기반 프론트엔드 개발 환경',
            'angular' => 'Angular 개발 템플릿 - Angular 기반 프론트엔드 개발 환경',
            'node' => 'Node.js 개발 템플릿 - Node.js 백엔드 개발 환경',
            'python' => 'Python 개발 템플릿 - Python 기반 개발 환경',
            'php' => 'PHP 개발 템플릿 - PHP 기반 웹 개발 환경',
            default => "{$templateName} 개발 템플릿 - 전문적인 개발 환경",
        };
    }
}
