<?php

use App\Enums\ImageCategoryType;
use App\Models\Image;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Ánh xạ giữa tên danh mục và tên tệp tin hình ảnh
     *
     * @var array
     */
    private array $categoryImageMap = [
        'income' => [
            'Lương' => 'work.png',
            'Thưởng' => 'wallet.png',
            'Quà tặng' => 'gift.png',
            'Đầu tư' => 'interest.png',
            'Khác' => 'other.png',
        ],
        'expense' => [
            'Ăn uống' => 'utilities.png',
            'Đi lại' => 'transport.png',
            'Mua sắm' => 'shopping.png',
            'Tiền nhà' => 'home.png',
            'Y tế' => 'heal.png',
            'Giáo dục' => 'education.png',
            'Giải trí' => 'movie.png',
            'Hóa đơn' => 'bill.png',
            'Dịch vụ số' => 'digital-services.png',
            'Bảo hiểm' => 'insurance.png',
            'Thuế' => 'tax.png',
            'Sự kiện' => 'event.png',
            'Sức khỏe' => 'beautify.png',
            'Thú cưng' => 'pets.png',
            'Gia đình' => 'children.png',
            'Xe cộ' => 'car.png',
            'Khác' => 'other.png',
        ],
        'transfer' => [
            'Chuyển tiền' => 'finance.png',
            'Rút tiền' => 'wallet.png',
            'Nạp tiền' => 'finance.png',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lấy user system đầu tiên hoặc tạo mới nếu chưa có
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt(Str::random(16)),
                'email_verified_at' => now(),
            ]
        );

        // Xóa tất cả các danh mục mặc định hiện có (nếu có)
        TransactionCategory::where('is_default', true)->delete();

        // Tạo các danh mục mặc định mới với hình ảnh
        foreach ($this->categoryImageMap as $type => $categories) {
            foreach ($categories as $name => $imagePath) {
                $this->createCategoryWithImage($name, $type, $imagePath, $adminUser->id);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa các danh mục mặc định đã thêm
        TransactionCategory::where('is_default', true)->delete();
    }

    /**
     * Tạo danh mục giao dịch với hình ảnh
     */
    private function createCategoryWithImage(string $name, string $type, string $imagePath, int $userId): void
    {
        // Tạo danh mục giao dịch
        $category = TransactionCategory::create([
            'name' => $name,
            'description' => "Danh mục $name",
            'type' => $type,
            'user_id' => null,
            'is_default' => true,
        ]);

        // Đường dẫn đầy đủ đến file hình ảnh
        $path = "transaction-categories/$imagePath";

        // Tạo bản ghi hình ảnh cho danh mục
        Image::create([
            'user_id' => $userId,
            'disk' => 'public',
            'path' => $path,
            'imageable_type' => TransactionCategory::class,
            'imageable_id' => $category->id,
            'type' => ImageCategoryType::CATEGORY_IMAGE,
        ]);
    }
};
