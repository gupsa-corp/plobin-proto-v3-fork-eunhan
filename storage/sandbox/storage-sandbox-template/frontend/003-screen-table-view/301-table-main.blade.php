<!-- 메인 테이블 컨테이너 -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden" 
     x-data="{
         sidebarOpen: false,
         selectedProject: null,
         async openSidebar(project) {
             // 먼저 사이드바 열고 로딩 상태 표시
             this.selectedProject = project;
             this.sidebarOpen = true;
             
             try {
                 // API에서 최신 프로젝트 데이터 가져오기
                 const response = await fetch('/api/sandbox/storage-sandbox-template/backend/api.php/projects/' + project.id);
                 const result = await response.json();
                 
                 if (result.success && result.data) {
                     // 최신 데이터로 업데이트
                     this.selectedProject = result.data;
                 }
             } catch (error) {
                 console.error('프로젝트 데이터 로딩 실패:', error);
                 // 에러가 발생해도 기존 데이터로 사이드바는 열림
             }
         },
         closeSidebar() {
             this.selectedProject = null;
             this.sidebarOpen = false;
         },
         updateProject() {
             if (!this.selectedProject) {
                 return;
             }
             window.saveProject(this.selectedProject, this);
         }
     }" x-init="window.projectTable = this">
    
    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/302-table-header.blade.php'); ?>
    
    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/303-table-body.blade.php'); ?>
    
    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/304-table-pagination.blade.php'); ?>
    
    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/305-sidebar-project-edit.blade.php'); ?>
</div>

<?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/306-table-scripts.blade.php'); ?>