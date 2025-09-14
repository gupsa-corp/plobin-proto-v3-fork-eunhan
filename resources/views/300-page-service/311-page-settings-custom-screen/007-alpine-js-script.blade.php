<script>
function customScreenSettingsPage() {
    return {
        // 기본 상태
        selectedCustomScreen: '{{ $currentCustomScreenId ?? '' }}',
        loading: false,
        error: null,
        
        // 3단계 선택 데이터
        sandboxes: [],
        domains: [],
        allScreens: window.customScreensData || [],
        filteredScreens: [],
        
        // 현재 선택 상태
        selectedSandbox: window.currentSandboxName || '',
        selectedDomain: '',

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
            this.sandboxes = [
                {
                    name: 'storage-sandbox-template',
                    title: 'Storage Sandbox Template'
                }
                // 추후 다른 샌드박스 추가 가능
            ];
            
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

            try {
                const response = await fetch(`/api/sandbox/${this.selectedSandbox}/domains`);
                if (response.ok) {
                    this.domains = await response.json();
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

            this.filteredScreens = this.allScreens.filter(screen => 
                screen.domain_folder === this.selectedDomain
            );
        },

        // 현재 선택된 화면에서 초기 상태 설정
        initializeFromCurrentScreen() {
            const currentScreen = this.allScreens.find(s => s.id === this.selectedCustomScreen);
            if (currentScreen) {
                this.selectedDomain = currentScreen.domain_folder;
                this.loadScreens();
            }
        },

        // 현재 화면에 맞는 도메인 자동 선택
        autoSelectDomainFromCurrentScreen() {
            if (this.selectedCustomScreen) {
                const currentScreen = this.allScreens.find(s => s.id === this.selectedCustomScreen);
                if (currentScreen && this.domains.find(d => d.folder === currentScreen.domain_folder)) {
                    this.selectedDomain = currentScreen.domain_folder;
                    this.loadScreens();
                }
            }
        },

        // 스크린 미리보기 함수
        previewScreen(screenId) {
            const screen = this.allScreens.find(s => s.id == screenId);
            if (screen && this.selectedSandbox) {
                const fullPath = screen.full_path_name || `${screen.domain_folder}/${screen.folder_name}`;
                const previewUrl = `/sandbox/${this.selectedSandbox}/${fullPath}`;
                window.open(previewUrl, 'screen-preview', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            }
        }
    }
}
</script>
