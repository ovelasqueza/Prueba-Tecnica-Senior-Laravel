<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FavoriteCity;
use App\Models\SearchHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $mockWeatherResponse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->mockWeatherResponse = [
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
            'http://api.weatherapi.com/v1/current.json*' => Http::response($this->mockWeatherResponse, 200)
        ]);
    }

    public function test_get_current_weather()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/weather?city=Madrid');

        $response->assertStatus(200)
            ->assertJson($this->mockWeatherResponse);

        $this->assertDatabaseHas('search_histories', [
            'user_id' => $this->user->id,
            'city_name' => 'Madrid',
            'country_code' => 'Spain'
        ]);
    }

    public function test_get_current_weather_validation_error()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/weather');

        $response->assertStatus(400)
            ->assertJsonStructure(['errors' => ['city']]);
    }

    public function test_add_city_to_favorites()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/weather/favorites', [
                'city' => 'Madrid'
            ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Ciudad añadida a favoritos']);

        $this->assertDatabaseHas('favorite_cities', [
            'user_id' => $this->user->id,
            'city_name' => 'Madrid',
            'country_code' => 'Spain'
        ]);
    }

    public function test_add_duplicate_city_to_favorites()
    {
        FavoriteCity::create([
            'user_id' => $this->user->id,
            'city_name' => 'Madrid',
            'country_code' => 'Spain',
            'latitude' => 40.42,
            'longitude' => -3.70
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/weather/favorites', [
                'city' => 'Madrid'
            ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'La ciudad ya está en favoritos']);
    }

    public function test_remove_city_from_favorites()
    {
        $favorite = FavoriteCity::create([
            'user_id' => $this->user->id,
            'city_name' => 'Madrid',
            'country_code' => 'Spain',
            'latitude' => 40.42,
            'longitude' => -3.70
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/weather/favorites/{$favorite->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ciudad eliminada de favoritos']);

        $this->assertDatabaseMissing('favorite_cities', ['id' => $favorite->id]);
    }

    public function test_get_favorite_cities()
    {
        FavoriteCity::create([
            'user_id' => $this->user->id,
            'city_name' => 'Madrid',
            'country_code' => 'Spain',
            'latitude' => 40.42,
            'longitude' => -3.70
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/weather/favorites');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'city_name' => 'Madrid',
                'country_code' => 'Spain'
            ]);
    }

    public function test_get_search_history()
    {
        SearchHistory::create([
            'user_id' => $this->user->id,
            'city_name' => 'Madrid',
            'country_code' => 'Spain',
            'temperature' => 20,
            'weather_condition' => 'Sunny',
            'wind_speed' => 15,
            'humidity' => 65,
            'local_time' => '2024-03-15 12:00'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/weather/history');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'city_name' => 'Madrid',
                'country_code' => 'Spain'
            ]);
    }
}