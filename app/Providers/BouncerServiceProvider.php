<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Silber\Bouncer\Bouncer;
use Silber\Bouncer\BouncerFacade;
use Illuminate\Support\Facades\Schema;

class BouncerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Cấu hình Bouncer sử dụng cache
        BouncerFacade::cache();

        // Chỉ định nghĩa permissions khi bảng abilities đã tồn tại
        if (Schema::hasTable('abilities')) {
            $this->defineAbilities();
        }
    }
    
    /**
     * Định nghĩa các khả năng (abilities) cho hệ thống.
     */
    protected function defineAbilities(): void
    {
        // User Management
        BouncerFacade::ability()->firstOrCreate(
            ['name' => 'view-users'],
            ['title' => 'Xem danh sách người dùng']
        );

        BouncerFacade::ability()->firstOrCreate(
            ['name' => 'create-users'],
            ['title' => 'Tạo người dùng mới']
        );

        BouncerFacade::ability()->firstOrCreate(
            ['name' => 'update-users'],
            ['title' => 'Cập nhật thông tin người dùng']
        );

        BouncerFacade::ability()->firstOrCreate(
            ['name' => 'delete-users'],
            ['title' => 'Xóa người dùng']
        );

        // Role Management
        BouncerFacade::ability()->firstOrCreate(
            ['name' => 'manage-roles'],
            ['title' => 'Quản lý vai trò']
        );

        // Settings
        BouncerFacade::ability()->firstOrCreate(
            ['name' => 'manage-settings'],
            ['title' => 'Quản lý cài đặt hệ thống']
        );
    }
}
