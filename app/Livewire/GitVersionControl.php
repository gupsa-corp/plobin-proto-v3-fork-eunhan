<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Process;

class GitVersionControl extends Component
{
    public $isGitRepo = false;
    public $currentBranch = '';
    public $changedFiles = [];
    public $commitMessage = '';
    public $commitHistory = [];
    public $sandboxPath;

    public function mount()
    {
        $this->sandboxPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . '/storage-sandbox-1');
        
        // 샌드박스 디렉토리가 없으면 생성
        if (!is_dir($this->sandboxPath)) {
            mkdir($this->sandboxPath, 0755, true);
        }
        
        $this->checkGitStatus();
        $this->loadChangedFiles();
        $this->loadCommitHistory();
    }

    public function checkGitStatus()
    {
        // Git 저장소 여부 확인 (샌드박스 디렉토리에서)
        $result = Process::path($this->sandboxPath)->run('git rev-parse --is-inside-work-tree 2>/dev/null');
        $this->isGitRepo = $result->successful();

        if ($this->isGitRepo) {
            // 현재 브랜치 확인
            $branchResult = Process::path($this->sandboxPath)->run('git branch --show-current');
            $this->currentBranch = trim($branchResult->output());
        }
    }

    public function initializeGit()
    {
        try {
            // Git 저장소 초기화 (샌드박스 디렉토리에서)
            $result = Process::path($this->sandboxPath)->run('git init');
            
            if ($result->successful()) {
                // 기본 설정
                Process::path($this->sandboxPath)->run('git config user.name "Plobin Proto V3"');
                Process::path($this->sandboxPath)->run('git config user.email "dev@plobin.com"');
                
                // 초기 README 파일 생성
                file_put_contents($this->sandboxPath . '/README.md', "# 샌드박스 프로젝트\n\n이것은 샌드박스 환경의 Git 저장소입니다.\n");
                
                $this->checkGitStatus();
                $this->loadChangedFiles();
                
                session()->flash('success', 'Git 저장소가 성공적으로 초기화되었습니다. (' . $this->sandboxPath . ')');
            } else {
                session()->flash('error', 'Git 저장소 초기화에 실패했습니다: ' . $result->errorOutput());
            }
        } catch (\Exception $e) {
            session()->flash('error', '오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function loadChangedFiles()
    {
        if (!$this->isGitRepo) {
            return;
        }

        $this->changedFiles = [];

        try {
            // 스테이징된 파일
            $stagedResult = Process::path($this->sandboxPath)->run('git diff --cached --name-status');
            $stagedFiles = [];
            if ($stagedResult->successful() && !empty(trim($stagedResult->output()))) {
                foreach (explode("\n", trim($stagedResult->output())) as $line) {
                    if (!empty($line)) {
                        $parts = preg_split('/\s+/', $line, 2);
                        if (count($parts) === 2) {
                            $stagedFiles[$parts[1]] = $parts[0];
                        }
                    }
                }
            }

            // 변경된 파일 (스테이징되지 않은)
            $changedResult = Process::path($this->sandboxPath)->run('git diff --name-status');
            if ($changedResult->successful() && !empty(trim($changedResult->output()))) {
                foreach (explode("\n", trim($changedResult->output())) as $line) {
                    if (!empty($line)) {
                        $parts = preg_split('/\s+/', $line, 2);
                        if (count($parts) === 2) {
                            $this->changedFiles[] = [
                                'path' => $parts[1],
                                'status' => $parts[0],
                                'staged' => false
                            ];
                        }
                    }
                }
            }

            // 스테이징된 파일 추가
            foreach ($stagedFiles as $path => $status) {
                $this->changedFiles[] = [
                    'path' => $path,
                    'status' => $status,
                    'staged' => true
                ];
            }

            // 새로운 파일 (untracked)
            $untrackedResult = Process::path($this->sandboxPath)->run('git ls-files --others --exclude-standard');
            if ($untrackedResult->successful() && !empty(trim($untrackedResult->output()))) {
                foreach (explode("\n", trim($untrackedResult->output())) as $file) {
                    if (!empty($file)) {
                        $this->changedFiles[] = [
                            'path' => $file,
                            'status' => 'A',
                            'staged' => false
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', '변경사항 로드 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function stageFile($filePath)
    {
        try {
            $result = Process::path($this->sandboxPath)->run(['git', 'add', $filePath]);
            
            if ($result->successful()) {
                $this->loadChangedFiles();
                session()->flash('success', '파일이 스테이징되었습니다: ' . $filePath);
            } else {
                session()->flash('error', '스테이징에 실패했습니다: ' . $result->errorOutput());
            }
        } catch (\Exception $e) {
            session()->flash('error', '오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function stageAll()
    {
        try {
            $result = Process::path($this->sandboxPath)->run('git add .');
            
            if ($result->successful()) {
                $this->loadChangedFiles();
                session()->flash('success', '모든 변경사항이 스테이징되었습니다.');
            } else {
                session()->flash('error', '일괄 스테이징에 실패했습니다: ' . $result->errorOutput());
            }
        } catch (\Exception $e) {
            session()->flash('error', '오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function unstageAll()
    {
        try {
            $result = Process::path($this->sandboxPath)->run('git reset HEAD .');
            
            if ($result->successful()) {
                $this->loadChangedFiles();
                session()->flash('success', '모든 스테이징이 해제되었습니다.');
            } else {
                session()->flash('error', '스테이징 해제에 실패했습니다: ' . $result->errorOutput());
            }
        } catch (\Exception $e) {
            session()->flash('error', '오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function commit()
    {
        if (empty($this->commitMessage)) {
            session()->flash('error', '커밋 메시지를 입력해주세요.');
            return;
        }

        try {
            $result = Process::path($this->sandboxPath)->run(['git', 'commit', '-m', $this->commitMessage]);
            
            if ($result->successful()) {
                $this->commitMessage = '';
                $this->loadChangedFiles();
                $this->loadCommitHistory();
                session()->flash('success', '커밋이 완료되었습니다.');
            } else {
                session()->flash('error', '커밋에 실패했습니다: ' . $result->errorOutput());
            }
        } catch (\Exception $e) {
            session()->flash('error', '오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function loadCommitHistory()
    {
        if (!$this->isGitRepo) {
            return;
        }

        try {
            $result = Process::path($this->sandboxPath)->run('git log --oneline --format="%H|%an|%ad|%s" --date=short -10');
            
            $this->commitHistory = [];
            if ($result->successful() && !empty(trim($result->output()))) {
                foreach (explode("\n", trim($result->output())) as $line) {
                    if (!empty($line)) {
                        $parts = explode('|', $line, 4);
                        if (count($parts) === 4) {
                            $this->commitHistory[] = [
                                'hash' => $parts[0],
                                'author' => $parts[1],
                                'date' => $parts[2],
                                'message' => $parts[3]
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // 히스토리 로드 실패는 무시 (빈 저장소일 수 있음)
        }
    }

    public function refreshHistory()
    {
        $this->loadCommitHistory();
        $this->loadChangedFiles();
        session()->flash('success', '히스토리가 새로고침되었습니다.');
    }

    public function render()
    {
        return view('700-page-sandbox.708-livewire-git-version-control');
    }
}