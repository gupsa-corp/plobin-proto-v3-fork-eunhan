# 프로젝트 구조 가이드

## 기본 경로 정보
- **샌드박스 컨테이너 PATH**: `sandbox/container/`
- **샌드박스 폴더명**: `storage-sandbox-template`
- **도메인 폴더 구조**: `1xx-domain-{도메인명}/1xx-screen-{화면명}`

## 도메인별 구조

### 100-domain-pms (PMS 도메인)
- 101-screen-dashboard
- 102-screen-project-list  
- 103-screen-table-view
- 104-screen-kanban-board
- 105-screen-gantt-chart
- 106-screen-calendar-view
- 107-screen-pms-summary-requests
- 108-screen-form-execution
- 109-screen-form-history

### 101-domain-rfx (RFX 도메인)
- 101-screen-multi-file-upload
- 102-screen-file-list
- 103-screen-uploaded-files-list
- 104-screen-analysis-requests
- 105-screen-document-analysis
- 900-downloads

## 공통 폴더 구조 (100-common)

각 도메인의 `100-common` 폴더는 다음과 같은 표준 구조를 따릅니다:

### 000-Config (설정 및 공통 연결 중심)
- `000-common.php` - 모든 연결의 중심점, 도메인별 공통 설정
- `002-common.php` - 추가 공통 설정 파일
- 기타 설정 파일들

### 100번대: Controller 계층
- **100-Controllers** - API 엔드포인트 및 컨트롤러 로직
  - 구조: `{도메인폴더명}/Controller.php` (예: DocumentAnalysis/Controller.php)  
  - 요청 검증: `{도메인폴더명}/Request.php` (예: DocumentAnalysis/Request.php)
- **102-Services** - 비즈니스 로직 서비스 클래스
  - 구조: `{도메인폴더명}/Service.php` (예: DocumentAnalysis/Service.php)
- **103-Routes** - 라우팅 설정 파일 (api.php 등)

### 200번대: Database 계층
- **200-Database**
  - `Models/` - 데이터 모델 클래스
  - `Database/` - 데이터베이스 설정 및 시드 파일
  - `Migration/` - 스키마 마이그레이션 파일

### 400번대: Storage 계층  
- **400-Storage**
  - `uploads/` - 업로드된 파일 저장소
  - `versions/` - 버전 관리 파일
  - `downloads/` - 다운로드 파일 저장소

## 폴더명 규칙
- 모든 폴더는 `{3자리숫자}-{설명}` 형식 사용
- 관련 기능들은 같은 숫자 그룹으로 묶음 (예: 100번대는 Controller 관련)
- Laravel 표준 구조를 기반으로 하되 숫자 프리픽스로 정렬 관리
