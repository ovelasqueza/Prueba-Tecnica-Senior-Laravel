# Weather App - Laravel

## Descripción

Weather App es una aplicación web desarrollada con Laravel que permite a los usuarios consultar el clima actual de diferentes ciudades del mundo. La aplicación utiliza la API de [WeatherAPI](https://www.weatherapi.com/) para obtener datos meteorológicos en tiempo real.

### Características principales

- Registro y autenticación de usuarios
- Consulta del clima actual por ciudad
- Guardado de ciudades favoritas
- Historial de búsquedas realizadas
- API RESTful para integración con otros sistemas
- Interfaz de usuario intuitiva y responsive

## Requisitos previos

- [Docker](https://www.docker.com/products/docker-desktop/) y Docker Compose
- [Git](https://git-scm.com/downloads)
- Una cuenta en [WeatherAPI](https://www.weatherapi.com/) para obtener una API key gratuita

## Instalación con Docker

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/weather-app-laravel.git
cd weather-app-laravel
```

### 2. Configurar variables de entorno

Copie el archivo de ejemplo de variables de entorno y configúrelo:

```bash
cp .env.example .env
```

Edite el archivo `.env` y configure las siguientes variables:

- Configuración de la base de datos (ya está preconfigurada para Docker):
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

- Configuración de la API de clima (requerida):
```
WEATHER_API_KEY=su_api_key_aquí
```

### 3. Construir y levantar los contenedores Docker

```bash
docker-compose up -d
```

### 4. Instalar dependencias de Composer

```bash
docker-compose exec app composer install
```

### 5. Generar clave de aplicación

```bash
docker-compose exec app php artisan key:generate
```

### 6. Ejecutar migraciones

```bash
docker-compose exec app php artisan migrate
```

### 7. Acceder a la aplicación

Abra su navegador y visite: [http://localhost:8000](http://localhost:8000)

## Estructura del proyecto

- `app/Http/Controllers/WeatherController.php`: Controlador principal para las funcionalidades del clima
- `app/Services/WeatherService.php`: Servicio que maneja la lógica de negocio y comunicación con la API externa
- `app/Repositories/`: Repositorios para acceso a datos
- `app/Models/`: Modelos de datos (User, FavoriteCity, SearchHistory)
- `database/migrations/`: Migraciones para crear las tablas en la base de datos
- `routes/`: Definición de rutas web y API
- `tests/`: Tests unitarios y de integración

## API Endpoints

La aplicación expone los siguientes endpoints API:

- `POST /api/register`: Registro de usuario
- `POST /api/login`: Inicio de sesión
- `GET /api/weather?city={city}`: Obtener clima actual de una ciudad
- `GET /api/favorites`: Listar ciudades favoritas del usuario
- `POST /api/favoritesAdd`: Añadir ciudad a favoritos
- `DELETE /api/favorites/{id}`: Eliminar ciudad de favoritos
- `GET /api/history`: Obtener historial de búsquedas

## Ejecución de tests

```bash
docker-compose exec app php artisan test
```

## Desarrollo sin Docker

Si prefiere desarrollar sin Docker, asegúrese de tener:

- PHP >= 8.0
- Composer
- MySQL >= 8.0

Siga los pasos de instalación normales de Laravel:

```bash
composer install
cp .env.example .env
# Configure su base de datos y API key en .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Contribución

Las contribuciones son bienvenidas. Por favor, abra un issue primero para discutir lo que le gustaría cambiar.

## Licencia

[MIT](https://choosealicense.com/licenses/mit/)
