<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Mail\OrganizationInvitationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\DuskTestCase;

class MemberInvitationBrowserTest extends DuskTestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();

        // 권한 시스템 설정
        $this->app['cache']->forget('spatie.permission.cache');

        // 필요한 역할과 권한 생성
        $userRole = Role::create(['name' => 'user']);
        $adminRole = Role::create(['name' => 'organization_admin']);
        $ownerRole = Role::create(['name' => 'organization_owner']);

        $managePermission = Permission::create(['name' => 'manage members']);
        $adminRole->givePermissionTo($managePermission);
        $ownerRole->givePermissionTo($managePermission);

        // 테스트용 관리자 사용자 생성
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Admin',
            'country_code' => 'KR',
        ]);
        $this->admin->assignRole($adminRole);

        // 테스트용 조직 생성
        $this->organization = Organization::create([
            'name' => 'Test Organization',
            'user_id' => $this->admin->id,
        ]);

        // 관리자를 조직에 추가
        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->admin->id,
            'role_name' => 'organization_admin',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);
    }

    /** @test */
    public function admin_can_invite_member_through_ui()
    {
        Mail::fake();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->assertSee('조직 멤버')
                    ->assertSee('멤버 초대')
                    ->click('멤버 초대')
                    ->waitFor('[wire\\:model="inviteEmail"]', 10)
                    ->type('[wire\\:model="inviteEmail"]', 'newmember@example.com')
                    ->select('[wire\\:model="inviteRole"]', 'user')
                    ->click('초대 전송')
                    ->waitUntilMissing('[wire\\:model="inviteEmail"]', 10)
                    ->assertSee('newmember@example.com');
        });

        // 데이터베이스 확인
        $this->assertDatabaseHas('users', [
            'email' => 'newmember@example.com',
        ]);

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $this->organization->id,
            'invitation_status' => 'pending',
        ]);

        // 이메일 전송 확인
        Mail::assertSent(OrganizationInvitationMail::class);
    }

    /** @test */
    public function admin_can_search_members()
    {
        // 추가 테스트 멤버 생성
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'John',
            'country_code' => 'KR',
        ]);
        $user->assignRole('user');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->assertSee('John Doe')
                    ->type('[wire\\:model\\.live="searchTerm"]', 'John')
                    ->waitFor('tbody', 5)
                    ->assertSee('John Doe')
                    ->assertSee('john@example.com');
        });
    }

    /** @test */
    public function admin_can_filter_by_role()
    {
        // 다양한 역할의 멤버 생성
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Regular',
            'country_code' => 'KR',
        ]);
        $regularUser->assignRole('user');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $regularUser->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->select('[wire\\:model\\.live="permissionFilter"]', 'user')
                    ->waitFor('tbody', 5)
                    ->assertSee('Regular User')
                    ->assertSee('사용자'); // 역할 배지 확인
        });
    }

    /** @test */
    public function admin_can_edit_member_role()
    {
        // 편집할 멤버 생성
        $user = User::create([
            'name' => 'Edit User',
            'email' => 'edit@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Edit',
            'country_code' => 'KR',
        ]);
        $user->assignRole('user');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->assertSee('Edit User')
                    ->click('편집') // 첫 번째 편집 버튼 클릭
                    ->waitFor('[wire\\:model="editingMemberRole"]', 10)
                    ->select('[wire\\:model="editingMemberRole"]', 'organization_admin')
                    ->click('저장')
                    ->waitUntilMissing('[wire\\:model="editingMemberRole"]', 10)
                    ->assertSee('관리자'); // 변경된 역할 배지 확인
        });

        // 데이터베이스에서 역할 변경 확인
        $user->refresh();
        $this->assertTrue($user->hasRole('organization_admin'));
    }

    /** @test */
    public function admin_can_remove_member()
    {
        // 제거할 멤버 생성
        $user = User::create([
            'name' => 'Remove User',
            'email' => 'remove@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Remove',
            'country_code' => 'KR',
        ]);
        $user->assignRole('user');

        $member = OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->assertSee('Remove User')
                    ->click('제거') // 제거 버튼 클릭
                    ->waitUntilMissing('Remove User', 10); // 사용자가 목록에서 사라질 때까지 대기
        });

        // 데이터베이스에서 삭제 확인
        $this->assertDatabaseMissing('organization_members', [
            'id' => $member->id,
        ]);
    }

    /** @test */
    public function invitation_modal_works_properly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->click('멤버 초대')
                    ->waitFor('.fixed.inset-0', 5) // 모달 배경 대기
                    ->assertVisible('[wire\\:model="inviteEmail"]')
                    ->assertVisible('[wire\\:model="inviteRole"]')
                    ->click('취소')
                    ->waitUntilMissing('.fixed.inset-0', 5); // 모달이 사라질 때까지 대기
        });
    }

    /** @test */
    public function edit_modal_shows_current_member_info()
    {
        // 편집할 멤버 생성
        $user = User::create([
            'name' => 'Modal Test User',
            'email' => 'modal@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Modal',
            'country_code' => 'KR',
        ]);
        $user->assignRole('user');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    ->click('편집')
                    ->waitFor('.fixed.inset-0', 5)
                    ->assertSee('Modal Test User')
                    ->assertSee('modal@example.com')
                    ->assertSelected('[wire\\:model="editingMemberRole"]', 'user')
                    ->click('취소')
                    ->waitUntilMissing('.fixed.inset-0', 5);
        });
    }

    /** @test */
    public function member_statistics_display_correctly()
    {
        // 다양한 상태의 멤버들 생성
        $activeUser = User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Active',
            'country_code' => 'KR',
        ]);
        $activeUser->assignRole('user');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $activeUser->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        $pendingUser = User::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Pending',
            'country_code' => 'KR',
        ]);
        $pendingUser->assignRole('user');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $pendingUser->id,
            'role_name' => 'user',
            'invitation_status' => 'pending',
            'invited_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/organizations/' . $this->organization->id . '/admin/members')
                    // 총 멤버 수 확인 (관리자 + 활성 + 대기)
                    ->assertSeeIn('.member-management-content', '3명')
                    ->assertSee('Active User')
                    ->assertSee('Pending User');
        });
    }
}