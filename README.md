# api-decameron

API REST para la gestión de hoteles y configuración de habitaciones, desarrollada como backend del sistema de administración hotelera Decameron. Permite crear y administrar hoteles, asignar tipos de habitación con sus respectivas acomodaciones y consultar catálogos de referencia, aplicando reglas de negocio estrictas en cada operación.

---

## Stack tecnologico

| Capa | Tecnologia | Version |
|---|---|---|
| Lenguaje | PHP | ^8.3 |
| Framework | Laravel | ^13.0 |
| Base de datos (produccion) | PostgreSQL via Supabase | 15 (pooler) |
| Base de datos (desarrollo local) | SQLite | cualquiera |
| ORM | Eloquent (incluido en Laravel) | — |
| REPL interactivo | Laravel Tinker | ^3.0 |
| Servidor de desarrollo | php artisan serve | — |
| Gestor de dependencias PHP | Composer | 2.x |
| Gestor de paquetes JS | npm | LTS |
| Linter PHP | Laravel Pint | ^1.27 |
| Testing | PHPUnit | ^12.5.12 |
| Faker (seeders/tests) | fakerphp/faker | ^1.23 |
| Inspeccion de logs | Laravel Pail | ^1.2.5 |
| Colision de errores CLI | nunomaduro/collision | ^8.6 |

No se utiliza ninguna libreria de autenticacion (Sanctum, Passport) ni transformadores (Resources) en la version actual. La API es publica.

---

## Arquitectura del proyecto

El proyecto sigue la arquitectura en capas propia de Laravel, con la adicion de una capa de servicio explícita para aislar la lógica de negocio de los controladores.

```
app/
  Exceptions/
    Handler.php              # Manejo global de errores en JSON
  Http/
    Controllers/
      CatalogController.php  # Endpoints de solo lectura (catálogos)
      HotelController.php    # CRUD de hoteles
      HotelRoomController.php# Gestion de configuraciones de habitacion
    Requests/
      StoreHotelRequest.php
      UpdateHotelRequest.php
      StoreHotelRoomRequest.php
      UpdateHotelRoomRequest.php
  Models/
    Hotel.php
    HotelRoom.php
    RoomType.php
    Accommodation.php
  Rules/
    RoomTypeAccommodationRule.php  # Regla de negocio: tipo + acomodacion valida
  Services/
    HotelService.php               # Logica de negocio centralizada
database/
  migrations/                      # Esquema de tablas
  seeders/
    RoomTypeSeeder.php             # Datos maestros: tipos de habitacion
    AccommodationSeeder.php        # Datos maestros: acomodaciones
routes/
  api.php                          # Definicion de todos los endpoints
```

---

## Modelo de datos

### Tablas

**hotels**

| Columna | Tipo | Restricciones |
|---|---|---|
| id | bigint (PK) | autoincrement |
| name | varchar(150) | not null |
| address | varchar(200) | not null |
| city | varchar(100) | not null |
| nit | varchar(20) | not null, unique |
| total_rooms | unsigned int | not null |
| created_at / updated_at | timestamp | — |

**room_types** — datos maestros, gestionados por seeder

| Valor |
|---|
| Estandar |
| Junior |
| Suite |

**accommodations** — datos maestros, gestionados por seeder

| Valor |
|---|
| Sencilla |
| Doble |
| Triple |
| Cuadruple |

**hotel_rooms** — tabla pivote con cantidad

| Columna | Tipo | Restricciones |
|---|---|---|
| id | bigint (PK) | autoincrement |
| hotel_id | bigint (FK) | cascadeOnDelete |
| room_type_id | bigint (FK) | restrictOnDelete |
| accommodation_id | bigint (FK) | restrictOnDelete |
| quantity | unsigned int | not null |
| — | unique constraint | (hotel_id, room_type_id, accommodation_id) |
| created_at / updated_at | timestamp | — |

### Relaciones

- `Hotel` tiene muchas `HotelRoom` (hasMany)
- `HotelRoom` pertenece a `Hotel`, `RoomType` y `Accommodation` (belongsTo)
- `RoomType` y `Accommodation` tienen muchas `HotelRoom` (hasMany)

---

## Reglas de negocio

Las reglas estan centralizadas en `App\Rules\RoomTypeAccommodationRule` y en `App\Services\HotelService`. No se dispersan por controladores ni validaciones de formulario.

### Combinaciones validas tipo + acomodacion

| Tipo de habitacion | Acomodaciones permitidas |
|---|---|
| Estandar | Sencilla, Doble |
| Junior | Triple, Cuadruple |
| Suite | Sencilla, Doble, Triple |

### Reglas aplicadas al crear o actualizar una configuracion de habitacion

1. La combinacion tipo + acomodacion debe ser valida segun la tabla anterior.
2. Un hotel no puede tener dos registros con la misma combinacion tipo + acomodacion (tambien garantizado por constraint unico en base de datos).
3. La suma de `quantity` de todas las configuraciones no puede superar `total_rooms` del hotel.
4. Al actualizar `total_rooms` de un hotel, el nuevo valor no puede ser inferior al numero de habitaciones ya configuradas.

Todas las violaciones de estas reglas se lanzan como `ValidationException` y el handler global las devuelve con HTTP 422 en formato JSON uniforme.

---

## Endpoints

La URL base es `/api`. Todos los endpoints responden y reciben JSON.

### Catálogos (solo lectura)

```
GET  /api/catalogs/room-types
GET  /api/catalogs/accommodations
GET  /api/catalogs/room-types/{roomType}/accommodations
```

### Hoteles

```
GET    /api/hotels
POST   /api/hotels
GET    /api/hotels/{hotel}
PUT    /api/hotels/{hotel}
DELETE /api/hotels/{hotel}
```

Campos requeridos para `POST /api/hotels`: `name`, `address`, `city`, `nit` (unico), `total_rooms` (entero >= 1).

### Habitaciones de un hotel (rutas anidadas)

```
GET    /api/hotels/{hotel}/rooms
POST   /api/hotels/{hotel}/rooms
PUT    /api/hotels/{hotel}/rooms/{hotelRoom}
DELETE /api/hotels/{hotel}/rooms/{hotelRoom}
```

Campos requeridos para `POST /api/hotels/{hotel}/rooms`: `room_type_id`, `accommodation_id`, `quantity`.

El endpoint `GET /api/hotels/{hotel}/rooms` incluye un campo `meta` con `total_rooms`, `assigned_rooms` y `available_rooms`.

### Formato de respuesta uniforme

Todos los endpoints devuelven:

```json
{
  "success": true | false,
  "message": "...",
  "data": { ... } | [ ... ],
  "errors": { ... }   // solo en caso de validacion fallida (HTTP 422)
}
```

Los errores de modelo no encontrado devuelven HTTP 404. Los errores internos devuelven HTTP 500 con el mensaje de excepcion solo si `APP_DEBUG=true`.

---

## Configuracion y puesta en marcha

### Requisitos previos

- PHP 8.3 o superior con las extensiones `pdo`, `pdo_pgsql` (o `pdo_sqlite`), `mbstring`, `openssl` y `tokenizer`
- Composer 2.x
- Node.js LTS y npm (necesario para el script `setup` del `composer.json`)
- Una instancia de PostgreSQL accesible, o SQLite para desarrollo rapido

### Variables de entorno

Copiar `.env.example` a `.env` y ajustar las siguientes variables:

```env
APP_NAME="Decameron API"
APP_ENV=local
APP_KEY=                        # Se genera con artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

# Base de datos — PostgreSQL (produccion / Supabase)
DB_CONNECTION=pgsql
DB_HOST=<host>
DB_PORT=5432
DB_DATABASE=postgres
DB_SCHEMA=db_decameron
DB_SEARCH_PATH=db_decameron
DB_USERNAME=<usuario>
DB_PASSWORD=<contrasena>
DB_SSLMODE=require

# Base de datos — SQLite (desarrollo local alternativo)
# DB_CONNECTION=sqlite
# (sin mas variables; usa database/database.sqlite)

LOG_CHANNEL=stack
LOG_LEVEL=debug

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Instalacion paso a paso

```bash
# 1. Clonar o descomprimir el proyecto
cd api-decameron

# 2. Instalar dependencias PHP
composer install

# 3. Copiar variables de entorno
cp .env.example .env

# 4. Generar clave de aplicacion
php artisan key:generate

# 5. Ejecutar migraciones
php artisan migrate

# 6. Poblar datos maestros (tipos de habitacion y acomodaciones)
php artisan db:seed --class=RoomTypeSeeder
php artisan db:seed --class=AccommodationSeeder

# 7. (Opcional) Instalar dependencias JS
npm install

# 8. Levantar servidor de desarrollo
php artisan serve
# La API queda disponible en http://localhost:8000/api
```

Alternativamente, el script `composer setup` encadena los pasos 2 al 7 automaticamente:

```bash
composer setup
```

### Levantar el entorno completo de desarrollo

El script `composer dev` levanta en paralelo el servidor HTTP, el worker de colas, el visor de logs y el proceso Vite:

```bash
composer dev
```

---

## CORS

La configuracion de CORS se encuentra en `config/cors.php`. En el estado actual del repositorio, `allowed_origins` esta abierto a `'*'` para facilitar el desarrollo. Para produccion se debe reemplazar por la URL exacta del frontend:

```php
'allowed_origins' => ['https://tu-frontend.com'],
```

---

## Testing

El proyecto usa PHPUnit. Para ejecutar la suite de pruebas:

```bash
composer test
# equivalente a: php artisan config:clear && php artisan test
```

Los archivos de prueba se ubican en `tests/Feature` y `tests/Unit`. La suite base incluye los tests de ejemplo de Laravel; se espera que se expanda con tests de los endpoints y las reglas de negocio.

---

## Consideraciones de produccion

- Cambiar `APP_DEBUG=false` para no exponer trazas de excepcion en las respuestas JSON.
- Configurar `allowed_origins` en CORS con la URL real del frontend.
- Ejecutar `php artisan config:cache` y `php artisan route:cache` para optimizar el arranque.
- Usar un proceso supervisor (Supervisor, systemd) si se activa el driver de colas `database` o `redis`.
- El `DB_SSLMODE=require` ya esta configurado para conexiones seguras a Supabase.