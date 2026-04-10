# Frontend — simpleFacturify

Interfaz de usuario del sistema de facturación, construida con **Next.js 16**, **React 19** y **Tailwind CSS 4**.

Una aplicación moderna, rápida y responsive para gestionar tus facturas desde cualquier dispositivo.

---

## Requisitos

| Herramienta    | Versión mínima   |
| -------------- | ---------------- |
| Node.js        | 18.x             |
| npm            | 10.x             |

---

## Instalación rápida

```bash
# Instalar dependencias
npm install

# Arrancar desarrollo
npm run dev
```

Accede a: **http://localhost:3000**

---

## Scripts disponibles

| Comando       | Descripción                    |
| -------------- | ------------------------------ |
| npm run dev    | Servidor de desarrollo          |
| npm run build  | Build de producción             |
| npm run start  | Servidor de producción          |
| npm run lint   | Verificar código              |

---

## Configuración

El frontend se conecta al backend en `http://localhost:8000`. Si necesitas cambiar esta URL, edita el archivo de configuración de API en `app/`.

---

## Estructura del proyecto

```
frontend/
├── app/              # Páginas y rutas (App Router)
├── components/       # Componentes reutilizables
├── styles/           # Estilos globales
└── public/           # Assets estáticos
```

---

## Ayuda

¿Necesitas ayuda con Next.js? Revisa la documentación oficial: https://nextjs.org/docs