<?php

namespace App\Providers;

use App\Repositories\FavoriteCityRepository;
use App\Repositories\Interfaces\FavoriteCityRepositoryInterface;
use App\Repositories\SearchHistoryRepository;
use App\Repositories\Interfaces\SearchHistoryRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(FavoriteCityRepositoryInterface::class, FavoriteCityRepository::class);
        $this->app->bind(SearchHistoryRepositoryInterface::class, SearchHistoryRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
