<?php

namespace Tests\Unit;

use App\Repositories\Interfaces\FavoriteCityRepositoryInterface;
use App\Repositories\Interfaces\SearchHistoryRepositoryInterface;
use App\Services\WeatherService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Mockery;

class WeatherServiceTest extends TestCase
{
    protected $favoriteCityRepository;
    protected $searchHistoryRepository;
    protected $weatherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->favoriteCityRepository = Mockery::mock(FavoriteCityRepositoryInterface::class);
        $this->searchHistoryRepository = Mockery::mock(SearchHistoryRepositoryInterface::class);

        $this->weatherService = new WeatherService(
            $this->favoriteCityRepository,
            $this->searchHistoryRepository
        );
    }

    public function test_get_current_weather_success()
    {
        $mockResponse = [
            'location' => [
                'name' => 'Madrid',
                'country' => 'Spain',
                'lat' => 40.42,
                'lon' => -3.70,
                'localtime' => '2024-03-15 12:00'
            ],
            'current' => [
                'temp_c' => 20,
                'condition' => ['text' => 'Sunny'],
                'wind_kph' => 15,
                'humidity' => 65
            ]
        ];

        Http::fake([
            'http://api.weatherapi.com/v1/current.json*' => Http::response($mockResponse, 200)
        ]);

        $result = $this->weatherService->getCurrentWeather('Madrid');

        $this->assertEquals($mockResponse, $result);
    }

    public function test_get_current_weather_api_error()
    {
        Http::fake([
            'http://api.weatherapi.com/v1/current.json*' => Http::response(null, 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error al obtener datos del clima');
        $this->weatherService->getCurrentWeather('InvalidCity');
    }

    public function test_save_search_history()
    {
        $userId = 1;
        $weatherData = [
            'location' => [
                'name' => 'Madrid',
                'country' => 'Spain',
                'localtime' => '2024-03-15 12:00'
            ],
            'current' => [
                'temp_c' => 20,
                'condition' => ['text' => 'Sunny'],
                'wind_kph' => 15,
                'humidity' => 65
            ]
        ];

        $this->searchHistoryRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userId) {
                return $data['user_id'] === $userId &&
                       $data['city_name'] === 'Madrid' &&
                       $data['country_code'] === 'Sp' &&
                       $data['temperature'] === 20 &&
                       $data['weather_condition'] === 'Sunny' &&
                       $data['wind_speed'] === 15 &&
                       $data['humidity'] === 65 &&
                       $data['local_time'] === '2024-03-15 12:00';
            }))
            ->andReturn(new \App\Models\SearchHistory());

        $this->weatherService->saveSearchHistory($userId, $weatherData);
    }

    public function test_add_to_favorites_success()
    {
        $userId = 1;
        $weatherData = [
            'location' => [
                'name' => 'Madrid',
                'country' => 'Spain',
                'lat' => 40.42,
                'lon' => -3.70
            ]
        ];

        $this->favoriteCityRepository
            ->shouldReceive('exists')
            ->once()
            ->with($userId, 'Madrid', 'Spain')
            ->andReturn(false);

        $this->favoriteCityRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userId) {
                return $data['user_id'] === $userId &&
                       $data['city_name'] === 'Madrid' &&
                       $data['country_code'] === 'ES' &&
                       $data['latitude'] === 40.42 &&
                       $data['longitude'] === -3.70;
            }))
            ->andReturn(new \App\Models\FavoriteCity());

        $result = $this->weatherService->addToFavorites($userId, $weatherData);
        $this->assertTrue($result);
    }

    public function test_add_to_favorites_already_exists()
    {
        $userId = 1;
        $weatherData = [
            'location' => [
                'name' => 'Madrid',
                'country' => 'Spain'
            ]
        ];

        $this->favoriteCityRepository
            ->shouldReceive('exists')
            ->once()
            ->with($userId, 'Madrid', 'ES')
            ->andReturn(true);

        $result = $this->weatherService->addToFavorites($userId, $weatherData);
        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}