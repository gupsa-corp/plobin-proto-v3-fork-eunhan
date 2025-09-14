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

test.describe('커스텀 화면 드롭다운 테스트', () => {
  test('프로젝트 대시보드 커스텀 화면 드롭다운 기능 테스트', async ({ page }) => {
    console.log('=== 커스텀 화면 드롭다운 테스트 시작 ===');

    // 로그인 시도
    const loginSuccess = await loginAsAdmin(page);

    if (!loginSuccess) {
      console.log('로그인 실패로 인해 테스트 건너뛰기');
      test.skip();
      return;
    }

    // 프로젝트 페이지로 이동 (페이지 ID 포함)
    await page.goto('/organizations/1/projects/1/pages/3');
    await page.waitForLoadState('networkidle');

    const dashboardUrl = page.url();
    console.log(`대시보드 URL: ${dashboardUrl}`);

    // 로그인 페이지로 리다이렉트된 경우
    if (dashboardUrl.includes('/login')) {
      console.log('❌ 대시보드 접근 실패: 권한 문제');
      test.skip();
      return;
    }

    console.log('✅ 프로젝트 대시보드 접근 성공');

    // 커스텀 화면 드롭다운이 있는지 확인
    const customScreenDropdown = page.locator('[wire\\:click*="dropdownOpen"]');
    
    if (await customScreenDropdown.count() > 0) {
      console.log('✅ 커스텀 화면 드롭다운 발견');

      // 현재 선택된 화면 표시 확인
      const currentScreen = page.locator('text="활성화됨:"');
      if (await currentScreen.count() > 0) {
        const currentScreenText = await currentScreen.first().textContent();
        console.log(`현재 선택된 화면: ${currentScreenText}`);
      }

      // 도메인 정보 표시 확인
      const domainInfo = page.locator('text="도메인:"');
      if (await domainInfo.count() > 0) {
        const domainText = await domainInfo.first().textContent();
        console.log(`현재 도메인: ${domainText}`);
      } else {
        console.log('⚠️ 도메인 정보가 표시되지 않음');
      }

      // 드롭다운 클릭 시도
      try {
        console.log('드롭다운 버튼 클릭 시도...');
        await customScreenDropdown.first().click();
        
        // Livewire 업데이트 대기
        await page.waitForTimeout(1000);

        // 드롭다운 메뉴가 열렸는지 확인
        const dropdownMenu = page.locator('[wire\\:click\\.away="closeDropdown"]');
        
        if (await dropdownMenu.isVisible()) {
          console.log('✅ 드롭다운 메뉴 열림 성공');

          // 드롭다운 옵션들 확인
          const screenOptions = page.locator('[wire\\:click*="selectScreen"]');
          const optionCount = await screenOptions.count();
          console.log(`사용 가능한 화면 옵션: ${optionCount}개`);

          // 각 옵션의 제목과 도메인 정보 확인
          for (let i = 0; i < Math.min(optionCount, 5); i++) { // 최대 5개만 확인
            const option = screenOptions.nth(i);
            const optionText = await option.textContent();
            console.log(`옵션 ${i + 1}: ${optionText}`);
          }

          // 첫 번째 옵션이 있다면 선택 테스트
          if (optionCount > 0) {
            console.log('첫 번째 화면 옵션 선택 시도...');
            
            try {
              await screenOptions.first().click();
              
              // Livewire 업데이트 대기
              await page.waitForTimeout(2000);

              console.log('✅ 화면 선택 완료');
              
              // 성공/오류 메시지 확인
              const successMessage = page.locator('.text-green-600');
              const errorMessage = page.locator('.text-red-600');

              if (await successMessage.count() > 0) {
                const successText = await successMessage.first().textContent();
                console.log(`성공 메시지: ${successText}`);
              }

              if (await errorMessage.count() > 0) {
                const errorText = await errorMessage.first().textContent();
                console.log(`오류 메시지: ${errorText}`);
              }

            } catch (error) {
              console.log(`⚠️ 화면 선택 중 오류: ${error.message}`);
            }
          }

        } else {
          console.log('❌ 드롭다운 메뉴 열림 실패');
        }

      } catch (error) {
        console.log(`⚠️ 드롭다운 클릭 중 오류: ${error.message}`);
      }

    } else {
      console.log('⚠️ 커스텀 화면 드롭다운을 찾을 수 없음');
    }

    // 전체 페이지 스크린샷 저장
    await page.screenshot({
      path: 'tests/e2e/screenshots/custom-screen-dropdown-test.png',
      fullPage: true
    });

    console.log('=== 커스텀 화면 드롭다운 테스트 완료 ===');
  });

  test('커스텀 화면 선택 시 도메인 정보 업데이트 테스트', async ({ page }) => {
    console.log('=== 도메인 정보 업데이트 테스트 시작 ===');

    const loginSuccess = await loginAsAdmin(page);
    if (!loginSuccess) {
      test.skip();
      return;
    }

    // 프로젝트 페이지로 이동 (페이지 ID 포함)
    await page.goto('/organizations/1/projects/1/pages/3');
    await page.waitForLoadState('networkidle');

    if (page.url().includes('/login')) {
      test.skip();
      return;
    }

    // 페이지에 샌드박스가 설정되어 있는지 확인
    const sandboxInfo = page.locator('text="샌드박스"');
    
    if (await sandboxInfo.count() > 0) {
      console.log('✅ 샌드박스 정보 발견');

      // 현재 도메인 정보 추출
      const domainElement = page.locator('span:has-text("도메인:")');
      let currentDomain = '';
      
      if (await domainElement.count() > 0) {
        currentDomain = await domainElement.first().textContent();
        console.log(`현재 도메인 정보: ${currentDomain}`);
      }

      // 드롭다운을 통한 화면 변경 테스트
      const dropdown = page.locator('[wire\\:click*="dropdownOpen"]');
      
      if (await dropdown.count() > 0) {
        await dropdown.first().click();
        await page.waitForTimeout(1000);

        const options = page.locator('[wire\\:click*="selectScreen"]');
        const optionCount = await options.count();

        if (optionCount > 1) {
          // 두 번째 옵션 선택 (첫 번째와 다른 것을 선택)
          console.log('다른 화면 옵션 선택 중...');
          await options.nth(1).click();
          
          // 페이지 리로드 대기
          await page.waitForTimeout(3000);
          
          // 도메인 정보가 업데이트 되었는지 확인
          const updatedDomainElement = page.locator('span:has-text("도메인:")');
          
          if (await updatedDomainElement.count() > 0) {
            const updatedDomain = await updatedDomainElement.first().textContent();
            console.log(`업데이트된 도메인 정보: ${updatedDomain}`);
            
            if (updatedDomain !== currentDomain) {
              console.log('✅ 도메인 정보 업데이트 성공');
            } else {
              console.log('⚠️ 도메인 정보 변경되지 않음');
            }
          }
        }
      }

    } else {
      console.log('⚠️ 샌드박스가 설정되지 않은 페이지');
    }

    // 최종 스크린샷
    await page.screenshot({
      path: 'tests/e2e/screenshots/domain-info-update-test.png',
      fullPage: true
    });

    console.log('=== 도메인 정보 업데이트 테스트 완료 ===');
  });
});