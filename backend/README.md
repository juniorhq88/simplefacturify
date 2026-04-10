# Backend — simpleFacturify

API RESTful del sistema de facturación, construida con **Laravel 11** y autenticación via **JWT**.

Este backend proporciona los endpoints para la gestión de facturas, clientes, y demás funcionalidades del sistema.

---

## Requisitos

| Herramienta    | Versión mínima   |
| -------------- | ---------------- |
| PHP            | 8.2              |
| Composer       | 2.x              |
| MySQL          | 8.0              |

---

## Instalación rápida

```bash
# Instalar dependencias
composer install

# Copiar configuración
cp .env.example .env

# Generar clave
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --seed

# Arrancar servidor
php artisan serve
```

Accede a: **http://localhost:8000**

---

## Documentación técnica

Consulta el README principal en la raíz del proyecto para información detallada:
- Endpoints de la API
- Estructura del proyecto
- Decisiones técnicas
- Variables de entorno

---

## Tests

```bash
php artisan test
```

---

## Ayuda

¿Necesitas ayuda? Revisa la documentación de Laravel: https://laravel.com/docs