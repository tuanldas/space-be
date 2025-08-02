<?php

namespace Tests;

use App\Enums\AbilityType;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;
use Silber\Bouncer\BouncerFacade as Bouncer;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        Passport::useClientModel(\Laravel\Passport\Client::class);
    }
    
    /**
     * Setup cơ bản với việc seed dữ liệu
     */
    protected function setupBase(): void
    {
        $this->seed();
    }

    /**
     * Setup admin user và gán quyền admin
     */
    protected function setupAdmin(): User
    {
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User'
        ]);
        
        Bouncer::role()->firstOrCreate(['name' => 'admin'], ['title' => 'Quản trị viên']);
        Bouncer::allow('admin')->everything();
        Bouncer::assign('admin')->to($this->admin);
        Bouncer::refresh();
        
        return $this->admin;
    }

    /**
     * Login với user được chỉ định
     */
    protected function actAsUser(User $user): void
    {
        Passport::actingAs($user);
    }

    /**
     * Login với admin
     */
    protected function actAsAdmin(): void
    {
        if (!isset($this->admin)) {
            $this->setupAdmin();
        }
        
        $this->actAsUser($this->admin);
    }

    /**
     * Cấp quyền cho user
     */
    protected function grantPermissions(User $user, array $abilities): void
    {
        foreach ($abilities as $ability) {
            Bouncer::allow($user)->to($ability);
        }
        
        Bouncer::refresh();
    }

    /**
     * Tạo một user mới và cấp quyền
     */
    protected function createUserWithAbilities(array $abilities, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->grantPermissions($user, $abilities);
        
        return $user;
    }
}
