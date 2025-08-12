<?php

namespace App\Enums;

enum TransactionType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    
    /**
     * Lấy danh sách các loại giao dịch dưới dạng mảng.
     *
     * @return array
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    /**
     * Lấy các loại giao dịch dưới dạng chuỗi phân cách bởi dấu phẩy.
     * Hữu ích cho validation rules.
     *
     * @return string
     */
    public static function getValuesString(): string
    {
        return implode(',', self::getValues());
    }
}
