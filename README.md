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

#### Buscar Medicamentos por Lote

- **Endpoint:** `GET /api/v1/medications/search`
- **Params:** `lot` (requerido), `start_date` (opcional, YYYY-MM-DD, default: hace 30 días), `end_date` (opcional, YYYY-MM-DD, default: hoy), `per_page`
- **Ejemplo:** `GET /api/v1/medications/search?lot=951357&start_date=2026-07-21&end_date=2026-08-21`
- Si se envía rango de fechas, solo retorna medicamentos con al menos una orden dentro de ese rango (confirma que el lote circuló en la ventana investigada).
- **Response (200 OK):**

  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Paracetamol 500mg (Afectado)",
        "description": "Lote bajo investigación de farmacovigilancia",
        "lot_number": "951357",
        "orders_count": 8
      }
    ],
    "meta": { "current_page": 1, "per_page": 15, "total": 1 }
  }
  ```

#### Listar / Buscar Órdenes por Lote

- **Endpoint:** `GET /api/v1/orders`
- **Params:** `lot` (requerido), `start_date` (opcional, YYYY-MM-DD, default: hace 30 días), `end_date` (opcional, YYYY-MM-DD, default: hoy), `per_page`
- **Ejemplo:** `GET /api/v1/orders?lot=951357&start_date=2026-07-21&end_date=2026-08-21`
- **Response (200 OK):**

  ```json
  {
    "data": [
      {
        "id": 1052,
        "purchase_date": "2026-08-05T14:30:00Z",
        "customer": {
          "id": 482,
          "name": "Carlos Mendoza",
          "email": "carlos.mendoza@example.com",
          "phone": "+57 300 123 4567"
        },
        "medications": [
          {
            "id": 12,
            "name": "Amoxicilina 500mg Compuesta",
            "lot_number": "951357",
            "quantity": 2,
            "unit_price": "12500.00"
          }
        ],
        "alerts_sent": 0
      }
    ],
    "meta": { "current_page": 1, "per_page": 15, "total": 45 }
  }
  ```

#### Detalle de Orden

- **Endpoint:** `GET /api/v1/orders/{id}`
- **Response (200 OK):** misma forma que un item de `GET /orders`, con `customer` y `medications` precargados.
- **Response (404 Not Found):** si el `{id}` no existe:

  ```json
  { "message": "Resource not found." }
  ```

#### Detalle de Cliente

- **Endpoint:** `GET /api/v1/customers/{id}`
- **Response (200 OK):**

  ```json
  {
    "data": {
      "id": 482,
      "name": "Carlos Mendoza",
      "email": "carlos.mendoza@example.com",
      "phone": "+57 300 123 4567"
    }
  }
  ```

- **Response (404 Not Found):** igual que en Detalle de Orden.

---

### 4.3 Despacho de Alertas

#### Enviar Alerta

- **Endpoint:** `POST /api/v1/alerts/send`
- Soporta bulk alerting: uno o varios `order_ids` en la misma petición, todos deben pertenecer al `lot_number` indicado.
- Solo persiste un registro en `alerts` cuando el correo se envía exitosamente — un fallo de envío no deja rastro en BD.
- **Request:**

  ```json
  {
    "lot_number": "951357",
    "order_ids": [1052, 1053],
    "channel": "email",
    "message": "Aviso de retiro de medicamento lote 951357. Favor suspender su uso e informar a la farmacia."
  }
  ```

  `channel` es opcional (default `email`; valores válidos: `email`, `sms`, `whatsapp`, alineados con `App\Enums\AlertChannel`).

- **Response (200 OK)** — procesa cada orden y desglosa el resultado, para que un fallo o duplicado en una orden no oscurezca el éxito de las demás:

  ```json
  {
    "message": "Alertas procesadas.",
    "data": {
      "lot_number": "951357",
      "sent": [1052],
      "skipped_duplicate": [1053],
      "failed": [],
      "invalid_order_ids": []
    }
  }
  ```

- **Response (409 Conflict):** cuando todas las órdenes de la petición ya habían sido notificadas para ese lote y canal (nada nuevo que enviar).
- **Response (422 Unprocessable Entity):** payload inválido, o ningún `order_id` corresponde al `lot_number` enviado.

---

## 5 Instrucciones de Instalación y Configuración

### 5.1 Requisitos Previos

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x & Yarn
- MySQL >= 8.0

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

## 7 Testing

```bash
php artisan test
```

Cobertura implementada:

- **Auth:** login válido/inválido, acceso no autenticado a rutas protegidas, logout.
- **Medications:** búsqueda por lote (parcial), filtro por rango de fechas (incluye/excluye según haya orden en el rango), validación de `lot` requerido.
- **Orders:** listado con paginación, filtro por lote y rango de fechas, detalle (`show`) con cliente y medicamentos precargados, `404` en orden inexistente, `401` sin autenticar.
- **Customers:** detalle (`show`), `404` en cliente inexistente.
- **Alerts:** envío individual y bulk, `409` ante reintento de una combinación ya notificada, `422` cuando ningún `order_id` corresponde al `lot_number`, verificación de que un fallo de envío **no** persiste el registro en `alerts`, verificación de que un envío exitoso sí lo persiste con `status = sent` y `sent_at` poblado.

---

## 8 Decisiones de Arquitectura y Supuestos

- El envío de alertas se marca como exitoso solo si el email realmente se despachó (no se registra una alerta "fantasma" ante un fallo de envío).
- La búsqueda de órdenes se filtra en MySQL, no en PHP, para evitar traer datos innecesarios y prevenir N+1 mediante eager loading explícito.
- Roles y permisos (RBAC) están fuera del MVP obligatorio; se documentan como bonus en `ROLES_PERMISSIONS_SCHEMA.md`

---

## 9 Credenciales de Prueba

_(Completar tras correr el seeder — usuario/contraseña generados en `DatabaseSeeder`.)
