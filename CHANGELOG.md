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

### Changed

### Removed

### Fixed

- **PHV-001**: scripts de `composer.json` migrados de npm a yarn
- **PHV-001**: dependencias duplicadas removidas del frontend (radix-vue, lucide-vue-next, tailwindcss-animate, shadcn-vue como runtime dep)
- **PHV-002**: `alerts.status` — valores del enum de MySQL alineados con `App\Enums\AlertStatus`, default cambiado de `sent` a `pending`
- **PHV-004**: `OrderFactory` ahora adjunta `order_items`/medicamentos reales (antes generaba órdenes vacías)
- **PHV-004**: `AlertFactory` — `status`/`sent_at` coherentes entre sí, `customer_id` derivado del dueño real de la orden
- **PHV-004**: `AlertFactory` — agregado el estado `queued()`, faltante tras sumar `AlertStatus::QUEUED` al enum
- **PHV-005**: Modelo `User` actualizado con el trait `HasApiTokens` y `UserFactory` ajustado para incluir el campo `username`
