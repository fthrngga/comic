<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ComicDriverInterface;
use App\Drivers\ShinigamiDriver;

class ComicServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ComicDriverInterface::class, ShinigamiDriver::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
