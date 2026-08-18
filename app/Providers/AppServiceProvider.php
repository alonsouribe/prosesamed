<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Domain\Venta\VentaRepositoryInterface;
use App\Infrastructure\Persistence\EloquentVentaRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VentaRepositoryInterface::class, EloquentVentaRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
