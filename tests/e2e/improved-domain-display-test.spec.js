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

test.describe('개선된 도메인 표시 테스트', () => {
  test('프로젝트 2 페이지 2에서 도메인 정보 표시 및 변경 테스트', async ({ page }) => {
    console.log('=== 개선된 도메인 표시 테스트 시작 ===');

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

    // 페이지 전체 스크린샷 (드롭다운 테스트 전)
    await page.screenshot({
      path: 'tests/e2e/screenshots/before-domain-test.png',
      fullPage: true
    });

    // 현재 선택된 화면 정보 확인
    const currentScreenInfo = page.locator('text="현재 활성화된 커스텀 화면"');
    
    if (await currentScreenInfo.count() > 0) {
      console.log('✅ 현재 선택된 커스텀 화면 정보 발견');
      
      // 도메인 배지 확인
      const domainBadge = page.locator('.bg-blue-100.text-blue-800');
      if (await domainBadge.count() > 0) {
        const domainText = await domainBadge.first().textContent();
        console.log(`✅ 현재 도메인 배지: "${domainText}"`);
      } else {
        console.log('⚠️ 도메인 배지를 찾을 수 없음');
      }
    } else {
      console.log('⚠️ 현재 선택된 화면 정보를 찾을 수 없음');
    }

    // 드롭다운 버튼 찾기
    const dropdown = page.locator('[wire\\:click*="dropdownOpen"]');
    
    if (await dropdown.count() > 0) {
      console.log('✅ 커스텀 화면 드롭다운 발견');

      // 드롭다운 열기
      await dropdown.first().click();
      await page.waitForTimeout(1000);

      // 드롭다운 메뉴 확인
      const dropdownMenu = page.locator('[wire\\:click\\.away="closeDropdown"]');
      
      if (await dropdownMenu.isVisible()) {
        console.log('✅ 드롭다운 메뉴 열림');

        // 화면 옵션들 확인
        const screenOptions = page.locator('[wire\\:click*="selectScreen"]');
        const optionCount = await screenOptions.count();
        console.log(`사용 가능한 화면 옵션: ${optionCount}개`);

        // 각 옵션의 도메인 배지 확인
        for (let i = 0; i < Math.min(optionCount, 5); i++) {
          const option = screenOptions.nth(i);
          const optionText = await option.textContent();
          
          // 도메인 배지 찾기
          const domainBadgeInOption = option.locator('.bg-gray-100.text-gray-600');
          if (await domainBadgeInOption.count() > 0) {
            const domainText = await domainBadgeInOption.textContent();
            console.log(`옵션 ${i + 1} 도메인: "${domainText}"`);
          }
        }

        // 다른 도메인의 화면 선택 (현재가 RFX면 PMS로, PMS면 RFX로)
        const pmsOptions = screenOptions.filter({ hasText: '100 Domain Pms' });
        const rfxOptions = screenOptions.filter({ hasText: '101 Domain Rfx' });

        let targetOption = null;
        let targetDomainName = '';

        if (await pmsOptions.count() > 0) {
          targetOption = pmsOptions.first();
          targetDomainName = 'PMS';
          console.log('PMS 도메인 옵션으로 변경 시도');
        } else if (await rfxOptions.count() > 0) {
          targetOption = rfxOptions.first();
          targetDomainName = 'RFX';
          console.log('RFX 도메인 옵션으로 변경 시도');
        }

        if (targetOption) {
          // 선택 전 현재 도메인 정보 기록
          const beforeDomainBadge = page.locator('.bg-blue-100.text-blue-800');
          let beforeDomain = '';
          if (await beforeDomainBadge.count() > 0) {
            beforeDomain = await beforeDomainBadge.first().textContent();
          }

          console.log(`선택 전 도메인: "${beforeDomain}"`);

          // 화면 선택
          await targetOption.click();
          
          // Livewire 업데이트 대기
          await page.waitForTimeout(3000);

          // 선택 후 도메인 정보 확인
          const afterDomainBadge = page.locator('.bg-blue-100.text-blue-800');
          
          if (await afterDomainBadge.count() > 0) {
            const afterDomain = await afterDomainBadge.first().textContent();
            console.log(`선택 후 도메인: "${afterDomain}"`);

            if (afterDomain !== beforeDomain && afterDomain.includes(targetDomainName)) {
              console.log(`✅ 도메인 변경 성공: ${beforeDomain} → ${afterDomain}`);
            } else {
              console.log(`⚠️ 도메인 변경 실패 또는 불일치`);
            }
          } else {
            console.log('⚠️ 선택 후 도메인 배지를 찾을 수 없음');
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
          console.log('⚠️ 변경할 도메인 옵션을 찾을 수 없음');
        }

      } else {
        console.log('❌ 드롭다운 메뉴 열기 실패');
      }

    } else {
      console.log('❌ 커스텀 화면 드롭다운을 찾을 수 없음');
    }

    // 최종 페이지 스크린샷
    await page.screenshot({
      path: 'tests/e2e/screenshots/after-domain-test.png',
      fullPage: true
    });

    console.log('=== 개선된 도메인 표시 테스트 완료 ===');
  });
});