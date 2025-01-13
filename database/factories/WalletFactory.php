<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use PHPUnit\TextUI\XmlConfiguration\MigrationException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $files = Storage::disk('local')->files('server/wallets/icons');

        $images = array_filter($files, function ($file) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
        });
        if (!empty($images)) {
            $randomImage = $images[array_rand($images)];
            return [
                'name' => $this->faker->name(),
                'icon' => $randomImage,
                'balance' => $this->faker->randomFloat(2, 0, 1000000),
                'currency' => $this->faker->randomElement(['VND', 'USD', 'EUR']),
                'type' => $this->faker->randomElement(['personal']),
                'created_by' => \App\Models\User::factory(),
            ];
        } else {
            throw new MigrationException('Không tìm thấy ảnh nào trong thư mục wallets/icons.');
        }
    }
}
