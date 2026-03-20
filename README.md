# RapidBistro

Sistema de gestión de reservaciones de restaurante.

## Stack

| Capa | Tecnología |
| ---- | ---------- |
| Backend | Laravel 12, PHP 8.x |
| Base de datos | PostgreSQL |
| Frontend | Svelte 5, InertiaJS v2 |
| Estilos | Tailwind CSS v4 |
| Autorización | Spatie Laravel Permission v7 |
| Notificaciones UI | svelte-sonner |
| Build | Vite 8 |

## Requisitos

- PHP >= 8.2
- Composer
- Node.js >= 20
- PostgreSQL

## Instalación

```bash
# Clonar el repositorio
git clone <repo-url>
cd rapidbistro-app

# Dependencias PHP
composer install

# Dependencias JS
npm install

# Variables de entorno
cp .env.example .env
php artisan key:generate
```

Configurar la conexión a base de datos en `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rapidbistro
DB_USERNAME=postgres
DB_PASSWORD=secret
```

```bash
# Migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm run build

# Servidor de desarrollo
php artisan serve
npm run dev
```

## Usuarios de prueba

| Email | Contraseña | Rol |
| ----- | ---------- | --- |
| `admin@rapidbistro.com` | `password` | super-admin |
| `recepcion@rapidbistro.com` | `password` | receptionist |
| `staff@rapidbistro.com` | `password` | staff |

## Roles y acceso

| Rol | Acceso |
| --- | ------ |
| `super-admin` | Todo el sistema |
| `admin` | Todo el sistema |
| `receptionist` | Reservaciones, mesas, zonas, dashboard |
| `staff` | Dashboard, reservaciones (solo lectura) |

Las rutas de **Horarios**, **Usuarios** y **Configuración** requieren rol `admin` o `super-admin`.

## Módulos implementados

### Reservaciones

- Listado con filtros por estado y búsqueda
- Crear reservación (mesa, cliente, personas, fecha/hora, notas)
- Editar reservación
- Eliminar reservación

### Mesas

- CRUD completo con modal inline
- Asignación a zona, capacidad y estado

### Zonas

- CRUD completo con modal inline

### Horarios (`/time-slots`)

- Gestión de franjas horarias por día de la semana
- Solo admin

### Usuarios (`/users`)

- Gestión de usuarios y asignación de roles
- Solo admin

### Configuración del restaurante (`/restaurant/settings`)

- Nombre, teléfono, dirección, capacidad máxima, configuraciones adicionales
- Solo admin

### Perfil (`/profile`)

- El usuario puede actualizar nombre, email, teléfono y contraseña

## Estructura de carpetas relevante

```text
app/
  Http/
    Controllers/     # Auth, Dashboard, Reservations, Zones, Tables, Users, TimeSlots, Restaurant, Profile
    Middleware/      # HandleInertiaRequests (shared data: auth.user, flash)
  Models/            # User, Restaurant, Zone, Table, TimeSlot, Reservation, ReservationStatusLog

resources/
  js/
    Layouts/         # AppLayout.svelte, AuthLayout.svelte
    Pages/           # Auth, Dashboard, Reservations, Zones, Tables, Users, TimeSlots, Restaurant, Profile

bootstrap/
  app.php            # Registro de middleware aliases (Spatie roles/permisos)
```

## Notas de desarrollo

- El frontend usa **Svelte 5 runes** (`$state`, `$derived`, `$props`, `$effect`). No usar `useForm` de InertiaJS — usar `$state` + `router` de `@inertiajs/svelte`.
- Los modelos usan `uuid` como identificador público en URLs (no `id`).
- Las flash messages (`success`, `error`, `warning`, `info`) se envían desde el backend y se muestran automáticamente como toasts en `AppLayout`.
- El sidebar en desktop es colapsable (icono-only mode).
