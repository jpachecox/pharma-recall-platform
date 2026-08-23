# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/),
y este proyecto usa [Conventional Commits](https://www.conventionalcommits.org/) para nombrar cada entrada.

## [Unreleased]

### Added

- **PHV-000**: Templates de issue y PR (`task`, `bug`, `feature`, `pull_request`)
- **PHV-001**: Proyecto base Laravel 12 + Vue 3 + TypeScript + Tailwind + Shadcn Vue
- **PHV-002**: migraciones de las 6 tablas del MVP (customers, medications, orders, order_items, alerts) y de RBAC (permissions, roles, role_has_permissions, user_has_roles)
- **PHV-003**: modelos Eloquent con relaciones y scopes (Order, OrderItem, Medication, Customer, Alert)
- **PHV-005**: Sistema de autenticación de API utilizando Laravel Sanctum (Endpoints, protección de rutas y test de integración)
- **PHV-006**: Definición del contrato de API v1 (`docs/api-contract.md`) con especificación de endpoints, códigos de estado HTTP y respuestas de error en inglés.
- **PHV-008**: `GET /api/v1/orders/{order}` — detalle de una orden vía route model binding (404 automático si no existe), con `customer` y `medications` precargados.
- **PHV-008**: `GET /api/v1/customers/{customer}` — detalle de un cliente vía route model binding.
- **PHV-008**: `POST /api/v1/alerts/send` — envío de alertas con soporte de bulk alerting (`order_ids`), validación de que cada orden corresponda al `lot_number` enviado, deduplicación contra `uq_alert_dedupe` (`409 Conflict`), y persistencia del registro en `alerts` únicamente si el envío del correo fue exitoso. Incluye `SendAlertRequest`, `App\Mail\LotRecallAlertMail` y su vista Blade.
- **PHV-008**: `App\Http\Resources\OrderResource`, `CustomerResource` y `MedicationResource` — serialización consistente para `orders`, `orders/{id}`, `customers/{id}` y `medications/search`; `OrderController@index` y `MedicationController@search` se refactorizan para usarlos en vez de arrays inline.
- **PHV-008**: Suite de tests de integración para los 6 endpoints de la API v1 (auth, medications/search, orders index/show, customers/show, alerts/send), incluyendo casos de `401`/`404`/`409`/`422` y verificación de que un fallo de envío de correo no persiste el registro de alerta.

### Changed

- **PHV-007**: `docs/api-contract.md` y `docs/openapi.yaml` reescritos para reflejar únicamente los endpoints implementados y los nombres de campo reales del esquema (`lot_number`, `purchase_date`, `quantity`, `unit_price`), eliminando referencias a un contrato genérico (`order_number`, `status`, `total_amount`, `batch_number` después de corregir las migraciones.
- **PHV-008**: `docs/api-contract.md` y `docs/openapi.yaml` — se documentan `orders/{id}`, `customers/{id}` y `alerts/send` (antes listados en la sección "Pendientes"); el contrato de API queda completo con los 6 endpoints implementados.
- **PHV-008**: `bootstrap/app.php` — se normaliza la respuesta de `ModelNotFoundException` en rutas `/api/*` a `{"message": "Resource not found."}`, alineada con `docs/api-contract.md`, en vez del mensaje verboso por defecto de Laravel.

### Removed

### Fixed

- **PHV-001**: scripts de `composer.json` migrados de npm a yarn
- **PHV-001**: dependencias duplicadas removidas del frontend (radix-vue, lucide-vue-next, tailwindcss-animate, shadcn-vue como runtime dep)
- **PHV-002**: `alerts.status` — valores del enum de MySQL alineados con `App\Enums\AlertStatus`, default cambiado de `sent` a `pending`
- **PHV-004**: `OrderFactory` ahora adjunta `order_items`/medicamentos reales (antes generaba órdenes vacías)
- **PHV-004**: `AlertFactory` — `status`/`sent_at` coherentes entre sí, `customer_id` derivado del dueño real de la orden
- **PHV-004**: `AlertFactory` — agregado el estado `queued()`, faltante tras sumar `AlertStatus::QUEUED` al enum
- **PHV-005**: Modelo `User` actualizado con el trait `HasApiTokens` y `UserFactory` ajustado para incluir el campo `username`
- **PHV-007**: `alerts.sent_at` — se quita `useCurrent()` y la columna pasa a `nullable()`; antes toda alerta quedaba marcada como "enviada" desde su creación sin importar su `status` real (`queued`/`pending`/`failed`).
- **PHV-007**: `alerts`/`orders` — `customer_id`/`order_id` cambian de `onDelete('cascade')` a `restrictOnDelete()` para no perder el historial de pedidos y alertas (evidencia de notificación de recall) al eliminar un cliente.
- **PHV-007**: `order_items` — agregado `unique(['order_id', 'medication_id'])` para impedir líneas duplicadas del mismo medicamento dentro de un mismo pedido; se quita el `default(0.00)` de `unit_price` para forzar un precio explícito.
- **PHV-007**: `alerts.channel` — pasa de `string(20)` libre a `enum(['email','sms','whatsapp'])` a nivel de BD, alineado con `App\Enums\AlertChannel`. Se agrega `unique(['customer_id','order_id','lot_number','channel'])` (`uq_alert_dedupe`) para evitar alertas duplicadas ante reintentos.
- **PHV-007**: `DatabaseSeeder` — `attach()` reemplazado por `syncWithoutDetaching()` y `Alert::factory()->create()` por `Alert::firstOrCreate()`, para que el seeder sea idempotente y no falle con `UniqueConstraintViolationException` al correrse más de una vez sin `migrate:fresh`.
- **PHV-008**: `DatabaseSeeder` — `attach()` reemplazado por `syncWithoutDetaching()` y `Alert::factory()->create()` por `Alert::firstOrCreate()`, para que el seeder sea idempotente y no falle con `UniqueConstraintViolationException` al correrse más de una vez sin `migrate:fresh`.
