<?php

namespace App\Repositories;

use App\Models\FavoriteCity;
use App\Repositories\Interfaces\FavoriteCityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FavoriteCityRepository implements FavoriteCityRepositoryInterface
{
    protected $model;

    public function __construct(FavoriteCity $model)
    {
        $this->model = $model;
    }

    public function getAllByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function create(array $data): FavoriteCity
    {
        return $this->model->create($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function exists(int $userId, string $cityName, string $countryCode): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('city_name', $cityName)
            ->where('country_code', $countryCode)
            ->exists();
    }
}