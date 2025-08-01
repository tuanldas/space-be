<?php

namespace App\Providers;

use App\Enums\AbilityType;
use Illuminate\Support\ServiceProvider;
use Silber\Bouncer\BouncerFacade;
use Illuminate\Support\Facades\Schema;

class BouncerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        BouncerFacade::cache();

        if (Schema::hasTable('abilities')) {
            $this->defineAbilities();
        }
    }
    
    /**
     * Định nghĩa các khả năng (abilities) cho hệ thống.
     */
    protected function defineAbilities(): void
    {
        foreach (AbilityType::getAllAbilities() as $ability) {
            BouncerFacade::ability()->firstOrCreate(
                ['name' => $ability['name']],
                ['title' => $ability['title']]
            );
        }
    }
}
