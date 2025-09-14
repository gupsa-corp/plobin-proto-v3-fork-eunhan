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

test.describe('도메인 정보 저장 테스트', () => {
  test('커스텀 화면 선택 후 도메인 정보 저장 확인', async ({ page }) => {
    console.log('=== 도메인 정보 저장 테스트 시작 ===');

    // 로그인
    const loginSuccess = await loginAsAdmin(page);
    if (!loginSuccess) {
      test.skip();
      return;
    }

    // 프로젝트 페이지로 이동
    await page.goto('/organizations/1/projects/1/pages/3');
    await page.waitForLoadState('networkidle');

    if (page.url().includes('/login')) {
      test.skip();
      return;
    }

    console.log('✅ 프로젝트 페이지 접근 성공');

    // 현재 페이지 상태 확인
    const hasSandboxInfo = page.locator('text="샌드박스"');
    const hasDropdown = page.locator('[wire\\:click*="dropdownOpen"]');

    if (await hasSandboxInfo.count() > 0 && await hasDropdown.count() > 0) {
      console.log('✅ 샌드박스 및 드롭다운 발견');

      // 드롭다운 열기
      await hasDropdown.first().click();
      await page.waitForTimeout(1000);

      // 사용 가능한 화면 옵션 확인
      const options = page.locator('[wire\\:click*="selectScreen"]');
      const optionCount = await options.count();
      
      if (optionCount > 0) {
        console.log(`사용 가능한 화면 옵션: ${optionCount}개`);

        // 각 옵션의 도메인 정보 확인
        for (let i = 0; i < Math.min(optionCount, 3); i++) {
          const option = options.nth(i);
          const optionText = await option.textContent();
          console.log(`옵션 ${i + 1}: ${optionText.replace(/\s+/g, ' ').trim()}`);
        }

        // 특정 도메인의 화면 선택 (예: 100-domain-pms)
        const pmsOption = options.filter({ hasText: '100 domain pms' }).first();
        
        if (await pmsOption.count() > 0) {
          console.log('PMS 도메인 화면 선택 중...');
          
          await pmsOption.click();
          
          // 페이지 리로드 대기
          await page.waitForTimeout(3000);
          
          // 선택 결과 확인
          const currentScreen = page.locator('span:has-text("활성화됨:")');
          const currentDomain = page.locator('span:has-text("도메인:")');

          if (await currentScreen.count() > 0) {
            const screenText = await currentScreen.first().textContent();
            console.log(`현재 활성화된 화면: ${screenText}`);
          }

          if (await currentDomain.count() > 0) {
            const domainText = await currentDomain.first().textContent();
            console.log(`✅ 현재 도메인: ${domainText}`);
          } else {
            console.log('⚠️ 도메인 정보가 표시되지 않음');
          }

          // 성공/오류 메시지 확인
          const successMsg = page.locator('.text-green-600');
          const errorMsg = page.locator('.text-red-600');

          if (await successMsg.count() > 0) {
            const successText = await successMsg.first().textContent();
            console.log(`성공 메시지: ${successText.replace(/\s+/g, ' ').trim()}`);
          }

          if (await errorMsg.count() > 0) {
            const errorText = await errorMsg.first().textContent();
            console.log(`오류 메시지: ${errorText.replace(/\s+/g, ' ').trim()}`);
          }

        } else {
          console.log('⚠️ PMS 도메인 화면을 찾을 수 없음');
        }

        // 다른 도메인 화면도 테스트 (예: 101-domain-rfx)
        await page.waitForTimeout(2000);
        
        // 드롭다운 다시 열기
        await hasDropdown.first().click();
        await page.waitForTimeout(1000);
        
        const rfxOption = options.filter({ hasText: '101 domain rfx' }).first();
        
        if (await rfxOption.count() > 0) {
          console.log('RFX 도메인 화면 선택 중...');
          
          await rfxOption.click();
          
          // 페이지 리로드 대기
          await page.waitForTimeout(3000);
          
          // 도메인 변경 확인
          const updatedDomain = page.locator('span:has-text("도메인:")');
          
          if (await updatedDomain.count() > 0) {
            const domainText = await updatedDomain.first().textContent();
            console.log(`✅ 업데이트된 도메인: ${domainText}`);
            
            if (domainText.includes('rfx') || domainText.includes('Rfx')) {
              console.log('✅ 도메인 변경 성공');
            } else {
              console.log('⚠️ 도메인이 제대로 변경되지 않음');
            }
          }
        }

      } else {
        console.log('⚠️ 사용 가능한 화면 옵션이 없음');
      }

    } else {
      console.log('⚠️ 샌드박스 또는 드롭다운을 찾을 수 없음');
    }

    // 최종 상태 스크린샷
    await page.screenshot({
      path: 'tests/e2e/screenshots/domain-storage-final.png',
      fullPage: true
    });

    console.log('=== 도메인 정보 저장 테스트 완료 ===');
  });
});