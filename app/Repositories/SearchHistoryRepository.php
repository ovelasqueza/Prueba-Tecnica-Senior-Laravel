<?php

namespace App\Repositories;

use App\Models\SearchHistory;
use App\Repositories\Interfaces\SearchHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SearchHistoryRepository implements SearchHistoryRepositoryInterface
{
    protected $model;

    public function __construct(SearchHistory $model)
    {
        $this->model = $model;
    }

    public function getByUserId(int $userId, int $limit = 10): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function create(array $data): SearchHistory
    {
        return $this->model->create($data);
    }

    public function clearHistory(int $userId): bool
    {
        return $this->model->where('user_id', $userId)->delete();
    }

    public function getLatestSearch(int $userId, string $cityName, string $countryCode): ?SearchHistory
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('city_name', $cityName)
            ->where('country_code', $countryCode)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}