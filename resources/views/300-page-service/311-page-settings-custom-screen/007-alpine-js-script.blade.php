<script>
function customScreenSettingsPage() {
    return {
        // 기본 상태
        selectedCustomScreen: '{{ $currentCustomScreenId ?? '' }}',
        customScreenMode: '{{ !empty($currentCustomScreenId) ? 'enabled' : 'disabled' }}',
        loading: false,
        error: null,
        sandboxSelected: '{{ !empty($currentSandboxName) ? 'true' : 'false' }}' === 'true',
        
        // 샌드박스 모드 관련
        sandboxMode: '{{ $page->sandbox_mode ?? 'project' }}',
        projectAllowsIndividualSandbox: {{ $page->project->allow_individual_sandbox_per_page ? 'true' : 'false' }},
        projectSandboxName: '{{ $page->project->sandbox_folder ?? '' }}',
        
        // 3단계 선택 데이터
        sandboxes: [],
        domains: {!! json_encode($availableDomains ?? []) !!},
        allScreens: {!! json_encode($customScreens ?? []) !!},
        filteredScreens: [],
        
        // 현재 선택 상태
        selectedSandbox: '{{ $currentSandboxName ?? '' }}',
        selectedDomain: '{{ $currentDomain ?? '' }}',

        init() {
            // 사용 가능한 샌드박스 목록 초기화
            this.loadSandboxes();
            
            // 현재 선택된 화면이 있다면 초기 상태 설정
            if (this.selectedCustomScreen) {
                this.initializeFromCurrentScreen();
            }
        },

        // 샌드박스 목록 로드
        loadSandboxes() {
            @if(!empty($currentSandboxName))
            this.sandboxes = [
                {
                    name: '{{ $currentSandboxName }}',
                    title: '{{ ucwords(str_replace(["-", "_"], " ", $currentSandboxName)) }}'
                }
                // 추후 다른 샌드박스 추가 가능
            ];
            @else
            this.sandboxes = [];
            @endif
            
            // 현재 프로젝트의 샌드박스가 설정되어 있으면 자동 선택
            if (this.selectedSandbox) {
                this.loadDomains();
            }
        },

        // 도메인 목록 로드
        async loadDomains() {
            if (!this.selectedSandbox) {
                this.domains = [];
                this.selectedDomain = '';
                this.filteredScreens = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`/api/sandbox/custom-screens/${this.selectedSandbox}`);
                const data = await response.json();
                
                if (response.ok) {
                    this.allScreens = data.screens || [];
                    
                    // 도메인별로 그룹화
                    const domains = [...new Set(this.allScreens.map(screen => screen.domain))];
                    this.domains = domains.map(domain => ({
                        folder: domain,
                        title: this.formatDomainTitle(domain)
                    }));
                } else {
                    // 하드코딩된 도메인 목록 (백업)
                    this.domains = [
                        { folder: '100-domain-pms', title: 'Domain PMS' },
                        { folder: '101-domain-rfx', title: 'Domain RFX' }
                    ];
                }
            } catch (error) {
                console.error('도메인 로드 실패:', error);
                // 하드코딩된 도메인 목록 (백업)
                this.domains = [
                    { folder: '100-domain-pms', title: 'Domain PMS' },
                    { folder: '101-domain-rfx', title: 'Domain RFX' }
                ];
            } finally {
                this.loading = false;
            }
            
            // 현재 화면에 맞는 도메인 자동 선택
            this.autoSelectDomainFromCurrentScreen();
        },

        // 화면 목록 필터링
        loadScreens() {
            if (!this.selectedDomain) {
                this.filteredScreens = [];
                return;
            }

            // allScreens가 이미 loadDomains()에서 로드되었으므로 필터링만 하면 됨
            this.filteredScreens = this.allScreens.filter(screen => 
                screen.domain === this.selectedDomain
            );
        },

        // 현재 선택된 화면에서 초기 상태 설정
        initializeFromCurrentScreen() {
            const currentScreen = this.allScreens.find(s => s.id === this.selectedCustomScreen);
            if (currentScreen) {
                this.selectedDomain = currentScreen.domain;
                this.loadScreens();
            }
        },

        // 현재 화면에 맞는 도메인 자동 선택
        autoSelectDomainFromCurrentScreen() {
            if (this.selectedCustomScreen) {
                const currentScreen = this.allScreens.find(s => s.id === this.selectedCustomScreen);
                if (currentScreen && this.domains.find(d => d.folder === currentScreen.domain)) {
                    this.selectedDomain = currentScreen.domain;
                    this.loadScreens();
                }
            }
        },

        // 커스텀 화면 모드 변경 처리
        onCustomScreenModeChange() {
            if (this.customScreenMode === 'disabled') {
                // 사용 안함: 모든 선택 초기화
                this.selectedCustomScreen = '';
                this.selectedSandbox = '';
                this.selectedDomain = '';
                this.filteredScreens = [];
            } else {
                // 사용함: 초기 상태로 설정
                if (this.selectedCustomScreen) {
                    this.initializeFromCurrentScreen();
                }
            }
        },

        // 샌드박스 모드 변경 처리
        onSandboxModeChange() {
            if (this.sandboxMode === 'project') {
                // 프로젝트 따름 모드: 프로젝트의 샌드박스 사용
                this.selectedSandbox = this.projectSandboxName;
                this.loadDomainsForProjectSandbox();
            } else {
                // 개별 선택 모드: 초기화
                this.selectedSandbox = '';
                this.selectedDomain = '';
                this.domains = [];
                this.allScreens = [];
                this.filteredScreens = [];
            }
        },

        // 프로젝트 샌드박스용 도메인 로드
        async loadDomainsForProjectSandbox() {
            if (!this.projectSandboxName) return;
            
            this.loading = true;
            try {
                // SandboxService를 통해 프로젝트 샌드박스의 도메인과 화면 정보 가져오기
                const response = await fetch(`/api/sandbox/custom-screens/${this.projectSandboxName}`);
                const data = await response.json();
                
                this.allScreens = data.screens || [];
                
                // 도메인별로 그룹화
                const domains = [...new Set(this.allScreens.map(screen => screen.domain))];
                this.domains = domains.map(domain => ({
                    folder: domain,
                    title: this.formatDomainTitle(domain)
                }));
                
                // 현재 선택된 도메인이 있으면 화면 필터링
                if (this.selectedDomain) {
                    this.loadScreens();
                }
            } catch (error) {
                console.error('Failed to load project sandbox domains:', error);
                this.error = 'Failed to load project sandbox information';
            } finally {
                this.loading = false;
            }
        },

        // 도메인 선택 표시 여부 결정
        shouldShowDomainSelection() {
            if (this.sandboxMode === 'project') {
                return this.projectSandboxName && this.domains.length > 0;
            } else {
                return this.selectedSandbox && this.domains.length > 0;
            }
        },

        // 도메인명 포맷팅
        formatDomainTitle(domain) {
            return domain.replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        // 스크린 미리보기 함수
        previewScreen(screenId) {
            const screen = this.allScreens.find(s => s.id == screenId);
            const sandbox = this.sandboxMode === 'project' ? this.projectSandboxName : this.selectedSandbox;
            
            if (screen && sandbox) {
                const fullPath = screen.full_path_name || `${screen.domain}/${screen.folder_name}`;
                const previewUrl = `/sandbox/${sandbox}/${fullPath}`;
                window.open(previewUrl, 'screen-preview', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            }
        }
    }
}
</script>
