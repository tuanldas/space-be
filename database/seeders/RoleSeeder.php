<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Bouncer;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo vai trò quản trị viên (nếu chưa có)
        $adminRole = Bouncer::role()->firstOrCreate(
            ['name' => 'admin'],
            ['title' => 'Quản trị viên']
        );

        // Tạo vai trò người dùng thông thường (nếu chưa có)
        $userRole = Bouncer::role()->firstOrCreate(
            ['name' => 'user'],
            ['title' => 'Người dùng']
        );

        // Cấp tất cả quyền cho vai trò quản trị viên
        Bouncer::allow($adminRole)->everything();

        // Cấp quyền cơ bản cho vai trò người dùng
        Bouncer::allow($userRole)->to('view-users');

        // Tìm user id=1 (nếu có) và gán vai trò quản trị
        $adminUser = User::find(1);
        if ($adminUser) {
            Bouncer::assign($adminRole)->to($adminUser);
            $this->command->info("Đã gán vai trò {$adminRole->title} cho user có ID 1");
        } else {
            $this->command->warn('Không tìm thấy user có ID 1 để gán vai trò quản trị');
        }

        // Tạo thêm vai trò biên tập viên
        $editorRole = Bouncer::role()->firstOrCreate(
            ['name' => 'editor'],
            ['title' => 'Biên tập viên']
        );

        // Cấp quyền cho biên tập viên
        Bouncer::allow($editorRole)->to('view-users');
        Bouncer::allow($editorRole)->to('update-users');

        $this->command->info('Đã khởi tạo các vai trò cơ bản cho hệ thống');
    }
}
