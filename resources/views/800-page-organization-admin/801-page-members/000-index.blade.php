<?php $common = getCommonPath(); ?>
<!DOCTYPE html>
@include('000-common-layouts.001-html-lang')
@include($common . '.301-layout-head', ['title' => '회원 관리'])
<body class="bg-gray-100">
    <div class="min-h-screen" style="position: relative;">
        @include('800-page-organization-admin.800-common.200-sidebar-main')
        <div class="main-content" style="margin-left: 240px; min-height: 100vh;">
            @include('800-page-organization-admin.800-common.100-header-main')

            @livewire('organization.admin.member-management', ['organizationId' => $id ?? 1])
        </div>
    </div>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Livewire loaded:', typeof window.Livewire !== 'undefined');
        });

        // Livewire 이벤트 리스너
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized');
        });

        // Livewire 3에서는 document로 이벤트를 리스닝합니다
        document.addEventListener('memberInvited', function(event) {
            alert('초대가 전송되었습니다: ' + event.detail.message);
        });

        document.addEventListener('error', function(event) {
            alert('오류: ' + event.detail.message);
        });

        document.addEventListener('memberRemoved', function(event) {
            alert(event.detail.message);
        });

        document.addEventListener('permissionChanged', function(event) {
            alert(event.detail.message);
        });

        document.addEventListener('invitationResent', function(event) {
            alert(event.detail.message);
        });

        document.addEventListener('console-log', function(event) {
            console.log('Livewire:', event.detail.message);
        });

        // 테스트용 함수
        window.testModal = function() {
            console.log('Testing modal...');
            if (window.Livewire) {
                // 모든 컴포넌트에서 MemberManagement 찾기
                const components = document.querySelectorAll('[wire\\:id]');
                for (let component of components) {
                    const hasButton = component.querySelector('button[wire\\:click="openInviteModal"]');
                    if (hasButton) {
                        const wireId = component.getAttribute('wire:id');
                        console.log('Found MemberManagement component:', wireId);
                        try {
                            window.Livewire.find(wireId)?.call('openInviteModal');
                        } catch (e) {
                            console.error('Failed to call openInviteModal:', e);
                        }
                        break;
                    }
                }
            }
        };
    </script>
</body>
</html>
