<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Message Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for various messages that we need to
    | display to the user. You are free to modify these language lines according
    | to your application's requirements.
    |
    */

    'success' => 'Thao tác hoàn thành thành công.',
    'error' => 'Đã xảy ra lỗi khi xử lý yêu cầu của bạn.',
    'not_found' => 'Không tìm thấy tài nguyên được yêu cầu.',
    'invalid_credentials' => 'Thông tin đăng nhập không hợp lệ.',
    'unauthorized' => 'Bạn không có quyền thực hiện hành động này.',
    'category' => [
        'created' => 'Danh mục được tạo thành công.',
        'updated' => 'Danh mục được cập nhật thành công.',
        'deleted' => 'Danh mục được xóa thành công.',
        'restored' => 'Danh mục được khôi phục thành công.',
        'force_deleted' => 'Danh mục đã bị xóa vĩnh viễn.',
        'not_found' => 'Không tìm thấy danh mục.',
        'not_found_in_trash' => 'Không tìm thấy danh mục trong thùng rác.',
        'cannot_modify_default' => 'Danh mục mặc định không thể được chỉnh sửa.',
        'cannot_delete_default' => 'Danh mục mặc định không thể bị xóa.',
    ],
    'permission' => [
        // User Management
        'view_users_denied' => 'Bạn không có quyền xem danh sách người dùng.',
        'view_user_denied' => 'Bạn không có quyền xem thông tin người dùng.',
        'create_users_denied' => 'Bạn không có quyền tạo người dùng mới.',
        'update_users_denied' => 'Bạn không có quyền cập nhật thông tin người dùng.',
        'delete_users_denied' => 'Bạn không có quyền xóa người dùng.',
        
        // Role Management
        'manage_roles_denied' => 'Bạn không có quyền quản lý vai trò.',
        'manage_user_roles_denied' => 'Bạn không có quyền quản lý vai trò người dùng.',
        
        // Transaction Categories
        'view_categories_denied' => 'Bạn không có quyền xem danh sách danh mục giao dịch.',
        'view_category_details_denied' => 'Bạn không có quyền xem chi tiết danh mục giao dịch.',
        'view_trashed_categories_denied' => 'Bạn không có quyền xem danh sách danh mục giao dịch đã xóa.',
        'create_categories_denied' => 'Bạn không có quyền tạo danh mục giao dịch mới.',
        'update_categories_denied' => 'Bạn không có quyền cập nhật danh mục giao dịch.',
        'delete_categories_denied' => 'Bạn không có quyền xóa danh mục giao dịch.',
        'restore_categories_denied' => 'Bạn không có quyền khôi phục danh mục giao dịch đã xóa.',
        'force_delete_categories_denied' => 'Bạn không có quyền xóa vĩnh viễn danh mục giao dịch.',
    ],
    'user' => [
        'not_found' => 'Không tìm thấy người dùng.',
        'deleted' => 'Đã xóa người dùng thành công.',
    ],
    'role' => [
        'not_found' => 'Vai trò không tồn tại.',
        'deleted' => 'Đã xóa vai trò thành công.',
        'assigned' => 'Đã gán vai trò thành công.',
        'removed' => 'Đã thu hồi vai trò thành công.',
    ],
]; 