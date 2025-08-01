<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        Bouncer::ability()->firstOrCreate(['name' => 'view-users'], ['title' => 'Xem danh sách người dùng']);
        Bouncer::ability()->firstOrCreate(['name' => 'create-users'], ['title' => 'Tạo người dùng mới']);
        Bouncer::ability()->firstOrCreate(['name' => 'update-users'], ['title' => 'Cập nhật thông tin người dùng']);
        Bouncer::ability()->firstOrCreate(['name' => 'delete-users'], ['title' => 'Xóa người dùng']);
        Bouncer::ability()->firstOrCreate(['name' => 'manage-roles'], ['title' => 'Quản lý vai trò']);

        Bouncer::role()->firstOrCreate(['name' => 'admin'], ['title' => 'Quản trị viên']);
        Bouncer::role()->firstOrCreate(['name' => 'user'], ['title' => 'Người dùng']);

        Bouncer::allow('admin')->everything();
        Bouncer::allow('user')->to('view-users');
    }

    /** @test */
    public function admin_can_view_all_roles()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Bouncer::assign('admin')->to($admin);

        Passport::actingAs($admin);

        $response = $this->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /** @test */
    public function normal_user_cannot_view_roles()
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        Bouncer::assign('user')->to($user);

        Passport::actingAs($user);

        $response = $this->getJson('/api/roles');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_role()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Bouncer::assign('admin')->to($admin);

        Passport::actingAs($admin);

        $response = $this->postJson('/api/roles', [
            'name' => 'editor',
            'title' => 'Biên tập viên',
            'abilities' => ['view-users', 'update-users']
        ]);

        $response->assertStatus(201)
            ->assertJson(['name' => 'editor', 'title' => 'Biên tập viên']);

        $this->assertTrue(Bouncer::role()->where('name', 'editor')->exists());
        $editorRole = Bouncer::role()->where('name', 'editor')->first();
        $this->assertNotNull($editorRole);
        
        $viewUsersAbility = Bouncer::ability()->where('name', 'view-users')->first();
        $updateUsersAbility = Bouncer::ability()->where('name', 'update-users')->first();
        
        $this->assertDatabaseHas('permissions', [
            'entity_type' => 'roles',
            'entity_id' => $editorRole->id,
            'ability_id' => $viewUsersAbility->id,
        ]);
        
        $this->assertDatabaseHas('permissions', [
            'entity_type' => 'roles',
            'entity_id' => $editorRole->id,
            'ability_id' => $updateUsersAbility->id,
        ]);
    }

    /** @test */
    public function admin_can_assign_role_to_user()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Bouncer::assign('admin')->to($admin);

        $user = User::factory()->create(['email' => 'user@example.com']);

        Passport::actingAs($admin);

        $response = $this->postJson('/api/users/assign-role', [
            'user_id' => $user->id,
            'role' => 'admin'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Đã gán vai trò thành công.']);

        $this->assertTrue($user->isAn('admin'));
    }

    /** @test */
    public function admin_can_remove_role_from_user()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Bouncer::assign('admin')->to($admin);

        $user = User::factory()->create(['email' => 'user@example.com']);
        Bouncer::assign('admin')->to($user);

        Passport::actingAs($admin);

        $response = $this->postJson('/api/users/remove-role', [
            'user_id' => $user->id,
            'role' => 'admin'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Đã thu hồi vai trò thành công.']);

        $this->assertFalse($user->fresh()->isAn('admin'));
    }

    /** @test */
    public function user_permissions_are_returned_when_logged_in()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Bouncer::assign('admin')->to($admin);

        Passport::actingAs($admin);
        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'roles',
                'abilities'
            ]);

        $this->assertTrue(in_array('admin', $response->json('roles')));
    }
}
