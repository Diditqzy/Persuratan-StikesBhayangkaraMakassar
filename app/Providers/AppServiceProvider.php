<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\OutgoingLetter;
use App\Models\IncomingDisposition;
use App\Observers\OutgoingLetterObserver;
use App\Observers\IncomingDispositionObserver;

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
        IncomingDisposition::observe(IncomingDispositionObserver::class);
    }
}
