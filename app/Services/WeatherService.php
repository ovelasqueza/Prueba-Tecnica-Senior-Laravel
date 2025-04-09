<?php

namespace App\Services;

use App\Repositories\Interfaces\FavoriteCityRepositoryInterface;
use App\Repositories\Interfaces\SearchHistoryRepositoryInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class WeatherService
{
    protected $apiKey;
    protected $baseUrl = 'http://api.weatherapi.com/v1';
    protected $favoriteCityRepository;
    protected $searchHistoryRepository;
    protected $countryCodes = [
        'Colombia' => 'CO',
        'United States' => 'US',
        'Mexico' => 'MX',
        'Brazil' => 'BR',
        'Argentina' => 'AR',
        'Chile' => 'CL',
        'Peru' => 'PE',
        'Ecuador' => 'EC',
        'Venezuela' => 'VE',
        'Spain' => 'ES'
    ];

    public function __construct(
        FavoriteCityRepositoryInterface $favoriteCityRepository,
        SearchHistoryRepositoryInterface $searchHistoryRepository
    ) {
        $this->apiKey = config('services.weather.key');
        $this->favoriteCityRepository = $favoriteCityRepository;
        $this->searchHistoryRepository = $searchHistoryRepository;
    }

    public function getCurrentWeather(string $city): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('La clave de API del clima no está configurada. Por favor, configure WEATHER_API_KEY en el archivo .env');
        }

        try {
            $response = Http::get("{$this->baseUrl}/current.json", [
                'key' => $this->apiKey,
                'q' => $city,
                'aqi' => 'no'
            ]);

            if ($response->status() === 401) {
                throw new \Exception('La clave de API del clima es inválida');
            }

            if ($response->status() === 404) {
                throw new \Exception('No se encontró la ciudad especificada');
            }

            $response->throw();
            return $response->json();
        } catch (RequestException $e) {
            Log::error('Error en la API del clima: ' . $e->getMessage());
            throw new \Exception('Error al obtener datos del clima: ' . $e->getMessage());
        }
    }

    public function saveSearchHistory(int $userId, array $weatherData): void
    {
        $countryCode = $weatherData['location']['country'];
        if (strlen($countryCode) > 2) {
            $countryCode = substr($countryCode, 0, 2);
        }

        $this->searchHistoryRepository->create([
            'user_id' => $userId,
            'city_name' => $weatherData['location']['name'],
            'country_code' => $countryCode,
            'temperature' => $weatherData['current']['temp_c'],
            'weather_condition' => $weatherData['current']['condition']['text'],
            'wind_speed' => $weatherData['current']['wind_kph'],
            'humidity' => $weatherData['current']['humidity'],
            'local_time' => $weatherData['location']['localtime']
        ]);
    }

    public function addToFavorites(int $userId, array $weatherData): bool
    {
        try {
            if (!isset($weatherData['location']['name'], $weatherData['location']['country'], $weatherData['location']['lat'], $weatherData['location']['lon'])) {
                throw new \InvalidArgumentException('Los datos del clima no contienen la información necesaria.');
            }
    
            $cityName = $weatherData['location']['name'];
            $countryName = $weatherData['location']['country'];
    
            $countryCode = Arr::get($this->countryCodes, $countryName, substr($countryName, 0, 2));
    
            if ($this->favoriteCityRepository->exists($userId, $cityName, $countryCode)) {
                return false;
            }
    
            $this->favoriteCityRepository->create([
                'user_id' => $userId,
                'city_name' => $cityName,
                'country_code' => $countryCode,
                'latitude' => $weatherData['location']['lat'],
                'longitude' => $weatherData['location']['lon']
            ]);
    
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al agregar ciudad a favoritos: ' . $e->getMessage());
            return false;
        }
    }

    public function removeFromFavorites(int $favoriteId): bool
    {
        return $this->favoriteCityRepository->delete($favoriteId);
    }

    public function getFavoriteCities(int $userId): array
    {
        return $this->favoriteCityRepository->getAllByUserId($userId)->toArray();
    }

    public function getSearchHistory(int $userId, int $limit = 10): array
    {
        return $this->searchHistoryRepository->getByUserId($userId, $limit)->toArray();
    }
}