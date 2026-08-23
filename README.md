# Pharmacovigilance Alert System

> **Modulo de Farmacovigilancia y Gestión de Retiro de Lotes**
> Sistema desarrollado para la identificación, filtrado y notificación de clientes afectados por el retiro de medicamentos o lotes específicos en una farmacia magistral (Compounding Pharmacy).

---

## 1. Propósito y Alcance

### 1.1 Propósito

Proporcionar una solución web eficiente y segura que permita al equipo de farmacovigilancia rastrear compras de medicamentos asociadas a un número de lote determinado (ejemplo: lote `951357`) en un rango de fechas y despachar alertas por correo electrónico/SMS a los compradores afectados.

### 1.2 Alcance Funcional

- **Autenticación Protegida:** Módulo exclusivo para personal autorizado (`/pharmacovigilance/login`).
- **Búsqueda y Filtrado:** Consulta por número de lote (requerido) y rango de fechas (por defecto: últimos 30 días).
- **Recuperación de Órdenes:** Visualización de órdenes, datos de contacto del cliente, fecha de compra e historial de alertas.
- **Acciones sobre Órdenes:**
  - Ver detalle de la orden.
  - Ver perfil del comprador.
  - Despachar alerta individual o masiva (Bulk Alerting).
- **Registro y Auditoría:** Historial completo de notificaciones emitidas (timestamp, usuario emisor, cliente notificado y estado).

---

## 2 Arquitectura Propuesta

El proyecto sigue una arquitectura limpia basada en **MVC + API-Driven Design**:

```text
Vue 3 (Frontend Shadcn Vue - SPA)
           │
           ▼
HTTP / REST API (Bearer Token - Sanctum)
           │
           ▼
    Laravel (Backend)
           │
           ▼
  MySQL (Relational DB)
```

### 2.1 Principios Clave de Diseño

1. **Controllers Delgados:** Delegación de reglas de negocio a Form Requests y Services/Actions.
2. **Prevención de N+1:** *Eager Loading* explícito en consultas de órdenes y medicamentos.
3. **Escalabilidad de Roles:** Verificación de permisos apoyada en caché para evitar sobrecargar MySQL en peticiones transaccionales.
4. **Tipado estricto en frontend:** Componentes Vue en TypeScript (`<script setup lang="ts">`) para detectar errores antes de runtime.

---

## 3 Stack Tecnológico

- **Backend:** PHP 8.2+ / Laravel 12 (API REST, Sanctum Auth)
- **Frontend:** Vue 3 (Composition API / Script Setup) + TypeScript + Vite
- **Componentes UI & Estilos:** Shadcn Vue + Tailwind CSS
- **Base de Datos:** MySQL 8.0+
- **Manejo de Tareas / Colas:** Laravel Queues (para envío diferido de mails/alertas)
- **Gestor de Paquetes (Frontend):** Yarn 4 (Berry)
- **Control de Versiones:** Git & GitHub

---

## 4 Contrato de API (REST API Endpoints)

Todos los endpoints retornan respuestas en formato `application/json` y están prefijados por `/api/v1`.

### 4.1 Autenticación

#### Iniciar Sesión

- **Endpoint:** `POST /api/v1/login`
- **Request:**

```json
  {
    "email": "admin@farmacia.com",
    "password": "SecretPassword123!"
  }
```

- **Response (200 OK):**

```json
  {
    "message": "Inicio de sesión exitoso.",
    "access_token": "1|token_hash_value",
    "token_type": "Bearer",
    "user": { "id": 1, "name": "Administrador Farmacovigilancia", "email": "admin@farmacia.com" }
  }
```

#### Cierre de Sesión

- **Endpoint:** `POST /api/v1/logout`
- **Header:** `Authorization: Bearer {token}`

---

### 4.2 Búsqueda y Órdenes

#### Listar / Buscar Órdenes por Lote

- **Endpoint:** `GET /api/v1/orders`
- **Params:** `lot` (requerido), `start_date` (opcional, YYYY-MM-DD), `end_date` (opcional, YYYY-MM-DD), `page`, `per_page`
- **Ejemplo:** `GET /api/v1/orders?lot=951357&start_date=2026-07-21&end_date=2026-08-21`
- **Response (200 OK):**

  ```json
  {
    "success": true,
    "data": [
      {
        "order_id": 1052,
        "order_number": "ORD-0001572",
        "purchase_date": "2026-08-05 14:30:00",
        "customer": {
          "id": 482,
          "name": "Carlos Mendoza",
          "email": "carlos.mendoza@example.com",
          "phone": "+57 300 123 4567"
        },
        "medication": {
          "id": 12,
          "name": "Amoxicilina 500mg Compuesta",
          "lot_number": "951357"
        },
        "alerted": false
      }
    ],
    "meta": { "current_page": 1, "per_page": 15, "total": 45 }
  }
  ```

#### Detalle de Orden

- **Endpoint:** `GET /api/v1/orders/{id}`

#### Detalle de Cliente

- **Endpoint:** `GET /api/v1/customers/{id}`

---

### 4.3 Despacho de Alertas

#### Enviar Alerta

- **Endpoint:** `POST /api/v1/alerts/send`
- **Request:**

  ```json
  {
    "lot_number": "951357",
    "order_ids": [1052, 1053],
    "message": "Aviso de retiro de medicamento lote 951357. Favor suspender su uso e informar a la farmacia."
  }
  ```

- **Response (200 OK):**

  ```json
  {
    "success": true,
    "message": "Alertas procesadas correctamente",
    "data": { "sent_count": 2, "timestamp": "2026-08-21 22:43:00" }
  }
  ```

---

### 5.2 Pasos de Instalación

#### - **Clonar el repositorio e ingresar al proyecto:**

```bash
   git clone git@github.com:jpachecox/pharma-recall-platform.git
   cd pharma-recall-platform
```

#### - **Instalar dependencias de PHP:**

```bash
   composer install
```

#### - **Instalar dependencias de Frontend (Vue 3 + TypeScript + Shadcn Vue):**

```bash
   yarn install
```

#### - **Configurar el archivo de entorno `.env`:**

```bash
   cp .env.example .env
   php artisan key:generate
```

#### - **Configurar la base de datos en `.env`:**

```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pharma_recall_platform
   DB_USERNAME=root
   DB_PASSWORD=
```

#### - **Ejecutar migraciones y poblar la base de datos con Seeders:**

```bash
   php artisan migrate:fresh --seed
```

#### - **Compilar assets o levantar el servidor de desarrollo Vite:**

```bash
   yarn dev
```

#### - **Iniciar el servidor local de Laravel:**

```bash
   php artisan serve
```

El backend estará disponible en `http://127.0.0.1:8000` y las rutas API respondiendo bajo `/api/v1/`.

- Scripts útiles (Frontend)

```bash
yarn dev          # servidor de desarrollo Vite
yarn build        # build de producción
yarn type-check   # validación de tipos TypeScript (vue-tsc), sin generar archivos
```

---

## 6 Flujo de Trabajo (Gitflow)

- **`main`**: rama de producción. Solo recibe merges vía PR desde `develop` (release).
- **`develop`**: rama de integración. Todo el trabajo diario se mergea aquí.
- **Ramas de trabajo:** `type/nombre-corto`, donde `type` sigue [Conventional Commits](https://www.conventionalcommits.org/) (`feat`, `fix`, `refactor`, `test`, `docs`, `chore`). Ejemplo: `feat/orders-search-endpoint`.
- **Issues:** se crean con los templates de `.github/ISSUE_TEMPLATE/` (`task`, `bug`, `feature`).
- **Pull Requests:** siguen `.github/PULL_REQUEST_TEMPLATE.md`, apuntan a `develop` por defecto.
- **Changelog:** cada cambio relevante se agrega a `CHANGELOG.md` bajo `[Unreleased]` antes de mergear a `develop`,
  siguiendo [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)

---

### 7 Testing

```bash
php artisan test
```

Cobertura mínima esperada: autenticación, búsqueda/filtros, orders (listado, detalle, 404, paginación), alerts (envío, validación, registro, fallo de envío).

---

## 8 Decisiones de Arquitectura y Supuestos

- El envío de alertas se marca como exitoso solo si el email realmente se despachó (no se registra una alerta "fantasma" ante un fallo de envío).
- La búsqueda de órdenes se filtra en MySQL, no en PHP, para evitar traer datos innecesarios y prevenir N+1 mediante eager loading explícito.
- Roles y permisos (RBAC) están fuera del MVP obligatorio; se documentan como bonus en `ROLES_PERMISSIONS_SCHEMA.md`

---

## 9 Credenciales de Prueba

_(Completar tras correr el seeder — usuario/contraseña generados en `DatabaseSeeder`.)
