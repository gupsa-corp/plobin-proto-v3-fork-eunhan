# 샌드박스 프로젝트 관리 시스템

이 시스템은 **실제 SQLite 데이터베이스 연동**을 통한 프로젝트 관리 및 간트차트 기능을 제공합니다.

> 🎯 **핵심 목표**: 샌드박스에서 실제 동작하는 데이터베이스 연동 기능 구현 및 학습

## 📁 디렉토리 구조

```
storage/sandbox/storage-sandbox-template/
├── common.php                         # 공통 설정 및 경로 관리
├── debug-info.php                     # 디버그 정보 확인 페이지
├── frontend/                          # 프론트엔드 템플릿
│   ├── 001-screen-dashboard/              # 대시보드
│   ├── 003-screen-table-view/             # 📊 프로젝트 테이블 뷰 (메인 기능)
│   ├── 007-screen-multi-file-upload/     # 다중 파일 업로드 화면
│   ├── 008-screen-uploaded-files-list/   # 업로드 파일 리스트 화면
│   └── [기타 화면들]/                    # 다양한 UI 템플릿
├── backend/                           # 백엔드 로직
│   ├── SandboxHelper.php              # 백엔드 헬퍼 클래스
│   ├── database/                      # 🗃️ SQLite 데이터베이스 및 마이그레이션
│   │   ├── release.sqlite             # SQLite 데이터베이스 파일
│   │   └── migration/                 # 마이그레이션 SQL 파일들
│   │       ├── 001_create_projects_table.sql      # 기본 프로젝트 테이블
│   │       ├── 002_add_gantt_columns.sql          # 간트차트 컬럼 추가
│   │       ├── 003_create_dynamic_columns_system.sql  # 동적 컬럼 시스템
│   │       └── 004_seed_sample_projects.sql       # 샘플 데이터
│   ├── controllers/                   # 컨트롤러
│   ├── services/                      # 서비스 클래스
│   ├── requests/                      # 요청 검증 클래스
│   ├── routes/                        # API 라우트
│   └── config/                        # 설정 파일
├── uploads/                          # 업로드된 파일 저장 디렉토리 (자동 생성)
├── temp/                             # 임시 파일 디렉토리 (자동 생성)
└── downloads/                        # 다운로드 파일 저장 디렉토리 (자동 생성)
```

## 🔧 경로 관리 시스템

### common.php
모든 프론트엔드 화면에서 현재 위치와 경로 정보를 제공합니다:

```php
<?php 
    require_once __DIR__ . '/../../common.php';
    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();
?>
```

### 주요 함수들
- `getCurrentScreenInfo()`: 현재 화면 정보 반환
- `getUploadPaths()`: 업로드 관련 경로 정보 반환  
- `getScreenUrl($type, $name)`: 다른 화면으로의 URL 생성
- `getApiUrl($endpoint)`: API 엔드포인트 URL 생성

### SandboxHelper.php
백엔드에서 샌드박스 경로 정보를 관리합니다:

```php
// 경로 정보
$paths = SandboxHelper::getUploadPaths();

// URL 생성
$screenUrl = SandboxHelper::getScreenUrl('frontend', '007-screen-multi-file-upload');
$apiUrl = SandboxHelper::getApiUrl('file-upload');
```

## 🚀 설치 및 설정

### 1. SQLite 데이터베이스 마이그레이션 실행

```bash
# 샌드박스 디렉토리로 이동
cd storage/sandbox/storage-sandbox-template/backend

# 마이그레이션 파일들을 순서대로 실행
sqlite3 database/release.sqlite < migration/001_create_projects_table.sql
sqlite3 database/release.sqlite < migration/002_add_gantt_columns.sql
sqlite3 database/release.sqlite < migration/003_create_dynamic_columns_system.sql
sqlite3 database/release.sqlite < migration/004_seed_sample_projects.sql
```

또는 한 번에 실행:
```bash
for file in migration/*.sql; do sqlite3 database/release.sqlite < "$file"; done
```

### 2. 데이터베이스 확인

```bash
# 테이블 확인
sqlite3 database/release.sqlite ".tables"

# 프로젝트 데이터 확인
sqlite3 database/release.sqlite "SELECT COUNT(*) FROM projects;"
```

### 3. 샌드박스 화면 접근

**🎯 메인 기능**: 프로젝트 테이블 뷰 (실제 데이터베이스 연동)
- **URL**: `/sandbox/storage-sandbox-template/frontend/003-screen-table-view/`
- **기능**: 검색, 필터링, 정렬, 페이지네이션이 모두 실제 데이터베이스와 연동

기타 화면들:
- 디버그 정보: `/sandbox/storage-sandbox-template/debug-info.php`
- 대시보드: `/sandbox/storage-sandbox-template/frontend/001-screen-dashboard/`
- 파일 업로드: `/sandbox/storage-sandbox-template/frontend/007-screen-multi-file-upload/`
- 파일 목록: `/sandbox/storage-sandbox-template/frontend/008-screen-uploaded-files-list/`

> **참고**: 실제 URL은 서버 설정에 따라 다를 수 있습니다. `debug-info.php`에서 정확한 URL을 확인하세요.

## 🗃️ 데이터베이스 구조

### 핵심 테이블들

#### 1. projects (메인 프로젝트 테이블)
```sql
CREATE TABLE projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,                    -- 프로젝트명
    description TEXT,                      -- 설명
    status TEXT DEFAULT 'planned',         -- 상태: planned, in_progress, completed, on_hold
    progress INTEGER DEFAULT 0,            -- 진행률 (0-100)
    team_members INTEGER DEFAULT 1,        -- 팀 멤버 수
    priority TEXT DEFAULT 'medium',        -- 우선순위: high, medium, low
    
    -- 간트차트용 컬럼
    start_date DATE,                       -- 시작일
    end_date DATE,                         -- 종료일
    estimated_hours INTEGER DEFAULT 0,     -- 예상 시간
    actual_hours INTEGER DEFAULT 0,        -- 실제 시간
    parent_id INTEGER,                     -- 부모 프로젝트 ID
    dependencies TEXT DEFAULT '[]',        -- 의존성 (JSON 배열)
    
    -- 추가 메타데이터
    budget DECIMAL(10,2) DEFAULT 0,        -- 예산
    client TEXT,                           -- 클라이언트
    category TEXT,                         -- 카테고리
    tags TEXT DEFAULT '[]',                -- 태그 (JSON 배열)
    
    created_date DATE DEFAULT CURRENT_DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. project_columns (동적 컬럼 메타데이터)
```sql
CREATE TABLE project_columns (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    column_name TEXT NOT NULL UNIQUE,      -- 컬럼명
    column_type TEXT NOT NULL,             -- 타입: TEXT, INTEGER, DECIMAL, DATE, BOOLEAN, JSON
    column_label TEXT NOT NULL,            -- 표시명
    default_value TEXT,                    -- 기본값
    is_required BOOLEAN DEFAULT 0,         -- 필수 여부
    is_active BOOLEAN DEFAULT 1,           -- 활성화 상태
    sort_order INTEGER DEFAULT 0,          -- 정렬 순서
    display_type TEXT DEFAULT 'input',     -- 입력 타입
    options TEXT DEFAULT '[]'              -- 선택 옵션들 (JSON)
);
```

#### 3. project_custom_data (동적 데이터 저장)
```sql
CREATE TABLE project_custom_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    column_name TEXT NOT NULL,
    column_value TEXT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

### 샘플 데이터
- 20개의 샘플 프로젝트 (다양한 상태, 우선순위, 카테고리)
- 부모-자식 관계 예시 (웹사이트 리뉴얼 > UI/UX 디자인, 프론트엔드 개발 등)
- 의존성 관계 예시

## 🎨 핵심 기능 - 프로젝트 테이블 뷰

### 📊 프로젝트 테이블 뷰 (003-screen-table-view) - 메인 기능

**🎯 핵심**: 실제 SQLite 데이터베이스와 완전 연동된 프로젝트 관리 시스템

#### 주요 기능
- **실시간 통계 카드**: 전체 프로젝트 수, 진행 중 프로젝트, 완료된 프로젝트, 평균 진행률
- **고급 검색 및 필터링**: 프로젝트명, 클라이언트명 검색 + 상태별, 우선순위별 필터
- **정렬 기능**: 프로젝트명, 진행률, 우선순위별 오름차순/내림차순 정렬
- **페이지네이션**: 실제 데이터 개수 기반 페이지 분할 (페이지당 10개)
- **상세 정보 표시**: 프로젝트 설명, 카테고리, 예산, 시작일-종료일, 클라이언트 정보

#### 데이터베이스 연동 기능
- **검색**: LIKE 쿼리로 프로젝트명, 설명, 클라이언트명 검색
- **필터링**: status, priority 컬럼 기반 WHERE 조건
- **정렬**: 사용자 선택에 따른 ORDER BY 동적 적용
- **페이지네이션**: LIMIT, OFFSET을 사용한 실제 페이징
- **보안**: Prepared Statements로 SQL 인젝션 방지

#### UI/UX 특징
- **반응형 디자인**: 모바일, 태블릿, 데스크톱 대응
- **상태별 색상 코딩**: 계획(보라), 진행중(파랑), 완료(녹색), 보류(노랑)
- **진행률 시각화**: 프로그레스 바로 진행 상황 표시
- **간트차트 연동 준비**: 간트차트 뷰로 전환 버튼 제공

### 기타 샌드박스 화면들

#### 파일 업로드 시스템 (007, 008)
- **다중 파일 업로드**: 드래그 앤 드롭, 실시간 진행률 표시
- **파일 목록 관리**: 검색, 필터링, 다운로드, 삭제 기능

## 🛠️ 개발 및 확장 가이드

### 새로운 컬럼 추가하는 방법 (라라벨 스타일 마이그레이션)

#### 1. 마이그레이션 파일 생성
```bash
# 예시: 예산 컬럼 추가
# 파일명: 005_add_budget_column.sql
```

```sql
-- 실제 테이블에 컬럼 추가
ALTER TABLE projects ADD COLUMN budget DECIMAL(10,2) DEFAULT 0;

-- 동적 컬럼 시스템에 메타데이터 추가
INSERT INTO project_columns (column_name, column_type, column_label, default_value) 
VALUES ('budget', 'DECIMAL', '예산', '0');

SELECT '예산 컬럼이 성공적으로 추가되었습니다.' as message;
```

#### 2. 마이그레이션 실행
```bash
sqlite3 backend/database/release.sqlite < backend/migration/005_add_budget_column.sql
```

### 간트차트 구현을 위한 확장 포인트

#### 데이터 준비 완료
- `start_date`, `end_date`: 프로젝트 시작/종료일
- `parent_id`: 부모-자식 프로젝트 관계
- `dependencies`: 프로젝트 간 의존성 (JSON 배열)
- `estimated_hours`, `actual_hours`: 시간 추적

#### 프론트엔드 구현 방향
- JavaScript 간트차트 라이브러리 활용 (Frappe Gantt, DHTMLX Gantt 등)
- PHP에서 간트차트용 JSON 데이터 생성
- 드래그&드롭으로 일정 조정 기능

## 🛡️ 보안 기능

### 프로젝트 관리 시스템 보안
- **SQL 인젝션 방지**: Prepared Statements 사용
- **XSS 방지**: htmlspecialchars()로 출력 이스케이프
- **입력 검증**: 정렬 컬럼, 페이지 번호 등 화이트리스트 검증
- **에러 처리**: 데이터베이스 연결 오류 시 안전한 폴백

### 파일 업로드 보안
- 파일 형식 검증, 크기 제한, 경로 트래버설 방지, CSRF 토큰 검증

## 🎯 학습 및 실무 응용 포인트

### 샌드박스 기능 구현의 핵심 가치

#### 1. **실제 데이터베이스 연동 학습**
- SQLite PDO 연결 및 쿼리 실행
- 동적 WHERE, ORDER BY 구문 생성
- 페이지네이션 구현 (LIMIT, OFFSET)
- 트랜잭션 및 에러 처리

#### 2. **사용자 경험 중심 개발**
- 실시간 검색 및 필터링
- 직관적인 정렬 UI (클릭으로 오름/내림차순 변경)
- 상태별 색상 코딩으로 가독성 향상
- 반응형 디자인으로 다양한 디바이스 대응

#### 3. **확장 가능한 시스템 설계**
- 동적 컬럼 추가 시스템 (EAV 패턴)
- 라라벨 스타일 마이그레이션 시스템
- 간트차트 확장을 위한 데이터 구조 설계

#### 4. **실무 활용 가능한 패턴**
- 마이그레이션 기반 데이터베이스 관리
- 검색/필터/정렬이 통합된 테이블 UI
- 프로젝트 관리에 필요한 모든 필드 구조

## 🚀 확장 기능 로드맵

### Phase 2: 간트차트 뷰 구현
- JavaScript 간트차트 라이브러리 통합
- 프로젝트 의존성 시각화
- 드래그&드롭으로 일정 조정
- 크리티컬 패스 계산

### Phase 3: 동적 컬럼 관리 UI
- 런타임 컬럼 추가/제거 인터페이스
- 컬럼 타입별 입력 위젯 (텍스트, 선택, 날짜 등)
- 컬럼 순서 변경 및 표시/숨김 기능

### Phase 4: 고급 프로젝트 관리
- 프로젝트 템플릿 시스템
- 리소스 할당 및 관리
- 시간 추적 및 보고서
- 알림 및 마일스톤 관리

### Phase 5: 협업 기능
- 댓글 및 피드백 시스템
- 파일 첨부 및 버전 관리
- 팀 멤버별 권한 관리

## 🐛 문제 해결

### 데이터베이스 관련 문제

1. **"데이터베이스 연결 오류" 표시**
   - SQLite 파일 경로 확인: `storage/sandbox/storage-sandbox-template/backend/database/release.sqlite`
   - 파일 권한 확인: `chmod 664 release.sqlite`
   - 마이그레이션 실행 여부 확인

2. **프로젝트가 표시되지 않음**
   ```bash
   # 데이터 확인
   cd storage/sandbox/storage-sandbox-template/backend
   sqlite3 database/release.sqlite "SELECT COUNT(*) FROM projects;"
   
   # 시딩 데이터 재실행
   sqlite3 database/release.sqlite < migration/004_seed_sample_projects.sql
   ```

3. **검색/필터가 작동하지 않음**
   - URL 파라미터 확인: `?search=test&status=in_progress`
   - PHP 에러 로그 확인: 브라우저 개발자 도구 > Network 탭

### 권한 문제

```bash
# 샌드박스 디렉토리 권한 설정
chmod -R 755 storage/sandbox/storage-sandbox-template/
chmod 664 storage/sandbox/storage-sandbox-template/backend/database/release.sqlite
```

### 개발 디버깅

```bash
# 데이터베이스 스키마 확인
sqlite3 backend/database/release.sqlite ".schema projects"

# 샘플 데이터 확인
sqlite3 backend/database/release.sqlite "SELECT id, name, status FROM projects LIMIT 5;"
```

## 📝 개발 노트

### 기술적 구현 특징
- **SQLite + PDO**: 경량화된 데이터베이스 연동
- **Prepared Statements**: SQL 인젝션 방지
- **동적 쿼리 생성**: 검색, 필터링, 정렬의 유연한 조합
- **EAV 패턴**: 동적 컬럼 시스템으로 확장성 확보
- **반응형 디자인**: Tailwind CSS 활용

### 성능 최적화
- 인덱스 기반 쿼리 최적화 (name, status, priority, created_date)
- 페이지네이션으로 대량 데이터 처리
- 트리거를 활용한 자동 타임스탬프 관리

### 보안 고려사항
- 모든 사용자 입력 검증 및 이스케이프
- 화이트리스트 기반 정렬 컬럼 검증
- 데이터베이스 연결 오류 시 안전한 폴백

---

> 🎯 **핵심 메시지**: 이 샌드박스는 **실제 동작하는 데이터베이스 연동 시스템**을 통해 실무에서 사용할 수 있는 패턴과 기술을 학습할 수 있도록 설계되었습니다. 단순한 데모가 아닌, 확장 가능하고 실용적인 프로젝트 관리 시스템의 기반을 제공합니다.
