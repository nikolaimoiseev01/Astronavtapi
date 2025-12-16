<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application Services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap any application Services.
     */
    public function boot(): void
    {
        Model::unguard();
        RedirectIfAuthenticated::redirectUsing(function () {
            return route('account.settings');
        });
    }
}
