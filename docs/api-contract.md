# Contrato de API v1 — Pharma Recall Platform

**Versión:** 1.0.0
**Prefijo base:** `/api/v1`
**Autenticación:** Bearer Token (Laravel Sanctum)

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

#### 201 Created

Recurso creado con éxito.

```json
{
  "message": "Resource created successfully.",
  "data": {}
}
```

#### 202 Accepted

Petición recibida y aceptada para procesamiento asíncrono (ej. colas/jobs).

```json
{
  "message": "Request accepted and queued for processing."
}
```

#### 204 No Content

La petición se completó con éxito pero no retorna cuerpo en la respuesta.

### Formato de Errores Genéricos

#### 400 Bad Request

La petición está mal formada o contiene parámetros inválidos.

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

El usuario autenticado no posee los permisos necesarios para realizar esta acción.

```json
{
  "message": "This action is unauthorized."
}
```

#### 404 Not Found

El recurso o ruta solicitada no existe.

```json
{
  "message": "Resource not found."
}
```

#### 405 Method Not Allowed

El método HTTP utilizado (GET, POST, PUT, DELETE) no está permitido para el endpoint.

```json
{
  "message": "The HTTP method is not supported for this route."
}
```

#### 409 Conflict

Conflicto con el estado actual del recurso (ej. registro duplicado).

```json
{
  "message": "Resource conflict detected."
}
```

#### 422 Unprocessable Entity

Error de validación en los campos enviados en el body de la petición.

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "batch_number": [
      "The batch_number field is required."
    ]
  }
}
```

#### 429 Too Many Requests

Se ha superado el límite de peticiones permitidas (Rate Limiting).

```json
{
  "message": "Too many requests. Please try again later."
}
```

#### 500 Internal Server Error

Error no controlado en el servidor.

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
  "message": "Login successful.",
  "access_token": "1|qX83j...token_string...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@farmacia.com"
  }
}
```

#### 2.2 **401 Unauthorized**

```json
{
  "message": "The provided credentials are incorrect."
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

---

### POST /logout

Revoca el token actual del usuario autenticado.

**Acceso:** Requiere Autenticación

**Respuestas:**

#### 2.4 **200 OK**

```json
{
  "message": "Successfully logged out."
}
```

#### 2.5 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

---

## 3. Endpoints de Órdenes (Orders)

### GET /orders

Obtiene un listado paginado de órdenes de compra.

**Acceso:** Requiere Autenticación

**Query Params:**

- `page` (int, opcional): Número de página (default: 1).
- `per_page` (int, opcional): Registros por página (default: 15).
- `status` (string, opcional): Filtrar por estado (`pending`, `completed`, `recalled`).

**Respuestas:**

#### 3.1 **200 OK**

```json
{
  "data": [
    {
      "id": 101,
      "order_number": "ORD-2026-001",
      "customer_id": 45,
      "status": "completed",
      "total_amount": 150.50,
      "created_at": "2026-08-23T04:00:00Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/orders?page=1",
    "last": "http://localhost:8000/api/v1/orders?page=5"
  },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

#### 3.2 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

---

### GET /orders/{id}

Obtiene el detalle de una orden de compra específica.

**Acceso:** Requiere Autenticación

**Parámetros de Ruta:** `id` (integer)

**Respuestas:**

#### 3.3 **200 OK**

```json
{
  "data": {
    "id": 101,
    "order_number": "ORD-2026-001",
    "customer": {
      "id": 45,
      "name": "Farmacia San José",
      "email": "contacto@sanjose.com"
    },
    "items": [
      {
        "id": 1,
        "product_name": "Paracetamol 500mg",
        "batch_number": "LOT-9928",
        "quantity": 10,
        "unit_price": 15.05
      }
    ],
    "total_amount": 150.50,
    "status": "completed",
    "created_at": "2026-08-23T04:00:00Z"
  }
}
```

#### 3.4 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

#### 3.5 **404 Not Found**

```json
{
  "message": "Order not found."
}
```

---

## 4. Endpoints de Clientes (Customers)

### GET /customers/{id}

Obtiene los detalles de una farmacia/cliente.

**Acceso:** Requiere Autenticación

**Parámetros de Ruta:** `id` (integer)

**Respuestas:**

#### 4.1 **200 OK**

```json
{
  "data": {
    "id": 45,
    "name": "Farmacia San José",
    "email": "contacto@sanjose.com",
    "phone": "+573001234567",
    "address": "Calle 10 #15-20",
    "created_at": "2026-01-10T10:00:00Z"
  }
}
```

#### 4.2 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

#### 4.3 **404 Not Found**

```json
{
  "message": "Customer not found."
}
```

---

## 5. Endpoints de Alertas (Alerts)

### POST /alerts/send

Desencadena el proceso de notificación masiva de un recall a los clientes afectados por número de lote.

**Acceso:** Requiere Autenticación

**Body Request:**

```json
{
  "recall_id": 12,
  "batch_number": "LOT-9928",
  "message": "Urgent health alert for batch LOT-9928. Please halt distribution."
}
```

**Respuestas:**

#### 5.1 **202 Accepted**

```json
{
  "message": "Alerts queued successfully for delivery.",
  "data": {
    "recall_id": 12,
    "batch_number": "LOT-9928",
    "recipients_count": 18,
    "queued_at": "2026-08-23T04:15:00Z"
  }
}
```

#### 5.2 **401 Unauthorized**

```json
{
  "message": "Unauthenticated."
}
```

#### 5.2 **422 Unprocessable Entity:** Error en los datos de entrada

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "batch_number": [
      "The batch_number field is required."
    ]
  }
}
```
