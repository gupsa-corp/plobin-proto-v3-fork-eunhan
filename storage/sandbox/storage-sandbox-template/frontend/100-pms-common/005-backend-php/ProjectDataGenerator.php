<?php

class ProjectDataGenerator
{
    /**
     * 테이블 뷰용 프로젝트 데이터 생성
     */
    public static function generateTableData()
    {
        $statuses = [
            'planning' => ['label' => '기획 중', 'color' => 'bg-yellow-100 text-yellow-800'],
            'in_progress' => ['label' => '진행 중', 'color' => 'bg-blue-100 text-blue-800'],
            'testing' => ['label' => '테스트', 'color' => 'bg-purple-100 text-purple-800'],
            'completed' => ['label' => '완료', 'color' => 'bg-green-100 text-green-800'],
            'on_hold' => ['label' => '보류', 'color' => 'bg-gray-100 text-gray-800']
        ];
        
        $priorities = [
            'urgent' => ['label' => '긴급', 'color' => 'text-red-600', 'icon' => '🔴'],
            'high' => ['label' => '높음', 'color' => 'text-orange-600', 'icon' => '🟠'],
            'medium' => ['label' => '보통', 'color' => 'text-blue-600', 'icon' => '🔵'],
            'low' => ['label' => '낮음', 'color' => 'text-gray-600', 'icon' => '⚪']
        ];
        
        $assignees = ['김개발', '이디자인', '박기획', '최테스터', '정PM'];
        
        $projects = [
            ['name' => '사용자 인증 시스템 개발', 'description' => 'JWT 기반 인증 시스템 구축'],
            ['name' => '대시보드 UI 리뉴얼', 'description' => '모던한 대시보드 인터페이스 디자인'],
            ['name' => 'API 성능 최적화', 'description' => '데이터베이스 쿼리 및 API 응답 속도 개선'],
            ['name' => '모바일 앱 버그 수정', 'description' => '크리티컬 버그 20건 수정'],
            ['name' => '데이터 백업 시스템', 'description' => '자동 백업 및 복원 시스템 구축'],
            ['name' => '사용자 피드백 시스템', 'description' => '고객 의견 수집 및 분석 도구'],
            ['name' => '보안 감사 및 개선', 'description' => '전체 시스템 보안 점검 및 강화'],
            ['name' => '성능 모니터링 도구', 'description' => '실시간 시스템 모니터링 구축']
        ];
        
        $tableData = [];
        foreach ($projects as $index => $project) {
            $statusKeys = array_keys($statuses);
            $priorityKeys = array_keys($priorities);
            
            $startDate = date('Y-m-d', strtotime('-' . rand(30, 90) . ' days'));
            $endDate = date('Y-m-d', strtotime($startDate . ' +' . rand(30, 120) . ' days'));
            
            $tableData[] = [
                'id' => 'PROJ-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'name' => $project['name'],
                'description' => $project['description'],
                'status' => $statusKeys[array_rand($statusKeys)],
                'priority' => $priorityKeys[array_rand($priorityKeys)],
                'assignee' => $assignees[array_rand($assignees)],
                'progress' => rand(0, 100),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'budget' => rand(100, 1000) . '만원'
            ];
        }
        
        return [$tableData, $statuses, $priorities];
    }

    /**
     * 칸반 보드용 작업 데이터 생성
     */
    public static function generateKanbanData()
    {
        $columns = [
            'backlog' => [
                'title' => '백로그',
                'color' => 'bg-gray-200',
                'icon' => '📋'
            ],
            'todo' => [
                'title' => '할 일',
                'color' => 'bg-red-100',
                'icon' => '📝'
            ],
            'in_progress' => [
                'title' => '진행 중',
                'color' => 'bg-blue-100',
                'icon' => '🔄'
            ],
            'review' => [
                'title' => '검토 중',
                'color' => 'bg-yellow-100',
                'icon' => '👁️'
            ],
            'testing' => [
                'title' => '테스트',
                'color' => 'bg-purple-100',
                'icon' => '🧪'
            ],
            'done' => [
                'title' => '완료',
                'color' => 'bg-green-100',
                'icon' => '✅'
            ]
        ];

        $priorities = [
            'urgent' => ['label' => '긴급', 'color' => 'bg-red-500', 'text' => 'text-white'],
            'high' => ['label' => '높음', 'color' => 'bg-orange-500', 'text' => 'text-white'],
            'medium' => ['label' => '보통', 'color' => 'bg-blue-500', 'text' => 'text-white'],
            'low' => ['label' => '낮음', 'color' => 'bg-gray-500', 'text' => 'text-white']
        ];

        $assignees = [
            ['name' => '김개발', 'avatar' => 'bg-blue-500'],
            ['name' => '이디자인', 'avatar' => 'bg-green-500'],
            ['name' => '박기획', 'avatar' => 'bg-purple-500'],
            ['name' => '최테스터', 'avatar' => 'bg-red-500'],
            ['name' => '정PM', 'avatar' => 'bg-yellow-500']
        ];

        $today = date('Y-m-d');

        $tasks = [
            // 백로그
            [
                'id' => 'TASK-001',
                'title' => '사용자 프로필 페이지 개선',
                'description' => '사용자 경험 향상을 위한 프로필 페이지 리뉴얼',
                'status' => 'backlog',
                'priority' => 'medium',
                'assignee' => $assignees[0],
                'due_date' => date('Y-m-d', strtotime($today . ' +15 days')),
                'tags' => ['UI/UX', '프론트엔드'],
                'comments' => 3
            ],
            [
                'id' => 'TASK-002',
                'title' => '데이터베이스 백업 자동화',
                'description' => '주기적 백업 스케줄링 시스템 구축',
                'status' => 'backlog',
                'priority' => 'low',
                'assignee' => $assignees[3],
                'due_date' => date('Y-m-d', strtotime($today . ' +20 days')),
                'tags' => ['백엔드', '인프라'],
                'comments' => 1
            ],

            // 할 일
            [
                'id' => 'TASK-003',
                'title' => '모바일 반응형 디자인',
                'description' => '모든 페이지의 모바일 최적화 작업',
                'status' => 'todo',
                'priority' => 'high',
                'assignee' => $assignees[1],
                'due_date' => date('Y-m-d', strtotime($today . ' +5 days')),
                'tags' => ['모바일', 'CSS'],
                'comments' => 5
            ],
            [
                'id' => 'TASK-004',
                'title' => 'API 문서화',
                'description' => 'Swagger를 이용한 API 문서 자동 생성',
                'status' => 'todo',
                'priority' => 'medium',
                'assignee' => $assignees[0],
                'due_date' => date('Y-m-d', strtotime($today . ' +10 days')),
                'tags' => ['문서', 'API'],
                'comments' => 2
            ],

            // 진행 중
            [
                'id' => 'TASK-005',
                'title' => '사용자 인증 시스템',
                'description' => 'JWT 기반 로그인/회원가입 구현',
                'status' => 'in_progress',
                'priority' => 'urgent',
                'assignee' => $assignees[0],
                'due_date' => $today,
                'tags' => ['보안', '백엔드'],
                'comments' => 8
            ],
            [
                'id' => 'TASK-006',
                'title' => '대시보드 차트 구현',
                'description' => '실시간 데이터 시각화 컴포넌트',
                'status' => 'in_progress',
                'priority' => 'high',
                'assignee' => $assignees[1],
                'due_date' => date('Y-m-d', strtotime($today . ' +2 days')),
                'tags' => ['차트', '프론트엔드'],
                'comments' => 4
            ],

            // 검토 중
            [
                'id' => 'TASK-007',
                'title' => '이메일 알림 시스템',
                'description' => '사용자 액션에 따른 이메일 발송',
                'status' => 'review',
                'priority' => 'medium',
                'assignee' => $assignees[2],
                'due_date' => date('Y-m-d', strtotime($today . ' +2 days')),
                'tags' => ['이메일', '알림'],
                'comments' => 6
            ],

            // 테스트
            [
                'id' => 'TASK-008',
                'title' => '결제 시스템 통합',
                'description' => '외부 결제 API 연동 및 테스트',
                'status' => 'testing',
                'priority' => 'urgent',
                'assignee' => $assignees[3],
                'due_date' => date('Y-m-d', strtotime($today . ' -4 days')),
                'tags' => ['결제', 'API'],
                'comments' => 12
            ],
            [
                'id' => 'TASK-009',
                'title' => '성능 최적화',
                'description' => '페이지 로딩 속도 개선',
                'status' => 'testing',
                'priority' => 'high',
                'assignee' => $assignees[0],
                'due_date' => date('Y-m-d', strtotime($today . ' -1 days')),
                'tags' => ['성능', '최적화'],
                'comments' => 3
            ],

            // 완료
            [
                'id' => 'TASK-010',
                'title' => '로고 및 브랜딩',
                'description' => '새로운 회사 로고 및 브랜드 가이드',
                'status' => 'done',
                'priority' => 'medium',
                'assignee' => $assignees[1],
                'due_date' => date('Y-m-d', strtotime($today . ' -10 days')),
                'tags' => ['디자인', '브랜딩'],
                'comments' => 7
            ],
            [
                'id' => 'TASK-011',
                'title' => '프로젝트 초기 설정',
                'description' => '개발 환경 및 CI/CD 파이프라인 구축',
                'status' => 'done',
                'priority' => 'high',
                'assignee' => $assignees[4],
                'due_date' => date('Y-m-d', strtotime($today . ' -15 days')),
                'tags' => ['인프라', 'DevOps'],
                'comments' => 9
            ]
        ];

        return [$columns, $tasks, $priorities, $assignees];
    }

    /**
     * 간트 차트용 프로젝트 데이터 생성
     */
    public static function generateGanttData()
    {
        $today = date('Y-m-d');
        
        // 날짜 계산 함수
        $getDateString = function($daysOffset) use ($today) {
            return date('Y-m-d', strtotime($today . ' ' . ($daysOffset >= 0 ? '+' : '') . $daysOffset . ' days'));
        };
        
        // 간트 차트용 작업 데이터
        $tasks = [
            [
                'id' => 'PROJ-1',
                'title' => '프로젝트 초기 설정 및 요구사항 분석',
                'assignee' => '김프로',
                'status' => 'completed',
                'priority' => 'high',
                'start_date' => $getDateString(-14),
                'end_date' => $getDateString(-10),
                'progress' => 100,
                'estimated_hours' => 40,
                'actual_hours' => 38,
                'description' => '프로젝트 범위 정의 및 초기 요구사항 수집'
            ],
            [
                'id' => 'PROJ-2',
                'title' => 'UI/UX 디자인 시스템 구축',
                'assignee' => '박디자인',
                'status' => 'completed',
                'priority' => 'high',
                'start_date' => $getDateString(-12),
                'end_date' => $getDateString(-6),
                'progress' => 100,
                'estimated_hours' => 60,
                'actual_hours' => 65,
                'description' => 'Design System, 와이어프레임, 프로토타입 제작',
                'dependencies' => ['PROJ-1']
            ],
            [
                'id' => 'PROJ-3',
                'title' => '데이터베이스 스키마 설계',
                'assignee' => '이백엔드',
                'status' => 'completed',
                'priority' => 'high',
                'start_date' => $getDateString(-10),
                'end_date' => $getDateString(-4),
                'progress' => 100,
                'estimated_hours' => 32,
                'actual_hours' => 28,
                'description' => 'ERD 설계 및 데이터베이스 구조 최적화',
                'dependencies' => ['PROJ-1']
            ],
            [
                'id' => 'PROJ-4',
                'title' => '백엔드 API 개발',
                'assignee' => '이백엔드',
                'status' => 'in_progress',
                'priority' => 'high',
                'start_date' => $getDateString(-4),
                'end_date' => $getDateString(8),
                'progress' => 65,
                'estimated_hours' => 80,
                'actual_hours' => 45,
                'description' => 'RESTful API 개발 및 비즈니스 로직 구현',
                'dependencies' => ['PROJ-3']
            ],
            [
                'id' => 'PROJ-5',
                'title' => '프론트엔드 컴포넌트 개발',
                'assignee' => '최프론트',
                'status' => 'in_progress',
                'priority' => 'medium',
                'start_date' => $getDateString(-6),
                'end_date' => $getDateString(6),
                'progress' => 45,
                'estimated_hours' => 70,
                'actual_hours' => 30,
                'description' => 'React 컴포넌트 및 페이지 구현',
                'dependencies' => ['PROJ-2']
            ],
            [
                'id' => 'PROJ-6',
                'title' => '사용자 인증 시스템',
                'assignee' => '정보안',
                'status' => 'in_progress',
                'priority' => 'urgent',
                'start_date' => $getDateString(-2),
                'end_date' => $getDateString(4),
                'progress' => 80,
                'estimated_hours' => 24,
                'actual_hours' => 18,
                'description' => '로그인, 회원가입, 권한 관리 시스템',
                'dependencies' => ['PROJ-4']
            ],
            [
                'id' => 'PROJ-7',
                'title' => '통합 테스트 및 QA',
                'assignee' => '한테스터',
                'status' => 'todo',
                'priority' => 'medium',
                'start_date' => $getDateString(6),
                'end_date' => $getDateString(12),
                'progress' => 0,
                'estimated_hours' => 40,
                'actual_hours' => 0,
                'description' => '전체 시스템 통합 테스트 및 버그 수정',
                'dependencies' => ['PROJ-4', 'PROJ-5']
            ],
            [
                'id' => 'PROJ-8',
                'title' => '배포 및 모니터링 설정',
                'assignee' => '윤데브옵스',
                'status' => 'todo',
                'priority' => 'low',
                'start_date' => $getDateString(10),
                'end_date' => $getDateString(14),
                'progress' => 0,
                'estimated_hours' => 16,
                'actual_hours' => 0,
                'description' => '프로덕션 배포 및 모니터링 시스템 구축',
                'dependencies' => ['PROJ-7']
            ]
        ];
        
        // 시간대 계산
        $allDates = [];
        foreach ($tasks as $task) {
            $allDates[] = $task['start_date'];
            $allDates[] = $task['end_date'];
        }
        
        $minDate = date('Y-m-d', strtotime(min($allDates) . ' -3 days'));
        $maxDate = date('Y-m-d', strtotime(max($allDates) . ' +7 days'));
        
        // 날짜 배열 생성
        $dateColumns = [];
        $currentDate = $minDate;
        while ($currentDate <= $maxDate) {
            $dayOfWeek = date('w', strtotime($currentDate));
            $dateColumns[] = [
                'date' => $currentDate,
                'day' => date('j', strtotime($currentDate)),
                'dayOfWeek' => ['일', '월', '화', '수', '목', '금', '토'][$dayOfWeek],
                'isWeekend' => in_array($dayOfWeek, [0, 6]),
                'isToday' => $currentDate === $today
            ];
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        
        return [$tasks, $dateColumns];
    }

    /**
     * 캘린더용 이벤트 데이터 생성
     */
    public static function generateCalendarData()
    {
        $currentDate = date('Y-m-01'); // 현재 월의 1일
        $today = date('Y-m-d');
        
        // 이벤트 데이터 생성
        $events = [
            [
                'id' => 'EVT-001',
                'title' => '프로젝트 킥오프 미팅',
                'date' => date('Y-m-d', strtotime($today . ' -3 days')),
                'time' => '10:00',
                'duration' => '2시간',
                'type' => 'meeting',
                'priority' => 'high',
                'attendees' => ['김PM', '이개발', '박디자인'],
                'description' => '프로젝트 전체 개요 및 역할 분담 논의'
            ],
            [
                'id' => 'EVT-002',
                'title' => 'UI/UX 리뷰',
                'date' => date('Y-m-d', strtotime($today . ' -1 days')),
                'time' => '14:00',
                'duration' => '1.5시간',
                'type' => 'review',
                'priority' => 'medium',
                'attendees' => ['박디자인', '최기획'],
                'description' => '와이어프레임 및 프로토타입 검토'
            ],
            [
                'id' => 'EVT-003',
                'title' => '스프린트 플래닝',
                'date' => $today,
                'time' => '09:00',
                'duration' => '3시간',
                'type' => 'planning',
                'priority' => 'high',
                'attendees' => ['김PM', '이개발', '최테스터'],
                'description' => '2주 스프린트 백로그 및 태스크 계획'
            ],
            [
                'id' => 'EVT-004',
                'title' => '데일리 스탠드업',
                'date' => date('Y-m-d', strtotime($today . ' +1 days')),
                'time' => '09:30',
                'duration' => '30분',
                'type' => 'standup',
                'priority' => 'low',
                'attendees' => ['전체팀'],
                'description' => '일일 진행 상황 공유'
            ],
            [
                'id' => 'EVT-005',
                'title' => 'API 개발 완료',
                'date' => date('Y-m-d', strtotime($today . ' +3 days')),
                'time' => null,
                'duration' => null,
                'type' => 'milestone',
                'priority' => 'urgent',
                'attendees' => ['이백엔드'],
                'description' => '백엔드 API 개발 마일스톤'
            ],
            [
                'id' => 'EVT-006',
                'title' => '코드 리뷰',
                'date' => date('Y-m-d', strtotime($today . ' +5 days')),
                'time' => '15:00',
                'duration' => '2시간',
                'type' => 'review',
                'priority' => 'medium',
                'attendees' => ['이개발', '정시니어'],
                'description' => '주간 코드 품질 검토 및 피드백'
            ],
            [
                'id' => 'EVT-007',
                'title' => '스프린트 회고',
                'date' => date('Y-m-d', strtotime($today . ' +7 days')),
                'time' => '16:00',
                'duration' => '1시간',
                'type' => 'retrospective',
                'priority' => 'medium',
                'attendees' => ['전체팀'],
                'description' => '스프린트 성과 분석 및 개선점 도출'
            ],
            [
                'id' => 'EVT-008',
                'title' => '고객 데모',
                'date' => date('Y-m-d', strtotime($today . ' +10 days')),
                'time' => '11:00',
                'duration' => '1시간',
                'type' => 'demo',
                'priority' => 'urgent',
                'attendees' => ['김PM', '고객사'],
                'description' => '개발 진행 상황 데모 및 피드백 수집'
            ],
            [
                'id' => 'EVT-009',
                'title' => 'QA 테스트 시작',
                'date' => date('Y-m-d', strtotime($today . ' +12 days')),
                'time' => null,
                'duration' => null,
                'type' => 'milestone',
                'priority' => 'high',
                'attendees' => ['최테스터'],
                'description' => '통합 테스트 및 품질 보증 시작'
            ],
            [
                'id' => 'EVT-010',
                'title' => '배포 준비',
                'date' => date('Y-m-d', strtotime($today . ' +14 days')),
                'time' => '10:00',
                'duration' => '4시간',
                'type' => 'deployment',
                'priority' => 'urgent',
                'attendees' => ['윤데브옵스', '이백엔드'],
                'description' => '프로덕션 배포 준비 및 인프라 점검'
            ]
        ];
        
        return [$currentDate, $today, $events];
    }

    /**
     * 상태 정보 가져오기
     */
    public static function getStatusInfo($status)
    {
        $statusMap = [
            'todo' => ['label' => '대기', 'color' => 'bg-gray-400', 'textColor' => 'text-gray-800'],
            'in_progress' => ['label' => '진행중', 'color' => 'bg-blue-500', 'textColor' => 'text-blue-800'],
            'completed' => ['label' => '완료', 'color' => 'bg-green-500', 'textColor' => 'text-green-800'],
            'blocked' => ['label' => '차단', 'color' => 'bg-red-500', 'textColor' => 'text-red-800']
        ];
        return $statusMap[$status] ?? $statusMap['todo'];
    }

    /**
     * 우선순위 정보 가져오기
     */
    public static function getPriorityInfo($priority)
    {
        $priorityMap = [
            'urgent' => ['label' => '긴급', 'color' => 'text-red-600', 'icon' => '🔴'],
            'high' => ['label' => '높음', 'color' => 'text-orange-600', 'icon' => '🟠'],
            'medium' => ['label' => '보통', 'color' => 'text-blue-600', 'icon' => '🔵'],
            'low' => ['label' => '낮음', 'color' => 'text-gray-600', 'icon' => '⚪']
        ];
        return $priorityMap[$priority] ?? $priorityMap['medium'];
    }

    /**
     * 이벤트 타입 정보 가져오기
     */
    public static function getEventTypeInfo($type)
    {
        $typeMap = [
            'meeting' => ['color' => 'bg-blue-500', 'icon' => '👥', 'label' => '회의'],
            'review' => ['color' => 'bg-purple-500', 'icon' => '👁️', 'label' => '검토'],
            'planning' => ['color' => 'bg-green-500', 'icon' => '📋', 'label' => '계획'],
            'standup' => ['color' => 'bg-yellow-500', 'icon' => '🏃', 'label' => '스탠드업'],
            'milestone' => ['color' => 'bg-red-500', 'icon' => '🏁', 'label' => '마일스톤'],
            'retrospective' => ['color' => 'bg-indigo-500', 'icon' => '🔄', 'label' => '회고'],
            'demo' => ['color' => 'bg-pink-500', 'icon' => '🎯', 'label' => '데모'],
            'deployment' => ['color' => 'bg-gray-500', 'icon' => '🚀', 'label' => '배포']
        ];
        return $typeMap[$type] ?? ['color' => 'bg-gray-500', 'icon' => '📅', 'label' => '기타'];
    }

    /**
     * 우선순위 컬러 가져오기
     */
    public static function getPriorityColor($priority)
    {
        $priorityMap = [
            'urgent' => 'border-l-4 border-red-600',
            'high' => 'border-l-4 border-orange-500',
            'medium' => 'border-l-4 border-blue-500',
            'low' => 'border-l-4 border-gray-400'
        ];
        return $priorityMap[$priority] ?? 'border-l-4 border-gray-400';
    }
}

?>