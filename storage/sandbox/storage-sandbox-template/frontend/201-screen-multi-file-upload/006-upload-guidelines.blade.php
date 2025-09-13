{{-- 업로드 가이드라인 컴포넌트 --}}
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span class="mr-2">ℹ️</span>
            업로드 가이드라인
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 지원 형식 --}}
            <div>
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="mr-2">📄</span>
                    지원 형식
                </h4>
                <div class="space-y-2">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-700 mb-1">이미지</div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center"><span class="mr-2">🖼️</span>JPG, JPEG, PNG, GIF, WebP</li>
                            <li class="text-xs text-gray-500 ml-6">최적: 1920x1080px 이하</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-700 mb-1">문서</div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center"><span class="mr-2">📝</span>PDF, DOC, DOCX, TXT</li>
                            <li class="text-xs text-gray-500 ml-6">OCR 지원: PDF, 이미지</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-700 mb-1">스프레드시트</div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center"><span class="mr-2">📊</span>XLS, XLSX, CSV</li>
                            <li class="text-xs text-gray-500 ml-6">최대 100만 행까지</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-700 mb-1">압축파일</div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center"><span class="mr-2">📦</span>ZIP, RAR, 7Z</li>
                            <li class="text-xs text-gray-500 ml-6">자동 압축해제 가능</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="font-medium text-gray-700 mb-1">멀티미디어</div>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center"><span class="mr-2">🎥</span>MP4, AVI, MOV</li>
                            <li class="flex items-center"><span class="mr-2">🎵</span>MP3, WAV, FLAC</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- 제한사항 및 보안 --}}
            <div>
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <span class="mr-2">⚖️</span>
                    제한사항 및 보안
                </h4>
                <div class="space-y-2">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="font-medium text-red-800 mb-1">크기 제한</div>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li>• 최대 파일 크기: {{ $maxFileSize }}MB</li>
                            <li>• 한 번에 최대: {{ $maxFiles }}개 파일</li>
                            <li>• 총 업로드 크기: {{ $maxTotalSize }}MB</li>
                        </ul>
                    </div>
                    
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                        <div class="font-medium text-orange-800 mb-1">보안 검사</div>
                        <ul class="text-sm text-orange-700 space-y-1">
                            <li>• 악성코드 자동 검사</li>
                            <li>• 파일 확장자 검증</li>
                            <li>• MIME 타입 확인</li>
                            <li>• 안전하지 않은 파일 차단</li>
                        </ul>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="font-medium text-green-800 mb-1">저장 및 보관</div>
                        <ul class="text-sm text-green-700 space-y-1">
                            <li>• 암호화된 안전 저장소</li>
                            <li>• 자동 백업 생성</li>
                            <li>• 30일 임시 보관</li>
                            <li>• 개인정보 보호 준수</li>
                        </ul>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="font-medium text-blue-800 mb-1">업로드 팁</div>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• 파일명은 영문/숫자 권장</li>
                            <li>• 특수문자 사용 지양</li>
                            <li>• 안정적인 인터넷 연결 필요</li>
                            <li>• 대용량 파일은 압축 권장</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- 추가 도움말 --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="bg-gray-50 rounded-lg p-4">
                <h5 class="font-medium text-gray-800 mb-2 flex items-center">
                    <span class="mr-2">💡</span>
                    업로드 도움말
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <p class="font-medium text-gray-700 mb-1">문제 해결</p>
                        <ul class="space-y-1">
                            <li>• 업로드 실패 시 파일 크기 확인</li>
                            <li>• 브라우저 새로고침 후 재시도</li>
                            <li>• 인터넷 연결 상태 확인</li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700 mb-1">최적화 방법</p>
                        <ul class="space-y-1">
                            <li>• 이미지는 압축 후 업로드</li>
                            <li>• 여러 파일은 폴더로 압축</li>
                            <li>• 업로드 중 페이지 이동 금지</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- 고객 지원 --}}
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-500">
                문제가 지속될 경우 
                <a href="mailto:support@example.com" class="text-green-600 hover:text-green-700 underline">
                    고객 지원팀
                </a>으로 문의해 주세요.
            </p>
        </div>
    </div>
</div>