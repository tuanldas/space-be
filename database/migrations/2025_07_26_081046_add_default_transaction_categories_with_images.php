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
        $adminUser = User::firstOrCreate(
            ['email' => 'tuanldas@gmail.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('123123'),
                'email_verified_at' => now(),
            ]
        );

        TransactionCategory::where('is_default', true)->delete();

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
        TransactionCategory::where('is_default', true)->delete();
    }

    /**
     * Tạo danh mục giao dịch với hình ảnh
     */
    private function createCategoryWithImage(string $name, string $type, string $imagePath, int $userId): void
    {
        $category = TransactionCategory::create([
            'name' => $name,
            'description' => "Danh mục $name",
            'type' => $type,
            'user_id' => null,
            'is_default' => true,
        ]);

        $path = "transaction-categories/$imagePath";

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
