import { test, expect } from '@playwright/test';

// 로그인 헬퍼 함수
async function loginAsAdmin(page) {
  await page.goto('/login');
  await page.waitForLoadState('networkidle');

  await page.fill('#email', 'admin@example.com');
  await page.fill('#password', 'password');
  await page.click('button:has-text("로그인")');
  await page.waitForTimeout(2000);

  // 로그인 성공 확인
  return !page.url().includes('/login');
}

test.describe('Sandbox Custom Screens Fix Verification', () => {
  test('Verify /sandbox/custom-screens works after bug fixes', async ({ page }) => {
    console.log('=== Sandbox Custom Screens Fix Verification ===');

    // 1. 로그인
    const loginSuccess = await loginAsAdmin(page);
    expect(loginSuccess).toBe(true);
    console.log('✅ 로그인 성공');

    // 2. /sandbox/custom-screens 페이지 접근
    await page.goto('/sandbox/custom-screens');
    await page.waitForLoadState('networkidle');

    const currentUrl = page.url();
    console.log(`Current URL: ${currentUrl}`);

    // 3. 페이지가 로그인 페이지로 리다이렉트되지 않았는지 확인
    expect(currentUrl).not.toContain('/login');
    console.log('✅ 로그인 페이지로 리다이렉트 되지 않음');

    // 4. 올바른 URL에 접근했는지 확인
    expect(currentUrl).toContain('/sandbox/custom-screens');
    console.log('✅ 올바른 URL 접근 확인');

    // 5. 페이지 제목 확인
    const pageTitle = await page.title();
    expect(pageTitle).toContain('Plobin');
    console.log(`✅ 페이지 제목: ${pageTitle}`);

    // 6. 페이지 내용 확인 - 에러 메시지가 없는지 확인
    const bodyContent = await page.locator('body').textContent();

    // 이전 에러들이 없는지 확인
    expect(bodyContent).not.toContain('View [layouts.app] not found');
    expect(bodyContent).not.toContain('샌드박스가 선택되지 않았습니다');
    expect(bodyContent).not.toContain('InvalidArgumentException');
    expect(bodyContent).not.toContain('RuntimeException');
    console.log('✅ 이전 에러 메시지 없음 확인');

    // 7. 페이지 핵심 요소들이 존재하는지 확인
    const sandboxHeader = page.locator('text=샌드박스');
    await expect(sandboxHeader).toBeVisible();
    console.log('✅ 샌드박스 헤더 존재');

    const templateManager = page.locator('text=템플릿 화면 관리자');
    await expect(templateManager).toBeVisible();
    console.log('✅ 템플릿 화면 관리자 섹션 존재');

    // 8. 샌드박스 선택 드롭다운 확인
    const sandboxSelector = page.locator('select[wire\\:model="selectedSandbox"]');
    if (await sandboxSelector.isVisible()) {
      console.log('✅ 샌드박스 선택 드롭다운 존재');
    }

    // 9. 스크린샷 저장
    await page.screenshot({
      path: 'tests/e2e/screenshots/sandbox-custom-screens-fix-verification.png',
      fullPage: true
    });
    console.log('✅ 스크린샷 저장 완료');

    // 10. HTTP 응답 상태 확인 (간접적)
    const response = await page.goto('/sandbox/custom-screens');
    expect(response.status()).toBe(200);
    console.log('✅ HTTP 200 응답 확인');

    console.log('=== Fix Verification Complete ===');
  });

  test('Test sandbox context middleware functionality', async ({ page }) => {
    console.log('=== Sandbox Context Middleware Test ===');

    // 로그인
    const loginSuccess = await loginAsAdmin(page);
    expect(loginSuccess).toBe(true);

    // 샌드박스 페이지 접근
    await page.goto('/sandbox/custom-screens');
    await page.waitForLoadState('networkidle');

    // 페이지가 정상적으로 로드되었는지 확인
    const pageContent = await page.locator('body').textContent();

    // SandboxContextMiddleware가 정상적으로 작동하는지 확인
    // (에러가 발생하지 않았다면 미들웨어가 정상 작동)
    expect(pageContent).not.toContain('샌드박스가 선택되지 않았습니다');
    expect(pageContent).not.toContain('Exception');
    console.log('✅ SandboxContextMiddleware 정상 작동 확인');

    console.log('=== Middleware Test Complete ===');
  });
});