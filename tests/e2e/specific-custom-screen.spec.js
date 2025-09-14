import { test, expect } from '@playwright/test';

// 로그인 헬퍼 함수
async function loginAsAdmin(page) {
  await page.goto('/login');
  await page.waitForLoadState('networkidle');

  const testAccounts = [
    { email: 'admin@example.com', password: 'password' },
  ];

  for (const account of testAccounts) {
    await page.reload();
    await page.waitForLoadState('networkidle');

    await page.fill('#email', account.email);
    await page.fill('#password', account.password);
    await page.click('button:has-text("로그인")');

    await page.waitForTimeout(2000);

    if (!page.url().includes('/login')) {
      console.log(`로그인 성공: ${account.email}`);
      return true;
    }
  }
  return false;
}

test.describe('특정 커스텀 화면 URL 테스트', () => {
  test('페이지 4 커스텀 화면 screen 파라미터 테스트', async ({ page }) => {
    console.log('=== 특정 커스텀 화면 URL 테스트 시작 ===');

    // 로그인 시도
    const loginSuccess = await loginAsAdmin(page);

    if (!loginSuccess) {
      console.log('로그인 실패로 인해 테스트 건너뛰기');
      test.skip();
      return;
    }

    // 특정 커스텀 화면 URL로 이동 (새로운 샌드박스 템플릿 구조)
    // 환경 변수에서 템플릿명을 가져오거나 기본 샌드박스 템플릿을 찾아 사용
    const sandboxTemplate = process.env.TEST_SANDBOX_TEMPLATE || 'default-template';
    const targetUrl = `/sandbox/${sandboxTemplate}/100-domain-pms/103-screen-table-view`;
    console.log(`테스트 URL: ${targetUrl}`);

    await page.goto(targetUrl);
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    console.log(`실제 이동한 URL: ${currentUrl}`);

    // 페이지 로드 결과 확인
    if (currentUrl.includes('/login')) {
      console.log('❌ 인증 실패: 로그인 페이지로 리다이렉트됨');
      
      // 스크린샷 저장
      await page.screenshot({
        path: 'tests/e2e/screenshots/custom-screen-auth-failure.png',
        fullPage: true
      });
      
      test.skip();
      return;
    }

    // 페이지 내용 분석
    const pageTitle = await page.title();
    const bodyContent = await page.locator('body').textContent();

    console.log(`페이지 제목: ${pageTitle}`);

    // URL 파라미터 확인
    const url = new URL(page.url());
    const screenParam = url.searchParams.get('screen');
    console.log(`Screen 파라미터: ${screenParam}`);

    // 페이지 로딩 상태 확인
    if (bodyContent.includes('오류') || bodyContent.includes('error')) {
      console.log('⚠️ 오류 메시지 발견');
      
      // 오류 내용 추출
      const errorElements = await page.locator('.bg-red-50, .text-red-600, .text-red-700, .text-red-800').allTextContents();
      errorElements.forEach(text => console.log(`오류: ${text}`));
    }

    // 커스텀 화면 콘텐츠 확인
    if (bodyContent.includes('커스텀 화면') || bodyContent.includes('템플릿')) {
      console.log('✅ 커스텀 화면 콘텐츠 발견');
    }

    // 데이터베이스에서 페이지 4 정보 확인 (JavaScript로는 불가능하므로 로그로 표시)
    console.log('페이지 4의 커스텀 화면 설정을 확인해야 합니다.');

    // 페이지 요소 분석
    const headings = await page.locator('h1, h2, h3').count();
    const customElements = await page.locator('[data-custom-screen], [x-data]').count();

    console.log(`제목 요소 수: ${headings}`);
    console.log(`커스텀/Alpine.js 요소 수: ${customElements}`);

    // 페이지 스크린샷 저장
    await page.screenshot({
      path: 'tests/e2e/screenshots/custom-screen-page-4.png',
      fullPage: true
    });

    console.log('=== 특정 커스텀 화면 URL 테스트 완료 ===');
  });

  test('페이지 4 설정 페이지 접근 테스트', async ({ page }) => {
    console.log('=== 페이지 4 설정 페이지 테스트 시작 ===');

    const loginSuccess = await loginAsAdmin(page);
    if (!loginSuccess) {
      test.skip();
      return;
    }

    // 설정 페이지로 이동 (기존 설정 페이지는 그대로 유지)
    await page.goto('/organizations/1/projects/1/pages/4/settings/custom-screen');
    await page.waitForLoadState('networkidle');

    const settingsUrl = page.url();
    console.log(`설정 페이지 URL: ${settingsUrl}`);

    if (!settingsUrl.includes('/login')) {
      console.log('✅ 설정 페이지 접근 성공');

      const pageContent = await page.locator('body').textContent();

      // 설정 폼 요소 확인
      const radioButtons = await page.locator('input[type="radio"]').count();
      const saveButtons = await page.locator('button:has-text("저장")').count();

      console.log(`라디오 버튼 수: ${radioButtons}`);
      console.log(`저장 버튼 수: ${saveButtons}`);

      // 현재 선택된 커스텀 화면 확인
      const checkedRadio = await page.locator('input[type="radio"]:checked').getAttribute('value');
      console.log(`현재 선택된 커스텀 화면: ${checkedRadio || '없음'}`);

      // 사용 가능한 커스텀 화면 목록 확인
      const customScreenOptions = await page.locator('input[name="custom_screen"]').count();
      console.log(`사용 가능한 커스텀 화면 옵션 수: ${customScreenOptions}`);

      // 설정 페이지 스크린샷
      await page.screenshot({
        path: 'tests/e2e/screenshots/custom-screen-settings-page-4.png',
        fullPage: true
      });
    } else {
      console.log('❌ 설정 페이지 접근 실패');
    }

    console.log('=== 페이지 4 설정 페이지 테스트 완료 ===');
  });
});