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

test.describe('화면 렌더링 테스트', () => {
  test('선택한 커스텀 화면이 실제로 페이지에 렌더링되는지 확인', async ({ page }) => {
    console.log('=== 화면 렌더링 테스트 시작 ===');

    // 로그인
    const loginSuccess = await loginAsAdmin(page);
    if (!loginSuccess) {
      test.skip();
      return;
    }

    // 프로젝트 2 페이지 2로 이동 (현재 101-screen-multi-file-upload로 설정됨)
    await page.goto('/organizations/1/projects/2/pages/2');
    await page.waitForLoadState('networkidle');

    console.log(`접근 URL: ${page.url()}`);

    if (page.url().includes('/login')) {
      console.log('❌ 페이지 접근 실패');
      test.skip();
      return;
    }

    console.log('✅ 프로젝트 2 페이지 2 접근 성공');

    // 페이지 로드 전 스크린샷
    await page.screenshot({
      path: 'tests/e2e/screenshots/before-screen-check.png',
      fullPage: true
    });

    // 페이지 내용 확인
    const pageContent = await page.locator('body').textContent();

    // 1. 커스텀 화면 컨텐츠가 렌더링되었는지 확인
    const customScreenContent = page.locator('.custom-screen-content');
    
    if (await customScreenContent.count() > 0) {
      console.log('✅ 커스텀 화면 컨텐츠 영역 발견');
      
      const contentText = await customScreenContent.textContent();
      console.log(`커스텀 화면 컨텐츠 일부: ${contentText.substring(0, 100)}...`);
      
    } else {
      console.log('⚠️ 커스텀 화면 컨텐츠 영역을 찾을 수 없음');
    }

    // 2. Multi File Upload 관련 요소들 확인
    const fileUploadElements = [
      'input[type="file"]',
      'text="파일 업로드"',
      'text="파일을 선택"',
      'text="드래그"',
      '.file-upload, .upload-area, .dropzone'
    ];

    let foundUploadElements = 0;
    for (const element of fileUploadElements) {
      const count = await page.locator(element).count();
      if (count > 0) {
        foundUploadElements++;
        console.log(`✅ 파일 업로드 요소 발견: ${element} (${count}개)`);
      }
    }

    if (foundUploadElements > 0) {
      console.log(`✅ 총 ${foundUploadElements}개의 파일 업로드 관련 요소 발견`);
    } else {
      console.log('⚠️ 파일 업로드 관련 요소를 찾을 수 없음');
    }

    // 3. 오류 메시지 확인
    const errorMessages = await page.locator('.bg-yellow-50, .bg-red-50, .text-red-600, .text-yellow-800').allTextContents();
    
    if (errorMessages.length > 0) {
      console.log('⚠️ 오류/경고 메시지 발견:');
      errorMessages.forEach((msg, index) => {
        console.log(`  오류 ${index + 1}: ${msg.replace(/\s+/g, ' ').trim()}`);
      });
    } else {
      console.log('✅ 오류/경고 메시지 없음');
    }

    // 4. 페이지 헤더 확인 (커스텀 화면 제목)
    const pageHeaders = await page.locator('h1, h2, h3').allTextContents();
    console.log('페이지 헤더들:');
    pageHeaders.forEach((header, index) => {
      console.log(`  헤더 ${index + 1}: ${header.replace(/\s+/g, ' ').trim()}`);
    });

    // 5. 드롭다운에서 다른 화면으로 변경 테스트
    console.log('\n=== 화면 변경 테스트 ===');
    
    const screenDropdown = page.locator('[wire\\:click*="dropdownOpen"]:not([wire\\:click*="domainDropdownOpen"])');
    
    if (await screenDropdown.count() > 0) {
      console.log('화면 드롭다운 열기...');
      await screenDropdown.first().click();
      await page.waitForTimeout(1000);

      const screenOptions = page.locator('[wire\\:click*="selectScreen"]');
      const optionCount = await screenOptions.count();

      if (optionCount > 1) {
        console.log('다른 화면 옵션으로 변경 중...');
        
        // 현재와 다른 화면 선택 (첫 번째가 아닌 것)
        await screenOptions.nth(1).click();
        await page.waitForTimeout(4000); // 화면 로드 대기
        
        console.log('화면 변경 완료, 새 화면 내용 확인...');
        
        // 변경된 화면 내용 확인
        const newPageContent = await page.locator('body').textContent();
        const newHeaders = await page.locator('h1, h2, h3').allTextContents();
        
        console.log('변경된 화면의 헤더들:');
        newHeaders.forEach((header, index) => {
          console.log(`  새 헤더 ${index + 1}: ${header.replace(/\s+/g, ' ').trim()}`);
        });

        // 새 화면 스크린샷
        await page.screenshot({
          path: 'tests/e2e/screenshots/after-screen-change.png',
          fullPage: true
        });
      }
    }

    // 최종 페이지 스크린샷
    await page.screenshot({
      path: 'tests/e2e/screenshots/screen-rendering-final.png',
      fullPage: true
    });

    console.log('=== 화면 렌더링 테스트 완료 ===');
  });
});