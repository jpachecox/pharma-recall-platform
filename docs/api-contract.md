# Contrato de API v1 — Pharma Recall Platform

**Versión:** 1.1.0
**Prefijo base:** `/api/v1`
**Autenticación:** Bearer Token (Laravel Sanctum)

> Este documento refleja únicamente los endpoints implementados en el
> código actual, con los nombres de campo reales del esquema de base de
> datos (`lot_number`, `purchase_date`, etc). Los endpoints aún no
> construidos están listados al final en "Pendientes" para no perder de
> vista el scope original del PDF de la prueba.

---

## 1. Convenciones Generales y Estándar de Respuestas

### Encabezados HTTP Requeridos

```http
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

### Respuestas Exitosas Genéricas

#### 200 OK

Petición procesada correctamente.

```json
{
  "message": "Operation completed successfully."
}
```

#### 202 Accepted

Petición recibida y aceptada para procesamiento asíncrono (ej. colas/jobs). Reservado para `POST /alerts/send` (pendiente).

#### 204 No Content

La petición se completó con éxito pero no retorna cuerpo en la respuesta.

### Formato de Errores Genéricos

#### 400 Bad Request

```json
{
  "message": "Bad request. Please check your payload."
}
```

#### 401 Unauthorized

Token de autenticación no proporcionado, expirado o inválido.

```json
{
  "message": "Unauthenticated."
}
```

#### 403 Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

#### 404 Not Found

```json
{
  "message": "Resource not found."
}
```

#### 405 Method Not Allowed

```json
{
  "message": "The HTTP method is not supported for this route."
}
```

#### 409 Conflict

Conflicto con el estado actual del recurso (ej. alerta duplicada — ver constraint `uq_alert_dedupe`).

```json
{
  "message": "Resource conflict detected."
}
```

#### 422 Unprocessable Entity

Error de validación en los campos enviados en el body o query de la petición.

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "lot": [
      "The lot field is required."
    ]
  }
}
```

#### 429 Too Many Requests

```json
{
  "message": "Too many requests. Please try again later."
}
```

#### 500 Internal Server Error

```json
{
  "message": "Server error. Please try again later."
}
```

---

## 2. Endpoints de Autenticación (Auth)

### POST /login

Inicia sesión y genera un token de acceso Bearer.

**Acceso:** Público

**Body Request:**

```json
{
  "email": "admin@farmacia.com",
  "password": "password123"
}
```

**Respuestas:**

#### 2.1 **200 OK**

```json
{
  "message": "Inicio de sesión exitoso.",
  "access_token": "1|qX83j...token_string...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Administrador Farmacovigilancia",
    "email": "admin@farmacia.com"
  }
}
```

#### 2.2 **401 Unauthorized**

```json
{
  "message": "Las credenciales proporcionadas son incorrectas."
}
```

#### 2.3 **422 Unprocessable Entity:** Error de validación de campos

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

### POST /logout

Revoca el token actual del usuario autenticado.

**Acceso:** Requiere Autenticación

**Respuestas:**

#### 2.4 **200 OK**

```json
{
  "message": "Sesión cerrada correctamente."
}
```

#### 2.5 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

---

## 3. Endpoints de Medicamentos (Medications)

### GET /medications/search

Busca medicamentos por número de lote (coincidencia parcial). Si se envía
un rango de fechas, filtra únicamente los medicamentos que tuvieron al
menos una orden de compra dentro de ese rango — confirma que el lote
realmente circuló en la ventana investigada.

**Acceso:** Requiere Autenticación

**Query Params:**

- `lot` (string, **requerido**): número de lote a buscar.
- `start_date` (date, opcional): inicio del rango de compra. Default: hace 30 días.
- `end_date` (date, opcional): fin del rango de compra. Default: hoy. Debe ser `>= start_date`.
- `per_page` (int, opcional): registros por página (default: 15).

**Respuestas:**

#### 3.1 **200 OK**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Paracetamol 500mg (Afectado)",
      "description": "Lote bajo investigación de farmacovigilancia",
      "lot_number": "951357",
      "orders_count": 8,
      "created_at": "2026-08-22T20:43:06Z",
      "updated_at": "2026-08-22T20:43:06Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/medications/search?lot=951357&page=1",
    "last": "http://localhost:8000/api/v1/medications/search?lot=951357&page=1"
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

#### 3.2 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

#### 3.3 **422 Unprocessable Entity**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "lot": ["El número de lote es obligatorio."]
  }
}
```

---

## 4. Endpoints de Órdenes (Orders)

### GET /orders

Obtiene un listado paginado de órdenes que contienen un medicamento con
el número de lote indicado, dentro de un rango de fecha de compra.

**Acceso:** Requiere Autenticación

**Query Params:**

- `lot` (string, **requerido**): número de lote a buscar.
- `start_date` (date, opcional): default hace 30 días.
- `end_date` (date, opcional): default hoy. Debe ser `>= start_date`.
- `per_page` (int, opcional): registros por página (default: 15).

**Respuestas:**

#### 4.1 **200 OK**

```json
{
  "data": [
    {
      "id": 3,
      "purchase_date": "2026-08-05T14:30:00Z",
      "customer": {
        "id": 12,
        "name": "María Alvear",
        "email": "maria.alvear@example.com",
        "phone": "+573001234567"
      },
      "medications": [
        {
          "id": 1,
          "name": "Paracetamol 500mg (Afectado)",
          "lot_number": "951357",
          "quantity": 2,
          "unit_price": "12500.00"
        }
      ],
      "alerts_sent": 1
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/orders?lot=951357&page=1",
    "last": "http://localhost:8000/api/v1/orders?lot=951357&page=1"
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 8
  }
}
```

#### 4.2 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

#### 4.3 **422 Unprocessable Entity**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "lot": ["El número de lote es obligatorio."]
  }
}
```

---

## 5. Pendientes (no implementados aún)

Estos endpoints estaban en el scope original del PDF de la prueba pero
todavía no tienen controller/ruta. Se documentan aquí solo como
referencia de lo que falta, no como contrato vigente:

- `GET /orders/{id}` — detalle de una orden puntual (3.4 "View Order").
- `GET /customers/{id}` — detalle de un cliente (3.4 "View Buyer").
- `POST /alerts/send` — envío de alerta individual o masiva por email/SMS/WhatsApp (3.5), usando `App\Models\Alert` y el enum `AlertChannel` (`email`, `sms`, `whatsapp`) ya existentes.
