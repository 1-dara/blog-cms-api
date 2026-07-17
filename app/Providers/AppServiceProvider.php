<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        if (!defined('L5_SWAGGER_CONST_HOST')) {
            define('L5_SWAGGER_CONST_HOST', config('app.url'));
        }
    }
}
