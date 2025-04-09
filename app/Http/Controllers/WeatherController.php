<?php

namespace App\Http\Controllers;

use App\Models\FavoriteCity;
use App\Models\SearchHistory;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class WeatherController extends Controller
{
    protected $weatherService;

  

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

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
    public function index()
    {  
        $weatherData = $this->weatherService->getCurrentWeather('Bogotá');
        $favoriteCities = FavoriteCity::where('user_id', Auth::id())
            ->orderBy('city_name')
            ->get();

        $searchHistory = SearchHistory::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('weather.index', [
            'favoriteCities' => $favoriteCities,
            'searchHistory' => $searchHistory
        ]);
    }

    public function getCurrentWeather(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255'
        ]);

        try {
            $weatherData = $this->weatherService->getCurrentWeather($request->city);
            $countryName = $weatherData['location']['country'];
            $countryCode = Arr::get($this->countryCodes, $countryName, substr($countryName, 0, 2));

            $weatherData['location']['country'] = $countryCode;
            $this->weatherService->saveSearchHistory(Auth::id(), $weatherData);

            return response()->json($weatherData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addFavoriteCity(Request $request)
{
    $request->validate([
        'city_name' => 'required|string|max:255',
    ]);

    try {
        $weatherData = $this->weatherService->getCurrentWeather($request->city_name);

        $success = $this->weatherService->addToFavorites(Auth::id(), $weatherData);
        if (!$success) {
            return response()->json(['error' => 'La ciudad ya está en favoritos'], 400);
        }

        return response()->json($weatherData, 201);
    } catch (\Exception $e) {
        \Log::error('Error al agregar ciudad a favoritos: ' . $e->getMessage());
        return response()->json(['error' => 'Ocurrió un error al procesar la solicitud'], 500);
    }
}

    public function removeFavoriteCity($id)
    {
        $success = $this->weatherService->removeFromFavorites($id);
        if (!$success) {
            return response()->json(['error' => 'No se pudo eliminar la ciudad'], 400);
        }
        return response()->json(['message' => 'Ciudad eliminada de favoritos']);
    }

    public function getFavoriteCities()
    {
        $favoriteCities = $this->weatherService->getFavoriteCities(Auth::id());
        return response()->json($favoriteCities);
    }

    public function getSearchHistory()
    {
        $searchHistory = $this->weatherService->getSearchHistory(Auth::id(), 10);
        return response()->json($searchHistory);
    }
}