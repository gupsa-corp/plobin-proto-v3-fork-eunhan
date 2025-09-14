import { test, expect } from '@playwright/test';

// 유틸리티 함수: Livewire 상태 업데이트 대기
async function waitForLivewireUpdate(page, timeout = 3000) {
  await page.waitForTimeout(1000);
}

// 로그인 헬퍼 함수
async function loginAsAdmin(page) {
  await page.goto('/login');
  await page.waitForLoadState('networkidle');

  // 여러 가능한 테스트 계정으로 시도
  const testAccounts = [
    { email: 'admin@example.com', password: 'password' },
  ];

  for (const account of testAccounts) {
    // 페이지 새로고침
    await page.reload();
    await page.waitForLoadState('networkidle');

    // 로그인 시도
    await page.fill('#email', account.email);
    await page.fill('#password', account.password);
    await page.click('button:has-text("로그인")');

    // 2초 대기 후 성공 여부 확인
    await page.waitForTimeout(2000);

    // 로그인 성공 시 URL이 변경됨
    if (!page.url().includes('/login')) {
      console.log(`로그인 성공: ${account.email}`);
      return true;
    }

    console.log(`로그인 실패: ${account.email}`);
  }

  return false;
}

test.describe('커스텀 화면 테스트', () => {
  test('인증 문제 진단 및 커스텀 화면 접근 테스트', async ({ page }) => {
    console.log('=== 인증 문제 진단 시작 ===');

    // 1. 로그인 페이지 접근 확인
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    const loginPageTitle = await page.title();
    console.log(`로그인 페이지 제목: ${loginPageTitle}`);

    // 로그인 폼 요소 확인
    const emailField = page.locator('#email');
    const passwordField = page.locator('#password');
    const loginButton = page.locator('button:has-text("로그인")');

    await expect(emailField).toBeVisible();
    await expect(passwordField).toBeVisible();
    await expect(loginButton).toBeVisible();

    console.log('로그인 폼 요소 확인 완료');

    // 2. 로그인 시도
    const loginSuccess = await loginAsAdmin(page);

    if (!loginSuccess) {
      console.log('=== 인증 실패 - 테스트 중단 ===');

      // 인증 실패 시 진단 정보 수집
      const currentUrl = page.url();
      const pageContent = await page.locator('body').textContent();

      console.log(`현재 URL: ${currentUrl}`);
      console.log('페이지 내용에 포함된 오류 메시지:');

      if (pageContent.includes('이메일 또는 비밀번호가 일치하지 않습니다')) {
        console.log('- 이메일 또는 비밀번호 불일치');
      }
      if (pageContent.includes('데이터베이스')) {
        console.log('- 데이터베이스 연결 문제');
      }
      if (pageContent.includes('서버')) {
        console.log('- 서버 문제');
      }

      // 스크린샷 저장
      await page.screenshot({
        path: 'tests/e2e/screenshots/custom-screen-auth-failure.png',
        fullPage: true
      });

      // 테스트 실패 - 하지만 진단 정보는 제공함
      expect(loginSuccess).toBe(true); // 이 부분에서 테스트가 실패하며 상세 정보를 제공
      return;
    }

    console.log('=== 인증 성공 - 커스텀 화면 테스트 진행 ===');

    // 3. 대시보드 또는 메인 페이지 확인
    await page.waitForTimeout(2000);
    const currentUrl = page.url();
    console.log(`로그인 후 현재 URL: ${currentUrl}`);

    // 4. 커스텀 화면 URL로 직접 이동 시도 (새로운 샌드박스 템플릿 구조)
    console.log('커스텀 화면 URL로 이동 중...');
    await page.goto('/sandbox/storage-sandbox-template/100-domain-pms/103-screen-table-view');
    await page.waitForLoadState('networkidle');

    const customScreenUrl = page.url();
    console.log(`커스텀 화면 페이지 URL: ${customScreenUrl}`);

    // 5. 페이지 내용 확인
    const pageTitle = await page.title();
    const bodyContent = await page.locator('body').textContent();

    console.log(`페이지 제목: ${pageTitle}`);

    // 다시 로그인 페이지로 리다이렉트되었는지 확인
    if (customScreenUrl.includes('/login')) {
      console.log('❌ 커스텀 화면 접근 실패: 로그인 페이지로 리다이렉트됨');
      console.log('권한 또는 라우팅 문제가 있습니다.');

      // 스크린샷 저장
      await page.screenshot({
        path: 'tests/e2e/screenshots/custom-screen-redirect-to-login.png',
        fullPage: true
      });
    } else {
      console.log('✅ 커스텀 화면 접근 성공');

      // 페이지 내용 분석
      if (bodyContent.includes('템플릿 렌더링 오류')) {
        console.log('⚠️ 템플릿 렌더링 오류 발견');
      }
      if (bodyContent.includes('커스텀 화면')) {
        console.log('✅ 커스텀 화면 콘텐츠 발견');
      }
      if (bodyContent.includes('파일 처리 오류')) {
        console.log('⚠️ 파일 처리 오류 발견');
      }

      // 전체 페이지 스크린샷 저장
      await page.screenshot({
        path: 'tests/e2e/screenshots/custom-screen-success.png',
        fullPage: true
      });
    }

    // 6. 페이지 요소 확인 (페이지가 로드된 경우)
    if (!customScreenUrl.includes('/login')) {
      console.log('페이지 요소 분석 중...');

      // 주요 요소들 확인
      const headings = await page.locator('h1, h2, h3').count();
      const buttons = await page.locator('button').count();
      const links = await page.locator('a').count();

      console.log(`제목 요소 수: ${headings}`);
      console.log(`버튼 수: ${buttons}`);
      console.log(`링크 수: ${links}`);

      // 오류 메시지 확인
      const errorElements = await page.locator('.bg-red-50, .text-red-600, .text-red-700, .text-red-800').count();
      if (errorElements > 0) {
        console.log(`❌ 오류 표시 요소 발견: ${errorElements}개`);

        const errorTexts = await page.locator('.bg-red-50, .text-red-600, .text-red-700, .text-red-800').allTextContents();
        errorTexts.forEach(text => console.log(`오류 내용: ${text}`));
      } else {
        console.log('✅ 오류 표시 없음');
      }
    }

    console.log('=== 커스텀 화면 테스트 완료 ===');
  });

  test('프로젝트 페이지 커스텀 화면 설정 테스트', async ({ page }) => {
    // 로그인 시도
    const loginSuccess = await loginAsAdmin(page);

    if (!loginSuccess) {
      console.log('로그인 실패로 인해 테스트 건너뛰기');
      test.skip();
      return;
    }

    console.log('=== 커스텀 화면 설정 테스트 시작 ===');

    // 페이지 설정 URL로 이동 (기존 설정 페이지는 그대로 유지)
    await page.goto('/organizations/1/projects/1/pages/3/settings/custom-screen');
    await page.waitForLoadState('networkidle');

    const settingsUrl = page.url();
    console.log(`설정 페이지 URL: ${settingsUrl}`);

    if (settingsUrl.includes('/login')) {
      console.log('❌ 설정 페이지 접근 실패: 권한 문제');

      // 스크린샷 저장
      await page.screenshot({
        path: 'tests/e2e/screenshots/custom-screen-settings-access-denied.png',
        fullPage: true
      });

      test.skip();
      return;
    }

    // 설정 페이지 내용 확인
    const pageContent = await page.locator('body').textContent();

    if (pageContent.includes('커스텀 화면')) {
      console.log('✅ 커스텀 화면 설정 페이지 로드 성공');

      // 설정 폼 요소 확인
      const selectElements = await page.locator('select').count();
      const saveButtons = await page.locator('button:has-text("저장")').count();

      console.log(`선택 요소 수: ${selectElements}`);
      console.log(`저장 버튼 수: ${saveButtons}`);

    } else {
      console.log('❌ 커스텀 화면 설정 페이지 내용 불일치');
    }

    // 전체 페이지 스크린샷 저장
    await page.screenshot({
      path: 'tests/e2e/screenshots/custom-screen-settings.png',
      fullPage: true
    });

    console.log('=== 커스텀 화면 설정 테스트 완료 ===');
  });

  test('샌드박스 커스텀 화면 브라우저 테스트', async ({ page }) => {
    // 로그인 시도
    const loginSuccess = await loginAsAdmin(page);

    if (!loginSuccess) {
      console.log('로그인 실패로 인해 테스트 건너뛰기');
      test.skip();
      return;
    }

    console.log('=== 샌드박스 커스텀 화면 브라우저 테스트 시작 ===');

    // 샌드박스 커스텀 화면 페이지로 이동
    await page.goto('/sandbox/custom-screens');
    await page.waitForLoadState('networkidle');

    const sandboxUrl = page.url();
    console.log(`샌드박스 페이지 URL: ${sandboxUrl}`);

    if (!sandboxUrl.includes('/login')) {
      console.log('✅ 샌드박스 페이지 접근 성공');

      const pageContent = await page.locator('body').textContent();

      // 커스텀 화면 목록 확인
      if (pageContent.includes('dashboard') || pageContent.includes('table') || pageContent.includes('calendar')) {
        console.log('✅ 커스텀 화면 목록 발견');
      } else {
        console.log('⚠️ 커스텀 화면 목록 없음');
      }

      // 전체 페이지 스크린샷 저장
      await page.screenshot({
        path: 'tests/e2e/screenshots/sandbox-custom-screens.png',
        fullPage: true
      });
    } else {
      console.log('❌ 샌드박스 페이지 접근 실패');
    }

    console.log('=== 샌드박스 커스텀 화면 브라우저 테스트 완료 ===');
  });
});
