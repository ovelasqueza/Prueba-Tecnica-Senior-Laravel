<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get current weather for a city
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentWeather(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'city' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }

            $weatherData = $this->weatherService->getCurrentWeather($request->city);
            
            if (Auth::check()) {
                $this->weatherService->saveSearchHistory(Auth::id(), $weatherData);
            }

            return response()->json($weatherData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos del clima'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add city to favorites
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addToFavorites(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'city' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }

            $weatherData = $this->weatherService->getCurrentWeather($request->city);
            $added = $this->weatherService->addToFavorites(Auth::id(), $weatherData);

            if (!$added) {
                return response()->json(['message' => 'La ciudad ya está en favoritos'], Response::HTTP_BAD_REQUEST);
            }

            return response()->json(['message' => 'Ciudad añadida a favoritos'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al añadir ciudad a favoritos'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove city from favorites
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removeFromFavorites(int $id): JsonResponse
    {
        try {
            $removed = $this->weatherService->removeFromFavorites($id);

            if (!$removed) {
                return response()->json(['error' => 'Ciudad no encontrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['message' => 'Ciudad eliminada de favoritos']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar ciudad de favoritos'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's favorite cities
     *
     * @return JsonResponse
     */
    public function getFavorites(): JsonResponse
    {
        try {
            $favorites = $this->weatherService->getFavoriteCities(Auth::id());
            return response()->json($favorites);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener ciudades favoritas'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's search history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearchHistory(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $history = $this->weatherService->getSearchHistory(Auth::id(), $limit);
            return response()->json($history);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener historial de búsquedas'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}