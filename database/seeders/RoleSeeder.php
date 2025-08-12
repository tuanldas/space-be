<?php

namespace Database\Seeders;

use App\Enums\AbilityType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Bouncer::role()->firstOrCreate(
            ['name' => 'admin'],
            ['title' => 'Quản trị viên']
        );

        $userRole = Bouncer::role()->firstOrCreate(
            ['name' => 'user'],
            ['title' => 'Người dùng']
        );

        foreach (AbilityType::cases() as $ability) {
            Bouncer::allow($adminRole)->to($ability->value);
        }

        Bouncer::allow($userRole)->to(AbilityType::VIEW_USERS->value);
        Bouncer::allow($userRole)->to(AbilityType::VIEW_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($userRole)->to(AbilityType::CREATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($userRole)->to(AbilityType::UPDATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($userRole)->to(AbilityType::DELETE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($userRole)->to(AbilityType::RESTORE_TRANSACTION_CATEGORIES->value);

        $adminUser = User::find(1);
        if ($adminUser) {
            Bouncer::assign($adminRole)->to($adminUser);
            $this->command->info("Đã gán vai trò {$adminRole->title} cho user có ID 1");
        } else {
            $this->command->warn('Không tìm thấy user có ID 1 để gán vai trò quản trị');
        }

        $editorRole = Bouncer::role()->firstOrCreate(
            ['name' => 'editor'],
            ['title' => 'Biên tập viên']
        );

        Bouncer::allow($editorRole)->to(AbilityType::VIEW_USERS->value);
        Bouncer::allow($editorRole)->to(AbilityType::UPDATE_USERS->value);
        Bouncer::allow($editorRole)->to(AbilityType::VIEW_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($editorRole)->to(AbilityType::UPDATE_TRANSACTION_CATEGORIES->value);
        Bouncer::allow($editorRole)->to(AbilityType::MANAGE_DEFAULT_TRANSACTION_CATEGORIES->value);

        $this->command->info('Đã khởi tạo các vai trò cơ bản cho hệ thống');
    }
}
