<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consulta del Tiempo - Weather App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div id="app" class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">Weather App</h1>
                    </div>
                    <div class="flex items-center">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-800">Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Búsqueda de ciudad -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Buscar Ciudad</h2>
                    <div class="flex gap-2">
                        <input type="text" v-model="searchQuery" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ingrese el nombre de la ciudad">
                        <button @click="searchWeather" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Buscar
                        </button>
                    </div>

                    <!-- Resultados del clima -->
                    <div v-if="weatherData" class="mt-6">
                        <div class="text-center">
                            <h3 class="text-xl font-bold">@{{ weatherData.city_name }}</h3>
                            <p class="text-4xl font-bold mt-2">@{{ weatherData.temperature }}°C</p>
                            <p class="text-lg mt-2">@{{ weatherData.weather_condition }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-600">Humedad</p>
                                    <p class="font-semibold">@{{ weatherData.humidity }}%</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Viento</p>
                                    <p class="font-semibold">@{{ weatherData.wind_speed }} km/h</p>
                                </div>
                            </div>
                            <button @click="addToFavorites" 
                                    class="mt-4 px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                Agregar a Favoritos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ciudades favoritas -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Ciudades Favoritas</h2>
                    <div v-if="favorites.length" class="space-y-4">
                        <div v-for="favorite in favorites" :key="favorite.id" 
                             class="p-4 border rounded-lg hover:bg-gray-50">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold">@{{ favorite.city_name }}</h3>
                                    <p class="text-sm text-gray-600">@{{ favorite.country_code }}</p>
                                </div>
                                <button @click="removeFavorite(favorite.id)" 
                                        class="text-red-600 hover:text-red-800">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-600 text-center">No tienes ciudades favoritas</p>
                </div>
            </div>

            <!-- Historial de búsquedas -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Historial de Búsquedas</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ciudad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Temperatura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Condición</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="search in searchHistory" :key="search.id">
                                <td class="px-6 py-4">@{{ search.city_name }}</td>
                                <td class="px-6 py-4">@{{ search.temperature }}°C</td>
                                <td class="px-6 py-4">@{{ search.weather_condition }}</td>
                                <td class="px-6 py-4">@{{ formatDate(search.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue

        // Configurar el interceptor de Axios para incluir el token en todas las solicitudes
        axios.interceptors.request.use(function (config) {
            const token = localStorage.getItem('token');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            config.headers['X-CSRF-TOKEN'] = csrfToken;
            if (token) {
                config.headers['Authorization'] = `Bearer ${token}`;
            }
            return config;
        }, function (error) {
            return Promise.reject(error);
        });

        createApp({
            data() {
                return {
                    searchQuery: '',
                    weatherData: null,
                    favorites: [],
                    searchHistory: []
                }
            },
            methods: {
                async searchWeather() {
                    try {
                        const response = await axios.get(`/api/weather?city=${this.searchQuery}`)
                       

                        this.weatherData = {
                        city_name: response.data.location.name,
                        country_code: response.data.location.country,
                        latitude: response.data.location.lat,
                        longitude: response.data.location.lon,
                        temperature: response.data.current.temp_c,
                        humidity: response.data.current.humidity,
                        wind_speed: response.data.current.wind_kph,
                        weather_condition: response.data.current.condition.text,
                        icon: response.data.current.condition.icon
                        }
                        this.loadSearchHistory()
                    } catch (error) {
                        alert('Error al buscar el clima. Por favor, intente nuevamente.')
                    }

                },
                async loadFavorites() {
                    try {
                        const response = await axios.get('/api/favorites')
                        this.favorites = response.data
                    } catch (error) {
                        console.error('Error al cargar favoritos:', error)
                    }
                },
                async addToFavorites() {
                    if (!this.weatherData) return
                    
                    try {
                        await axios.post('/api/favoritesAdd', {
                            city_name: this.weatherData.city_name,
                            country_code: this.weatherData.country_code,
                            latitude: this.weatherData.latitude,
                            longitude: this.weatherData.longitude
                        })
                        this.loadFavorites()
                    } catch (error) {
                        alert('Error al agregar a favoritos. Por favor, intente nuevamente.')
                    }
                },
                async removeFavorite(id) {
                    try {
                        await axios.delete(`/api/favorites/${id}`)
                        this.loadFavorites()
                    } catch (error) {
                        alert('Error al eliminar de favoritos. Por favor, intente nuevamente.')
                    }
                },
                async loadSearchHistory() {
                    try {
                        const response = await axios.get('/api/history')
                        this.searchHistory = response.data
                    } catch (error) {
                        console.error('Error al cargar historial:', error)
                    }
                },
                formatDate(date) {
                    return new Date(date).toLocaleString()
                }
            },
            mounted() {
                this.loadFavorites()
                this.loadSearchHistory()
            }
        }).mount('#app')
    </script>
</body>
</html>