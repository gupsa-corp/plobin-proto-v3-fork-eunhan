import { test, expect } from '@playwright/test';

test('단순 모달 테스트', async ({ page }) => {
  // 콘솔 메시지 수집
  page.on('console', msg => {
    console.log(`브라우저 콘솔 ${msg.type()}: ${msg.text()}`);
  });

  // 네트워크 요청 수집 (더 자세히)
  page.on('request', request => {
    if (request.url().includes('livewire')) {
      console.log('Livewire 요청:', request.method(), request.url());
      if (request.method() === 'POST') {
        console.log('POST 데이터:', request.postData());
      }
    }
  });

  page.on('response', response => {
    if (response.url().includes('livewire')) {
      console.log('Livewire 응답:', response.status(), response.url());
    }
  });

  // 로그인
  await page.goto('http://localhost:8500/login');
  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForLoadState('networkidle');

  // 멤버 관리 페이지로 이동
  await page.goto('http://localhost:8500/organizations/7/admin/members');
  await page.waitForLoadState('networkidle');

  console.log('페이지 로드 완료');

  // 초대 버튼이 보이는지 확인
  await expect(page.locator('text=+ 멤버 초대')).toBeVisible();
  console.log('초대 버튼 확인됨');

  // 버튼 클릭 전 3초 대기
  await page.waitForTimeout(3000);

  // 다양한 방법으로 버튼 클릭 시도
  console.log('일반 클릭 시도...');
  await page.click('text=+ 멤버 초대');
  await page.waitForTimeout(1000);

  console.log('강제 클릭 시도...');
  await page.click('button[wire\\:click="openInviteModal"]', { force: true });
  await page.waitForTimeout(1000);

  console.log('JavaScript 클릭 시도...');
  await page.evaluate(() => {
    const button = document.querySelector('button[wire\\:click="openInviteModal"]');
    if (button) {
      button.click();
      console.log('JavaScript click executed');
    }
  });
  await page.waitForTimeout(1000);

  // 테스트 함수 호출
  console.log('테스트 함수 호출...');
  await page.evaluate(() => {
    if (typeof window.testModal === 'function') {
      window.testModal();
    } else {
      console.log('testModal function not available');
    }
  });

  // 추가 3초 대기
  await page.waitForTimeout(3000);

  // 모달 존재 확인
  const modalExists = await page.locator('.fixed.inset-0').count();
  console.log('모달 개수:', modalExists);

  if (modalExists > 0) {
    console.log('✅ 모달이 나타났습니다!');

    // 모달 내용 확인
    const modalVisible = await page.locator('text=멤버 초대').isVisible();
    console.log('모달 제목 표시:', modalVisible);

    const emailInputVisible = await page.locator('input[wire\\:model="inviteEmail"]').isVisible();
    console.log('이메일 입력 필드 표시:', emailInputVisible);
  } else {
    console.log('❌ 모달이 나타나지 않았습니다');

    // 초대 버튼의 HTML 확인
    const buttonHtml = await page.locator('text=+ 멤버 초대').innerHTML();
    console.log('초대 버튼 HTML:', buttonHtml);

    // 버튼의 부모 요소 HTML 확인
    const buttonParentHtml = await page.locator('text=+ 멤버 초대').locator('..').innerHTML();
    console.log('버튼 부모 HTML:', buttonParentHtml);

    // Livewire 컴포넌트 wire:id 확인
    const wireIds = await page.locator('[wire\\:id]').all();
    console.log('Livewire 컴포넌트 개수:', wireIds.length);

    for (let i = 0; i < wireIds.length; i++) {
      const wireId = await wireIds[i].getAttribute('wire:id');
      console.log(`컴포넌트 ${i}: wire:id="${wireId}"`);
    }
  }
});