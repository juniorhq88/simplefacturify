# MiInbox — Backend (Laravel)

API RESTful para el módulo de mensajería tipo inbox, construida con **Laravel** y autenticación via **JWT** (JSON Web Tokens).

---

## Requisitos

| Herramienta    | Versión mínima   |
| -------------- | ---------------- |
| PHP            | 8.2              |
| Composer       | 2.x              |
| MySQL / SQLite | 8.0 / cualquiera |

---

## Instalación y arranque

```bash
# 1. Entrar a la carpeta
cd backend

# 2. Instalar dependencias
composer install

# 3. Copiar variables de entorno
cp .env.example .env

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_DATABASE=miinbox
DB_USERNAME=root
DB_PASSWORD=

# 6. Ejecutar migraciones + seeder
php artisan migrate --seed

# 7. Levantar servidor de desarrollo
php artisan serve
# → http://localhost:8000
```

---

## Variables de entorno relevantes

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000   # Para CORS

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=miinbox
DB_USERNAME=root
DB_PASSWORD=

JWT_TTL=3600
JWT_REFRESH_TTL=7200
```

---

## Endpoints de la API

Todos los endpoints protegidos requieren el header:

```
Authorization: Bearer {jwt_token}
```

### Autenticación

| Método | Ruta        | Descripción                   |
| ------ | ----------- | ----------------------------- |
| POST   | /api/login  | Login → devuelve Bearer token |
| GET    | /api/user   | Datos del usuario autenticado |
| POST   | /api/logout | Revoca el token actual        |

**Login payload:**

```json
{ "email": "alice@example.com", "password": "password" }
```

### Conversaciones (Threads)

| Método | Ruta              | Descripción                           |
| ------ | ----------------- | ------------------------------------- |
| GET    | /api/threads      | Lista paginada de hilos del usuario   |
| POST   | /api/threads      | Crear hilo nuevo con primer mensaje   |
| GET    | /api/threads/{id} | Detalle del hilo + todos sus mensajes |

**Query params para GET /api/threads:**

- `search=texto` — filtra por asunto
- `per_page=15` — resultados por página (default: 15)

**POST /api/threads payload:**

```json
{
  "subject": "Asunto del mensaje",
  "body": "Cuerpo del primer mensaje",
  "participant_ids": [2, 3]
}
```

### Mensajes

| Método | Ruta                       | Descripción              |
| ------ | -------------------------- | ------------------------ |
| POST   | /api/threads/{id}/messages | Enviar respuesta al hilo |

**Payload:**

```json
{ "body": "Texto de la respuesta" }
```

### Notificaciones (bonus)

| Método | Ruta                         | Descripción                    |
| ------ | ---------------------------- | ------------------------------ |
| GET    | /api/notifications           | Lista notificaciones no leídas |
| PATCH  | /api/notifications/{id}/read | Marcar una como leída          |
| POST   | /api/notifications/read-all  | Marcar todas como leídas       |

---

## Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── ThreadController.php
│   │   ├── MessageController.php
│   │   └── NotificationController.php
│   ├── Requests/
│   │   ├── StoreThreadRequest.php
│   │   └── StoreMessageRequest.php
│   └── Resources/
│       ├── UserResource.php
│       ├── ThreadResource.php
│       ├── MessageResource.php
│       └── NotificationResource.php
├── Models/
│   ├── Thread.php
│   ├── Message.php
│   └── InboxNotification.php
database/
├── migrations/
│   ├── ..._create_threads_table.php
│   ├── ..._create_thread_participants_table.php
│   ├── ..._create_messages_table.php
│   └── ..._create_inbox_notifications_table.php
├── factories/
│   ├── ThreadFactory.php
│   ├── MessageFactory.php
│   └── InboxNotificationFactory.php
└── seeders/
    └── DatabaseSeeder.php
routes/
└── api.php
tests/
└── Feature/Api/
    ├── AuthTest.php
    ├── ThreadTest.php
    ├── MessageTest.php
    └── NotificationTest.php
```

---

## Ejecutar tests

```bash
php artisan test
# o con cobertura:
php artisan test --coverage
```

---

## Decisiones técnicas

### JWT en lugar de Laravel Sanctum

Se ha implementado autenticación con **JWT** en lugar de Sanctum para proporcionar tokens autocontenidos que incluyen claims verificables. JWT permite una validación más eficiente en sistemas distribuidos, reduce las consultas a la base de datos y facilita la implementación de SSO. La elección de JWT sigue explícitamente los requisitos de la prueba y ofrece mayor flexibilidad para futuras integraciones.

### SoftDeletes en Thread y Message

Se agregó `SoftDeletes` para permitir archivar/eliminar sin perder historial. Es un patrón habitual en sistemas de mensajería donde la auditoría importa.

### Tabla `thread_participants` explícita

En lugar de una relación polimórfica genérica, se optó por una tabla pivot dedicada con `last_read_at`. Esto permite calcular mensajes no leídos por participante de forma eficiente con un simple `WHERE last_read_at < messages.created_at`.

### `last_message_at` desnormalizado en `threads`

En lugar de hacer un `MAX(messages.created_at)` en cada query de listado, se mantiene `last_message_at` actualizado automáticamente desde el modelo `Message` (hook `booted → created`). Esto convierte el ordenamiento de la lista de hilos en un simple `ORDER BY last_message_at DESC` con índice.

### Form Requests para validación

Toda la validación de entrada vive en `StoreThreadRequest` y `StoreMessageRequest`. Esto mantiene los controladores delgados y las reglas de negocio localizadas y testeables de forma independiente.

### Resources de API

Se usan `JsonResource` para todas las respuestas. `whenLoaded` y `whenCounted` evitan el problema N+1 y permiten controlar qué relaciones se exponen según el endpoint.

---

## Nota sobre uso de IA

Este proyecto fue desarrollado con apoyo de **Claude (Anthropic)** como herramienta de asistencia. Las partes donde se utilizó IA y cómo se adaptaron:

- **Estructura de migraciones y modelos**: La IA sugirió la convención de nombres y los índices. Se revisó y ajustó la decisión de usar `last_message_at` desnormalizado (decisión propia) en lugar del enfoque sugerido inicialmente con subquery.
- **Tests de PHPUnit**: La IA generó la estructura base de los tests. Todos los escenarios (non-participant forbidden, notification not sent to sender, etc.) fueron diseñados manualmente para cubrir casos de negocio reales.
- **Resources**: El patrón `whenLoaded`/`whenCounted` fue sugerido por la IA y se adoptó tal cual por ser la práctica idiomática de Laravel.

Todo el código fue revisado, entendido y ajustado línea por línea antes de incluirlo.

---

## Usuarios de prueba (seeder)

| Email             | Password | Rol               |
| ----------------- | -------- | ----------------- |
| alice@example.com | password | Usuario principal |
| bob@example.com   | password | Participante      |
| carol@example.com | password | Participante      |
