<?php

namespace App\Repositories\Interfaces;

use App\Models\FavoriteCity;
use Illuminate\Database\Eloquent\Collection;

interface FavoriteCityRepositoryInterface
{
    /**
     * Get all favorite cities for a user
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllByUserId(int $userId): Collection;

    /**
     * Add a new favorite city
     *
     * @param array $data
     * @return FavoriteCity
     */
    public function create(array $data): FavoriteCity;

    /**
     * Remove a favorite city
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Check if a city is already favorited by user
     *
     * @param int $userId
     * @param string $cityName
     * @param string $countryCode
     * @return bool
     */
    public function exists(int $userId, string $cityName, string $countryCode): bool;
}