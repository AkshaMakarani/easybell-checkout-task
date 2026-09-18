<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Common\Pricing\PriceList;

class AppServiceProvider extends ServiceProvider
{
    // Register any application services.
    public function register(): void
    {
        $this->app->singleton(PriceList::class, function () {
            return PriceList::fromJsonFile(storage_path('pricing.json'));
        });
    }

    public function boot(): void
    {
        
    }
}
