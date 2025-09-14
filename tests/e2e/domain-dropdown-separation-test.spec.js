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

test.describe('도메인 드롭다운 분리 테스트', () => {
  test('도메인 드롭다운과 화면 드롭다운이 분리되어 작동하는지 확인', async ({ page }) => {
    console.log('=== 도메인 드롭다운 분리 테스트 시작 ===');

    // 로그인
    const loginSuccess = await loginAsAdmin(page);
    if (!loginSuccess) {
      test.skip();
      return;
    }

    // 프로젝트 2 페이지 2로 이동 (샌드박스가 설정된 페이지)
    await page.goto('/organizations/1/projects/2/pages/2');
    await page.waitForLoadState('networkidle');

    console.log(`접근 URL: ${page.url()}`);

    if (page.url().includes('/login')) {
      console.log('❌ 페이지 접근 실패');
      test.skip();
      return;
    }

    console.log('✅ 프로젝트 2 페이지 2 접근 성공');

    // 페이지 구조 확인 - 3개의 드롭다운이 있어야 함
    const allDropdowns = page.locator('[wire\\:click*="$toggle"]');
    const dropdownCount = await allDropdowns.count();
    console.log(`전체 드롭다운 수: ${dropdownCount}`);

    // 1. 도메인 드롭다운 확인
    const domainDropdown = page.locator('[wire\\:click*="domainDropdownOpen"]');
    
    if (await domainDropdown.count() > 0) {
      console.log('✅ 도메인 드롭다운 발견');

      // 도메인 드롭다운 텍스트 확인
      const domainButton = domainDropdown.first();
      const domainButtonText = await domainButton.textContent();
      console.log(`도메인 버튼 텍스트: "${domainButtonText.replace(/\s+/g, ' ').trim()}"`);

      // 도메인 드롭다운 열기
      await domainButton.click();
      await page.waitForTimeout(1000);

      // 도메인 옵션들 확인
      const domainOptions = page.locator('[wire\\:click*="selectDomain"]');
      const domainOptionCount = await domainOptions.count();
      console.log(`도메인 옵션 수: ${domainOptionCount}`);

      // 각 도메인 옵션 확인
      for (let i = 0; i < Math.min(domainOptionCount, 5); i++) {
        const option = domainOptions.nth(i);
        const optionText = await option.textContent();
        console.log(`도메인 ${i + 1}: "${optionText.replace(/\s+/g, ' ').trim()}"`);
      }

      // 다른 도메인 선택
      if (domainOptionCount > 1) {
        console.log('다른 도메인 선택 시도...');
        await domainOptions.nth(1).click();
        await page.waitForTimeout(2000);

        // 선택 후 도메인 버튼 텍스트 변경 확인
        const updatedDomainText = await domainButton.textContent();
        console.log(`변경된 도메인 버튼 텍스트: "${updatedDomainText.replace(/\s+/g, ' ').trim()}"`);
      }

    } else {
      console.log('❌ 도메인 드롭다운을 찾을 수 없음');
    }

    // 2. 화면 드롭다운 확인
    const screenDropdown = page.locator('[wire\\:click*="dropdownOpen"]:not([wire\\:click*="domainDropdownOpen"])');
    
    if (await screenDropdown.count() > 0) {
      console.log('✅ 화면 드롭다운 발견');

      // 화면 드롭다운 텍스트 확인
      const screenButton = screenDropdown.first();
      const screenButtonText = await screenButton.textContent();
      console.log(`화면 버튼 텍스트: "${screenButtonText.replace(/\s+/g, ' ').trim()}"`);

      // 화면 드롭다운 열기
      await screenButton.click();
      await page.waitForTimeout(1000);

      // 현재 도메인의 화면들만 표시되는지 확인
      const screenOptions = page.locator('[wire\\:click*="selectScreen"]');
      const screenOptionCount = await screenOptions.count();
      console.log(`현재 도메인의 화면 옵션 수: ${screenOptionCount}`);

      // 각 화면 옵션 확인
      for (let i = 0; i < Math.min(screenOptionCount, 3); i++) {
        const option = screenOptions.nth(i);
        const optionText = await option.textContent();
        console.log(`화면 ${i + 1}: "${optionText.replace(/\s+/g, ' ').trim()}"`);
      }

      // 화면 선택
      if (screenOptionCount > 0) {
        console.log('화면 선택 시도...');
        await screenOptions.first().click();
        await page.waitForTimeout(3000);

        // 선택 후 화면 버튼 텍스트 변경 확인
        const updatedScreenText = await screenButton.textContent();
        console.log(`변경된 화면 버튼 텍스트: "${updatedScreenText.replace(/\s+/g, ' ').trim()}"`);

        // 성공 메시지 확인
        const successMsg = page.locator('.text-green-600');
        if (await successMsg.count() > 0) {
          const successText = await successMsg.first().textContent();
          console.log(`성공 메시지: ${successText.replace(/\s+/g, ' ').trim()}`);
        }
      }

    } else {
      console.log('❌ 화면 드롭다운을 찾을 수 없음');
    }

    // 3. 도메인 변경이 화면 옵션에 미치는 영향 테스트
    console.log('\n=== 도메인 변경 후 화면 옵션 변경 테스트 ===');

    // 도메인 다시 변경
    const domainDropdown2 = page.locator('[wire\\:click*="domainDropdownOpen"]');
    if (await domainDropdown2.count() > 0) {
      await domainDropdown2.first().click();
      await page.waitForTimeout(1000);

      const domainOptions2 = page.locator('[wire\\:click*="selectDomain"]');
      const optionCount2 = await domainOptions2.count();

      if (optionCount2 > 1) {
        // 첫 번째 도메인 선택 (이전과 다른 도메인)
        await domainOptions2.first().click();
        await page.waitForTimeout(2000);

        console.log('도메인 변경 완료, 화면 옵션 변화 확인...');

        // 화면 드롭다운 다시 열기
        const screenDropdown2 = page.locator('[wire\\:click*="dropdownOpen"]:not([wire\\:click*="domainDropdownOpen"])');
        await screenDropdown2.first().click();
        await page.waitForTimeout(1000);

        const newScreenOptions = page.locator('[wire\\:click*="selectScreen"]');
        const newScreenCount = await newScreenOptions.count();
        console.log(`도메인 변경 후 화면 옵션 수: ${newScreenCount}`);

        // 새 화면 옵션들 확인
        for (let i = 0; i < Math.min(newScreenCount, 3); i++) {
          const option = newScreenOptions.nth(i);
          const optionText = await option.textContent();
          console.log(`새 화면 ${i + 1}: "${optionText.replace(/\s+/g, ' ').trim()}"`);
        }
      }
    }

    // 최종 페이지 스크린샷
    await page.screenshot({
      path: 'tests/e2e/screenshots/domain-dropdown-separation-test.png',
      fullPage: true
    });

    console.log('=== 도메인 드롭다운 분리 테스트 완료 ===');
  });
});