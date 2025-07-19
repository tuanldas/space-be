<?php

namespace App\Enums;

enum ImageCategoryType: string
{
    case CATEGORY_IMAGE = 'category_image';
    case USER_AVATAR = 'user_avatar';
    case WALLET_ICON = 'wallet_icon';
    
    /**
     * Lấy danh sách tất cả các giá trị được chấp nhận
     *
     * @return array
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    /**
     * Kiểm tra xem loại hình ảnh có hợp lệ không
     *
     * @param string $type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::getValues());
    }
} 