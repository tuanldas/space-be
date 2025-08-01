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

    'success' => 'Operation completed successfully.',
    'error' => 'An error occurred while processing your request.',
    'not_found' => 'The requested resource could not be found.',
    'invalid_credentials' => 'Invalid credentials provided.',
    'unauthorized' => 'You are not authorized to perform this action.',
    'category' => [
        'created' => 'Category created successfully.',
        'updated' => 'Category updated successfully.',
        'deleted' => 'Category deleted successfully.',
        'restored' => 'Category restored successfully.',
        'force_deleted' => 'Category permanently deleted.',
        'not_found' => 'Category not found.',
        'not_found_in_trash' => 'Category not found in trash.',
        'cannot_modify_default' => 'Default categories cannot be modified.',
        'cannot_delete_default' => 'Default categories cannot be deleted.',
    ],
    'permission' => [
        // User Management
        'view_users_denied' => 'You do not have permission to view the user list.',
        'view_user_denied' => 'You do not have permission to view user details.',
        'create_users_denied' => 'You do not have permission to create new users.',
        'update_users_denied' => 'You do not have permission to update users.',
        'delete_users_denied' => 'You do not have permission to delete users.',
        
        // Role Management
        'manage_roles_denied' => 'You do not have permission to manage roles.',
        'manage_user_roles_denied' => 'You do not have permission to manage user roles.',
        
        // Transaction Categories
        'view_categories_denied' => 'You do not have permission to view transaction categories.',
        'view_category_details_denied' => 'You do not have permission to view transaction category details.',
        'view_trashed_categories_denied' => 'You do not have permission to view deleted transaction categories.',
        'create_categories_denied' => 'You do not have permission to create new transaction categories.',
        'update_categories_denied' => 'You do not have permission to update transaction categories.',
        'delete_categories_denied' => 'You do not have permission to delete transaction categories.',
        'restore_categories_denied' => 'You do not have permission to restore deleted transaction categories.',
        'force_delete_categories_denied' => 'You do not have permission to permanently delete transaction categories.',
    ],
    'user' => [
        'not_found' => 'User not found.',
        'deleted' => 'User deleted successfully.',
    ],
    'role' => [
        'not_found' => 'Role not found.',
        'deleted' => 'Role deleted successfully.',
        'assigned' => 'Role assigned successfully.',
        'removed' => 'Role removed successfully.',
    ],
]; 