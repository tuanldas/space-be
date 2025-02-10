<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use PHPUnit\TextUI\XmlConfiguration\MigrationException;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', SeederConstants::USER_EMAIL)->first();
        if ($user) {
            Wallet::factory()
                ->count(10)
                ->create([
                    'created_by' => $user->id
                ])
                ->each(function ($wallet) use ($user) {
                    $files = Storage::disk('public')->files('server/wallets/icons');

                    $images = array_filter($files, function ($file) {
                        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
                    });
                    if (!empty($images)) {
                        $randomImage = $images[array_rand($images)];
                        $wallet->icon()->create([
                            'disk' => 'public',
                            'path' => $randomImage
                        ]);
                    } else {
                        throw new MigrationException('Không tìm thấy ảnh nào trong thư mục wallets/icons.');
                    }
                });
        } else {
            Wallet::factory()
                ->count(10)
                ->create()
                ->each(function ($wallet) use ($user) {
                    $files = Storage::disk('public')->files('server/wallets/icons');

                    $images = array_filter($files, function ($file) {
                        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
                    });
                    if (!empty($images)) {
                        $randomImage = $images[array_rand($images)];
                        $wallet->icon()->create([
                            'disk' => 'public',
                            'path' => $randomImage
                        ]);
                    } else {
                        throw new MigrationException('Không tìm thấy ảnh nào trong thư mục wallets/icons.');
                    }
                });
        }
    }
}
