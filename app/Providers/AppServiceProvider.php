<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\OutgoingLetter;
use App\Observers\OutgoingLetterObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        OutgoingLetter::observe(OutgoingLetterObserver::class);
    }
}
