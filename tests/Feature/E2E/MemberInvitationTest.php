<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Mail\OrganizationInvitationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MemberInvitationTest extends TestCase
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
    public function admin_can_access_member_management_page()
    {
        Auth::login($this->admin);

        $response = $this->get('/organizations/' . $this->organization->id . '/admin/members');

        $response->assertStatus(200);
        $response->assertSee('조직 멤버');
        $response->assertSee('멤버 초대');
    }

    /** @test */
    public function admin_can_invite_new_member_with_email_sending()
    {
        Mail::fake();
        Auth::login($this->admin);

        $inviteEmail = 'newmember@example.com';
        $inviteRole = 'user';

        // Livewire 컴포넌트 테스트
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('openInviteModal')
        ->assertSet('showInviteModal', true)
        ->set('inviteEmail', $inviteEmail)
        ->set('inviteRole', $inviteRole)
        ->call('inviteMember')
        ->assertSet('showInviteModal', false)
        ->assertDispatched('memberInvited');

        // 사용자가 생성되었는지 확인
        $this->assertDatabaseHas('users', [
            'email' => $inviteEmail,
        ]);

        // 조직 멤버가 생성되었는지 확인
        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $this->organization->id,
            'role_name' => $inviteRole,
            'invitation_status' => 'pending',
        ]);

        // 이메일이 전송되었는지 확인
        Mail::assertSent(OrganizationInvitationMail::class, function ($mail) use ($inviteEmail) {
            return $mail->hasTo($inviteEmail);
        });
    }

    /** @test */
    public function admin_can_resend_invitation_email()
    {
        Mail::fake();
        Auth::login($this->admin);

        // 대기 중인 멤버 생성
        $invitedUser = User::create([
            'name' => 'Invited User',
            'email' => 'invited@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Invited',
            'country_code' => 'KR',
        ]);

        $member = OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $invitedUser->id,
            'role_name' => 'user',
            'invitation_status' => 'pending',
            'invited_at' => now()->subDay(), // 하루 전 초대
        ]);

        // 초대 재전송 테스트
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('resendInvitation', $member->id)
        ->assertDispatched('invitationResent');

        // 이메일이 재전송되었는지 확인
        Mail::assertSent(OrganizationInvitationMail::class, function ($mail) use ($invitedUser) {
            return $mail->hasTo($invitedUser->email);
        });

        // invited_at이 업데이트되었는지 확인
        $member->refresh();
        $this->assertTrue($member->invited_at->isAfter(now()->subHour()));
    }

    /** @test */
    public function admin_can_remove_member()
    {
        Auth::login($this->admin);

        // 일반 멤버 생성
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Regular',
            'country_code' => 'KR',
        ]);
        $regularUser->assignRole('user');

        $member = OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $regularUser->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        // 멤버 제거 테스트
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('removeMember', $member->id)
        ->assertDispatched('memberRemoved');

        // 데이터베이스에서 삭제되었는지 확인
        $this->assertDatabaseMissing('organization_members', [
            'id' => $member->id,
        ]);
    }

    /** @test */
    public function admin_can_change_member_role()
    {
        Auth::login($this->admin);

        // 일반 멤버 생성
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Regular',
            'country_code' => 'KR',
        ]);
        $regularUser->assignRole('user');

        $member = OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $regularUser->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        // 역할 변경 테스트
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('changeRole', $member->id, 'organization_admin')
        ->assertDispatched('permissionChanged');

        // 사용자의 역할이 변경되었는지 확인
        $regularUser->refresh();
        $this->assertTrue($regularUser->hasRole('organization_admin'));

        // 조직 멤버의 역할도 업데이트되었는지 확인
        $member->refresh();
        $this->assertEquals('organization_admin', $member->role_name);
    }

    /** @test */
    public function owner_role_cannot_be_removed_or_demoted()
    {
        Auth::login($this->admin);

        // 조직 소유자 생성
        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Owner',
            'country_code' => 'KR',
        ]);
        $owner->assignRole('organization_owner');

        $ownerMember = OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $owner->id,
            'role_name' => 'organization_owner',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        // 소유자 제거 시도 - 실패해야 함
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('removeMember', $ownerMember->id)
        ->assertDispatched('error');

        // 여전히 데이터베이스에 존재하는지 확인
        $this->assertDatabaseHas('organization_members', [
            'id' => $ownerMember->id,
        ]);

        // 소유자 역할 변경 시도 - 실패해야 함
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('changeRole', $ownerMember->id, 'user')
        ->assertDispatched('error');

        // 여전히 소유자 역할을 가지고 있는지 확인
        $owner->refresh();
        $this->assertTrue($owner->hasRole('organization_owner'));
    }

    /** @test */
    public function cannot_invite_existing_member()
    {
        Auth::login($this->admin);

        $existingEmail = $this->admin->email;

        // 이미 존재하는 멤버 초대 시도
        Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ])
        ->call('openInviteModal')
        ->set('inviteEmail', $existingEmail)
        ->set('inviteRole', 'user')
        ->call('inviteMember')
        ->assertDispatched('error');
    }

    /** @test */
    public function search_and_filter_functionality_works()
    {
        Auth::login($this->admin);

        // 추가 테스트 멤버들 생성
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'John',
            'country_code' => 'KR',
        ]);
        $user1->assignRole('user');

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Jane',
            'country_code' => 'KR',
        ]);
        $user2->assignRole('organization_admin');

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user1->id,
            'role_name' => 'user',
            'invitation_status' => 'accepted',
            'joined_at' => now(),
        ]);

        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $user2->id,
            'role_name' => 'organization_admin',
            'invitation_status' => 'pending',
            'invited_at' => now(),
        ]);

        // 검색 기능 테스트
        $component = Livewire::test('organization.admin.member-management', [
            'organizationId' => $this->organization->id
        ]);

        // 이름으로 검색
        $component->set('searchTerm', 'John');
        $filteredMembers = $component->get('filteredMembers');
        $this->assertCount(1, $filteredMembers);
        $this->assertEquals('john@example.com', $filteredMembers[0]['email']);

        // 역할로 필터링
        $component->set('searchTerm', '')
                 ->set('permissionFilter', 'user');
        $filteredMembers = $component->get('filteredMembers');
        $userMembers = array_filter($filteredMembers, fn($member) => $member['role'] === 'user');
        $this->assertGreaterThan(0, count($userMembers));

        // 상태로 필터링
        $component->set('permissionFilter', '')
                 ->set('statusFilter', 'pending');
        $filteredMembers = $component->get('filteredMembers');
        $pendingMembers = array_filter($filteredMembers, fn($member) => $member['status'] === 'pending');
        $this->assertGreaterThan(0, count($pendingMembers));
    }
}