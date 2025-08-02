<?php

namespace Tests\Feature\Api;

use App\Enums\AbilityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        Bouncer::role()->firstOrCreate(['name' => 'user']);
        
        Bouncer::ability()->firstOrCreate(['name' => AbilityType::VIEW_USERS->value]);
        Bouncer::ability()->firstOrCreate(['name' => AbilityType::CREATE_USERS->value]);
        Bouncer::ability()->firstOrCreate(['name' => AbilityType::UPDATE_USERS->value]);
        Bouncer::ability()->firstOrCreate(['name' => AbilityType::DELETE_USERS->value]);
        Bouncer::ability()->firstOrCreate(['name' => AbilityType::MANAGE_ROLES->value]);
        
        Bouncer::allow('admin')->everything();
        
        Bouncer::allow('user')->to(AbilityType::VIEW_USERS->value);
    }

    public function test_admin_can_view_all_roles()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        Bouncer::assign('admin')->to($admin);
        Passport::actingAs($admin);
        
        $response = $this->getJson('/api/roles');
        
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }

    public function test_normal_user_cannot_view_roles()
    {
        $user = User::factory()->create(['email' => 'normal_user@example.com']);
        Bouncer::assign('user')->to($user);
        Passport::actingAs($user);
        
        $response = $this->getJson('/api/roles');
        
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_role()
    {
        $admin = User::factory()->create(['email' => 'admin_create@example.com']);
        Bouncer::assign('admin')->to($admin);
        Passport::actingAs($admin);
        
        $roleName = 'editor_' . time();
        $response = $this->postJson('/api/roles', [
            'name' => $roleName,
            'abilities' => [AbilityType::VIEW_USERS->value, AbilityType::UPDATE_USERS->value],
            'title' => 'Editor',
            'description' => 'Can edit content',
        ]);
        
        $response->assertStatus(201);
        $this->assertEquals($roleName, $response->json('name'));
        
        $this->assertTrue(Bouncer::role()->where('name', $roleName)->exists());
        $editorRole = Bouncer::role()->where('name', $roleName)->first();
        $this->assertNotNull($editorRole);

        $viewUsersAbility = Bouncer::ability()->where('name', AbilityType::VIEW_USERS->value)->first();
        $updateUsersAbility = Bouncer::ability()->where('name', AbilityType::UPDATE_USERS->value)->first();

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

    public function test_admin_can_assign_role_to_user()
    {
        $admin = User::factory()->create(['email' => 'admin_assign@example.com']);
        Bouncer::assign('admin')->to($admin);
        Passport::actingAs($admin);
        
        $user = User::factory()->create(['email' => 'role_assignee@example.com']);
        
        $response = $this->postJson('/api/users/assign-role', [
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => __('messages.role.assigned')
            ]);
        
        $this->assertTrue($user->isAn('admin'));
    }
    
    public function test_admin_can_remove_role_from_user()
    {
        $admin = User::factory()->create(['email' => 'admin_remove@example.com']);
        Bouncer::assign('admin')->to($admin);
        Passport::actingAs($admin);
        
        $user = User::factory()->create(['email' => 'role_removee@example.com']);
        Bouncer::assign('admin')->to($user);
        
        $this->assertTrue($user->isAn('admin'));
        
        $response = $this->postJson('/api/users/remove-role', [
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => __('messages.role.removed')
            ]);
        
        $user->refresh();
        Bouncer::refresh();
        $this->assertFalse($user->isAn('admin'));
    }

    public function test_user_permissions_are_returned_when_logged_in()
    {
        $admin = User::factory()->create(['email' => 'admin_perms@example.com']);
        Bouncer::assign('admin')->to($admin);
        
        Bouncer::allow($admin)->to(AbilityType::MANAGE_ROLES->value);
        
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
        
        $this->assertContains(AbilityType::MANAGE_ROLES->value, $response->json('abilities'));
    }
}
