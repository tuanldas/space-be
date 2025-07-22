<?php

namespace App\Enums;

enum AllowedImageTypes: string
{
    case JPEG = 'jpeg';
    case JPG = 'jpg';
    case PNG = 'png';
    case GIF = 'gif';
    
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
     * Lấy giá trị cho quy tắc xác thực mimes
     *
     * @return string
     */
    public static function getMimeValidationString(): string
    {
        return implode(',', self::getValues());
    }
    
    /**
     * Kiểm tra xem phần mở rộng của file có được chấp nhận không
     *
     * @param string $extension
     * @return bool
     */
    public static function isValid(string $extension): bool
    {
        return in_array(strtolower($extension), self::getValues());
    }
} 