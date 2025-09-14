import { test, expect } from '@playwright/test';

test.describe('멤버 초대 기능 테스트', () => {

  test.beforeEach(async ({ page }) => {
    // 페이지 접속 전에 콘솔 에러 로그 수집
    page.on('console', msg => {
      if (msg.type() === 'error') {
        console.log('Browser console error:', msg.text());
      }
    });

    // 로그인 페이지로 이동
    await page.goto('http://localhost:8500/login');

    // 관리자 계정으로 로그인
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // 로그인 후 페이지가 로드될 때까지 대기
    await page.waitForLoadState('networkidle');
  });

  test('멤버 관리 페이지 접근 가능', async ({ page }) => {
    // 조직 관리 페이지로 이동
    await page.goto('http://localhost:8500/organizations/7/admin/members');

    // 페이지 로드 대기
    await page.waitForLoadState('networkidle');

    // 페이지 제목 확인
    await expect(page.locator('h3')).toContainText('조직 멤버');

    // 초대 버튼 확인
    await expect(page.locator('text=+ 멤버 초대')).toBeVisible();

    // Livewire가 로드되었는지 확인
    const livewireLoaded = await page.evaluate(() => {
      return typeof window.Livewire !== 'undefined';
    });

    expect(livewireLoaded).toBe(true);
  });

  test('멤버 초대 모달 열기', async ({ page }) => {
    await page.goto('http://localhost:8500/organizations/7/admin/members');
    await page.waitForLoadState('networkidle');

    // 초대 버튼이 보이는지 확인
    await expect(page.locator('text=+ 멤버 초대')).toBeVisible();

    // 콘솔 오류 확인을 위한 로그 수집
    const consoleMessages = [];
    page.on('console', msg => {
      console.log(`Console ${msg.type()}: ${msg.text()}`);
      consoleMessages.push({ type: msg.type(), text: msg.text() });
    });

    // 네트워크 요청 확인
    page.on('request', request => {
      if (request.url().includes('livewire')) {
        console.log('Livewire request:', request.url());
      }
    });

    // Livewire 초기화 확인
    const livewireReady = await page.evaluate(() => {
      return typeof window.Livewire !== 'undefined';
    });
    console.log('Livewire ready:', livewireReady);

    // 초대 버튼 클릭
    console.log('Clicking invite button...');
    await page.click('text=+ 멤버 초대');

    // 클릭 후 잠시 대기
    await page.waitForTimeout(2000);

    // 모달이 있는지 확인
    const modalExists = await page.locator('.fixed.inset-0').count();
    console.log('Modal count after click:', modalExists);

    if (modalExists === 0) {
      // 모달이 없으면 HTML 확인
      const htmlContent = await page.content();
      console.log('Page HTML length:', htmlContent.length);

      // Livewire 상태 확인
      const livewireState = await page.evaluate(() => {
        const component = window.Livewire.components.all()[0];
        return component ? component.getData() : null;
      });
      console.log('Livewire component state:', JSON.stringify(livewireState, null, 2));
    }

    // 모달이 나타날 때까지 대기 (더 긴 대기 시간)
    try {
      await page.waitForSelector('.fixed.inset-0', { timeout: 15000 });
      console.log('Modal appeared successfully');
    } catch (error) {
      console.log('Modal did not appear:', error.message);
      throw error;
    }

    // 모달 내용 확인
    await expect(page.locator('text=멤버 초대')).toBeVisible();
    await expect(page.locator('input[wire\\:model="inviteEmail"]')).toBeVisible();
    await expect(page.locator('select[wire\\:model="inviteRole"]')).toBeVisible();

    // 취소 버튼으로 모달 닫기
    await page.click('text=취소');
    await page.waitForSelector('.fixed.inset-0', { state: 'hidden', timeout: 5000 });
  });

  test('새 멤버 초대 기능', async ({ page }) => {
    await page.goto('http://localhost:8500/organizations/7/admin/members');
    await page.waitForLoadState('networkidle');

    const testEmail = `test${Date.now()}@example.com`;

    // 초대 버튼 클릭
    await page.click('text=+ 멤버 초대');
    await page.waitForSelector('.fixed.inset-0');

    // 초대 정보 입력
    await page.fill('input[wire\\:model="inviteEmail"]', testEmail);
    await page.selectOption('select[wire\\:model="inviteRole"]', 'user');

    // 초대 전송
    await page.click('text=초대 전송');

    // 모달이 닫힐 때까지 대기
    await page.waitForSelector('.fixed.inset-0', { state: 'hidden', timeout: 10000 });

    // 페이지 새로고침 후 새 멤버 확인
    await page.reload();
    await page.waitForLoadState('networkidle');

    // 새로 추가된 멤버가 목록에 있는지 확인
    const memberExists = await page.locator('table').textContent();
    expect(memberExists).toContain(testEmail);
  });

  test('검색 기능', async ({ page }) => {
    await page.goto('http://localhost:8500/organizations/7/admin/members');
    await page.waitForLoadState('networkidle');

    // 검색어 입력
    await page.fill('input[wire\\:model\\.live="searchTerm"]', 'admin');

    // 검색 결과 로드 대기
    await page.waitForTimeout(1000);

    // 검색 결과에 admin이 포함된 멤버만 표시되는지 확인
    const tableContent = await page.locator('table tbody').textContent();
    expect(tableContent).toContain('admin@example.com');
  });

  test('역할 필터링', async ({ page }) => {
    await page.goto('http://localhost:8500/organizations/7/admin/members');
    await page.waitForLoadState('networkidle');

    // 역할 필터 선택
    await page.selectOption('select[wire\\:model\\.live="permissionFilter"]', 'organization_admin');

    // 필터링 결과 로드 대기
    await page.waitForTimeout(1000);

    // 관리자 역할 배지가 있는지 확인
    const adminBadges = page.locator('tbody .bg-purple-100, tbody .bg-red-100, tbody .bg-gray-100');
    await expect(adminBadges.first()).toBeVisible({ timeout: 5000 });
  });

  test('멤버 편집 모달', async ({ page }) => {
    await page.goto('http://localhost:8500/organizations/7/admin/members');
    await page.waitForLoadState('networkidle');

    // 편집 버튼이 있는지 확인 후 클릭
    const editButton = page.locator('button:has-text("편집")').first();
    await expect(editButton).toBeVisible();
    await editButton.click();

    // 편집 모달이 열리는지 확인
    await page.waitForSelector('text=멤버 편집', { timeout: 5000 });

    // 편집 모달 내용 확인
    await expect(page.locator('select[wire\\:model="editingMemberRole"]')).toBeVisible();

    // 취소 버튼으로 모달 닫기
    await page.click('text=취소');
    await page.waitForSelector('text=멤버 편집', { state: 'hidden', timeout: 5000 });
  });

});

// 이메일 전송 확인 테스트
test.describe('이메일 전송 테스트', () => {

  test('Mailhog에서 초대 이메일 확인', async ({ page }) => {
    // 먼저 멤버 초대
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://localhost:8500/organizations/7/admin/members');
    await page.waitForLoadState('networkidle');

    const testEmail = `mailtest${Date.now()}@example.com`;

    // 멤버 초대
    await page.click('text=+ 멤버 초대');
    await page.waitForSelector('.fixed.inset-0');
    await page.fill('input[wire\\:model="inviteEmail"]', testEmail);
    await page.selectOption('select[wire\\:model="inviteRole"]', 'user');
    await page.click('text=초대 전송');
    await page.waitForSelector('.fixed.inset-0', { state: 'hidden', timeout: 10000 });

    // 이메일 전송 처리 시간 대기
    await page.waitForTimeout(3000);

    // Mailhog으로 이동
    await page.goto('http://localhost:8025/');

    // 페이지 로드 대기
    await page.waitForLoadState('networkidle');

    // 새로고침
    await page.reload();
    await page.waitForLoadState('networkidle');

    // 이메일이 도착했는지 확인
    const emailExists = await page.locator('.messages').textContent();
    expect(emailExists).toContain(testEmail);

    // 이메일 클릭해서 내용 확인
    await page.click(`text=${testEmail}`);

    // 이메일 내용 확인
    await expect(page.locator('.tab-content')).toContainText('조직 초대를 받았습니다');
  });

});