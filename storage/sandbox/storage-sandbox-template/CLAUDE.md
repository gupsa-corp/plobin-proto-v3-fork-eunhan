# 샌드박스 템플릿 개발 가이드

## 공통 구조

### 디렉토리 구조
모든 도메인은 동일한 구조를 따름:
```
{숫자}-{도메인명}-common/
├── 000-common.php          # 도메인별 공통 설정 파일
├── 001-database/           # 데이터베이스 파일들
├── 002-migration/          # 마이그레이션 파일들
├── 005-backend-php/        # 백엔드 PHP 파일들  
├── 006-backend-config/     # 백엔드 설정 파일들
├── 007-backend-database/   # 백엔드 DB 관련
├── 008-backend-models/     # 백엔드 모델들
├── 009-backend-controllers/# 백엔드 컨트롤러들
├── 010-backend-api/        # 백엔드 API 파일들
├── 011-backend-functions/  # 백엔드 비즈니스 로직
├── 012-backend-services/   # 백엔드 서비스들
├── 013-backend-requests/   # 백엔드 요청 처리
└── 014-downloads/          # 다운로드 파일들
```

### 파일 명명 규칙
- **숫자 접두사 필수**: 모든 파일과 폴더는 `000-999` 형식
- **공통 파일**: `000-common.php` (각 도메인별)
- **설정 파일**: `001-api.php`, `002-common.php`, `003-config.php`
- **백엔드 구조**: `005-013` 번호 대역 사용
- **다운로드**: `014-downloads/` 고정

## 공통 파일 시스템

### 000-common.php 구조
각 도메인의 공통 파일은 동일한 함수 구조를 가짐:
- `get{Domain}CurrentScreenInfo()` - 화면 정보
- `get{Domain}UploadPaths()` - 업로드 경로
- `get{Domain}ApiUrl()` - API URL 생성
- `get{Domain}ScreenUrl()` - 스크린 URL 생성
- `get{Domain}LocalFilesList()` - 파일 목록
- `debug{Domain}CurrentLocation()` - 디버그 정보

### 사용법
```php
// 각 스크린에서 해당 도메인 공통 파일 include
require_once __DIR__ . '/../{숫자}-{도메인명}-common/000-common.php';
```

## 개발 규칙

### 절대 원칙
- **숫자 접두사 필수**: 모든 파일과 폴더
- **상대 경로 사용**: `__DIR__` 기준
- **도메인별 함수 사용**: 크로스 도메인 참조 금지
- **backend 폴더 참조 금지**: frontend 도메인 폴더만 사용

### 경로 참조
```php
// 올바른 예
require_once __DIR__ . '/../100-pms-common/000-common.php';
require_once __DIR__ . '/005-backend-php/file.php';

// 잘못된 예  
require_once '../backend/common.php';
require_once '/absolute/path/file.php';
```