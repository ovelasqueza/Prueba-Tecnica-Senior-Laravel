<?php

namespace App\Repositories\Interfaces;

use App\Models\SearchHistory;
use Illuminate\Database\Eloquent\Collection;

interface SearchHistoryRepositoryInterface
{
    /**
     * Get search history for a user
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getByUserId(int $userId, int $limit = 10): Collection;

    /**
     * Record a new search
     *
     * @param array $data
     * @return SearchHistory
     */
    public function create(array $data): SearchHistory;

    /**
     * Clear search history for a user
     *
     * @param int $userId
     * @return bool
     */
    public function clearHistory(int $userId): bool;

    /**
     * Get latest search for a city by user
     *
     * @param int $userId
     * @param string $cityName
     * @param string $countryCode
     * @return SearchHistory|null
     */
    public function getLatestSearch(int $userId, string $cityName, string $countryCode): ?SearchHistory;
}