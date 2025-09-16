import { test, expect } from '@playwright/test';

test.describe('샌드박스 파일 업로드 리스트 화면', () => {
  test.beforeEach(async ({ page }) => {
    // 로그인
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
  });

  test('103-screen-uploaded-files-list 화면 렌더링 확인', async ({ page }) => {
    // 프로젝트 페이지로 직접 이동
    await page.goto('/organizations/1/projects/1/pages/4');
    await page.waitForLoadState('networkidle');

    // 페이지가 정상적으로 로드되는지 확인
    const pageTitle = await page.textContent('h1');
    console.log('Page title:', pageTitle);

    // 업로드된 파일 목록 헤더 확인 (h1 텍스트 확인)
    const hasFileListHeader = await page.locator('text=/업로드된 파일/i').count() > 0;
    if (!hasFileListHeader) {
      console.log('페이지 내용:', await page.content());
    }
    expect(hasFileListHeader).toBeTruthy();

    // 검색 필터 영역 확인
    await expect(page.locator('#search-input')).toBeVisible();
    await expect(page.locator('#type-filter')).toBeVisible();
    await expect(page.locator('#sort-select')).toBeVisible();

    // 파일 컨테이너 확인
    await expect(page.locator('#files-container')).toBeVisible();
  });

  test('파일 업로드 모달 열기 확인', async ({ page }) => {
    await page.goto('/organizations/1/projects/1/pages/4');
    await page.waitForLoadState('networkidle');

    // 업로드 버튼 확인 및 클릭
    const uploadButton = page.locator('button:has-text("새 파일 업로드")');
    await expect(uploadButton).toBeVisible();
    await uploadButton.click();

    // 모달 표시 확인
    await expect(page.locator('#upload-modal')).toBeVisible();
    await expect(page.locator('h3:has-text("파일 업로드")')).toBeVisible();

    // 드롭존 확인
    await expect(page.locator('#drop-zone')).toBeVisible();

    // 모달 닫기
    const closeButton = page.locator('#upload-modal button').filter({ hasText: '취소' }).first();
    await closeButton.click();
    await expect(page.locator('#upload-modal')).toBeHidden();
  });
});