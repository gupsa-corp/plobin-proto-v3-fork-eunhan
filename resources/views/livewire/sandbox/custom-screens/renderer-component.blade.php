<div>
    <style>
        /* 렌더링된 콘텐츠를 위한 기본 스타일 */
        .rendered-content, .rendered-screen-content {
            /* Tailwind CSS 스타일이 적용되도록 */
        }
        
        .rendered-content h1, .rendered-screen-content h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1rem;
        }
        
        .rendered-content h2, .rendered-screen-content h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .rendered-content h3, .rendered-screen-content h3 {
            font-size: 1.125rem;
            font-weight: 500;
            color: #374151;
        }
        
        .rendered-content p, .rendered-screen-content p {
            color: #4b5563;
        }
        
        .rendered-content .space-y-4 > * + *, .rendered-screen-content .space-y-4 > * + * {
            margin-top: 1rem;
        }
        
        .rendered-content .border, .rendered-screen-content .border {
            border-color: #e5e7eb;
            border-width: 1px;
        }
        
        .rendered-content .rounded, .rendered-screen-content .rounded {
            border-radius: 0.5rem;
        }
        
        .rendered-content .p-4, .rendered-screen-content .p-4 {
            padding: 1rem;
        }
        
        .rendered-content .bg-white, .rendered-screen-content .bg-white {
            background-color: #ffffff;
        }
        
        .rendered-content .shadow, .rendered-screen-content .shadow {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        
        .rendered-content .text-gray-500, .rendered-screen-content .text-gray-500 {
            color: #6b7280;
        }
        
        .rendered-content .font-semibold, .rendered-screen-content .font-semibold {
            font-weight: 600;
        }
        
        .rendered-content .text-gray-600, .rendered-screen-content .text-gray-600 {
            color: #4b5563;
        }
    </style>

    <div class="renderer-content">
        @if($error)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="text-red-400 text-xl mr-3">⚠️</div>
                    <div>
                        <h4 class="font-medium text-red-800">렌더링 오류</h4>
                        <p class="text-red-600 text-sm mt-1">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @elseif($renderedContent)
            <!-- 렌더링된 콘텐츠 출력 -->
            <div class="rendered-content">
                {!! $renderedContent !!}
            </div>
            
            <!-- 디버그 정보 (개발 모드에서만) -->
            @if(config('app.debug'))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <details class="text-xs text-gray-500">
                        <summary class="cursor-pointer hover:text-gray-700">디버그 정보</summary>
                        <div class="mt-2 space-y-2">
                            <div>
                                <strong>화면 제목:</strong> {{ $screen['title'] ?? 'N/A' }}
                            </div>
                            <div>
                                <strong>화면 유형:</strong> {{ $screen['type'] ?? 'N/A' }}
                            </div>
                            @if(!empty($screen['connected_functions']))
                                @php $functions = json_decode($screen['connected_functions'], true); @endphp
                                <div>
                                    <strong>연결된 함수:</strong> 
                                    @foreach($functions as $func)
                                        <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs mr-1">
                                            {{ $func['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($screen['db_queries']))
                                @php $queries = json_decode($screen['db_queries'], true); @endphp
                                <div>
                                    <strong>DB 쿼리:</strong> 
                                    @foreach($queries as $query)
                                        <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1">
                                            {{ $query['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </details>
                </div>
            @endif
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <div class="text-gray-400 text-4xl mb-3">📱</div>
                <h4 class="font-medium text-gray-600">화면 데이터가 없습니다</h4>
                <p class="text-gray-500 text-sm mt-1">렌더링할 블레이드 템플릿을 확인해주세요.</p>
            </div>
        @endif
    </div>
</div>