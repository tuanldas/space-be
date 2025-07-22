<?php

namespace Database\Seeders;

use App\Models\TransactionCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Income categories
        $this->createCategory('Lương', 'Thu nhập từ lương', 'income');
        $this->createCategory('Thưởng', 'Thu nhập từ tiền thưởng', 'income');
        $this->createCategory('Quà tặng', 'Thu nhập từ quà tặng', 'income');
        $this->createCategory('Đầu tư', 'Thu nhập từ các khoản đầu tư', 'income');
        $this->createCategory('Khác', 'Các khoản thu nhập khác', 'income');

        // Expense categories
        $this->createCategory('Ăn uống', 'Chi tiêu cho ăn uống, nhà hàng', 'expense');
        $this->createCategory('Đi lại', 'Chi tiêu cho di chuyển, xăng xe', 'expense');
        $this->createCategory('Mua sắm', 'Chi tiêu cho mua sắm đồ dùng', 'expense');
        $this->createCategory('Tiền nhà', 'Chi tiêu cho thuê nhà, điện nước', 'expense');
        $this->createCategory('Y tế', 'Chi tiêu cho y tế, thuốc men', 'expense');
        $this->createCategory('Giáo dục', 'Chi tiêu cho học tập, sách vở', 'expense');
        $this->createCategory('Giải trí', 'Chi tiêu cho giải trí, du lịch', 'expense');
        $this->createCategory('Khác', 'Các khoản chi tiêu khác', 'expense');

        // Transfer categories
        $this->createCategory('Chuyển tiền', 'Chuyển tiền giữa các tài khoản', 'transfer');
        $this->createCategory('Rút tiền', 'Rút tiền từ tài khoản', 'transfer');
        $this->createCategory('Nạp tiền', 'Nạp tiền vào tài khoản', 'transfer');
    }

    /**
     * Create a category
     */
    private function createCategory(string $name, string $description, string $type): void
    {
        TransactionCategory::create([
            'id' => Str::uuid(),
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'user_id' => null // System default category
        ]);
    }
}
