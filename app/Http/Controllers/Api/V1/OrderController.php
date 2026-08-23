<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Order\OrderIndexRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        path: "/orders",
        summary: "Listar órdenes afectadas por lote y rango de fechas",
        description: "Obtiene una lista paginada de órdenes que contienen un número de lote específico, con la opción de filtrar por rango de fechas de compra.",
        tags: ["Órdenes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "lot",
                in: "query",
                required: true,
                description: "Número de lote a investigar (Obligatorio)",
                schema: new OA\Schema(type: "string", example: "951357")
            ),
            new OA\Parameter(
                name: "start_date",
                in: "query",
                required: false,
                description: "Fecha inicial de filtro (YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-08-01")
            ),
            new OA\Parameter(
                name: "end_date",
                in: "query",
                required: false,
                description: "Fecha final de filtro (YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-08-23")
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Cantidad de elementos por página (por defecto 15)",
                schema: new OA\Schema(type: "integer", example: 15)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de órdenes obtenido exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "purchase_date", type: "string", format: "date-time", example: "2026-08-15 10:30:00"),
                                    new OA\Property(property: "customer_id", type: "integer", example: 4),
                                    new OA\Property(property: "alerts_count", type: "integer", example: 1)
                                ]
                            )
                        ),
                        new OA\Property(property: "links", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado (Token no provisto o inválido)"
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación (Ej. falta el parámetro 'lot' o rango de fechas inválido)"
            )
        ]
    )]
    public function index(OrderIndexRequest $request): AnonymousResourceCollection
    {
        $orders = Order::byLotAndDateRange(
                $request->validated('lot'),
                $request->validated('start_date'),
                $request->validated('end_date'),
            )
            ->withCount('alerts')
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    #[OA\Get(
        path: "/orders/{order}",
        summary: "Obtener detalle completo de una orden",
        description: "Devuelve los datos detallados de una orden de compra, incluyendo el cliente y los medicamentos asociados con sus cantidades y precios.",
        tags: ["Órdenes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "order",
                in: "path",
                required: true,
                description: "ID único de la orden",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle de la orden obtenido exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "purchase_date", type: "string", format: "date-time", example: "2026-08-15 10:30:00"),
                                new OA\Property(
                                    property: "customer",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 4),
                                        new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                        new OA\Property(property: "email", type: "string", example: "juan.perez@email.com"),
                                        new OA\Property(property: "phone", type: "string", example: "+573001234567")
                                    ]
                                ),
                                new OA\Property(
                                    property: "medications",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "name", type: "string", example: "Paracetamol 500mg"),
                                            new OA\Property(property: "lot_number", type: "string", example: "951357"),
                                            new OA\Property(
                                                property: "pivot",
                                                type: "object",
                                                properties: [
                                                    new OA\Property(property: "quantity", type: "integer", example: 2),
                                                    new OA\Property(property: "unit_price", type: "string", example: "12500.00")
                                                ]
                                            )
                                        ]
                                    )
                                ),
                                new OA\Property(property: "alerts_count", type: "integer", example: 1)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado (Token no provisto o inválido)"
            ),
            new OA\Response(
                response: 404,
                description: "Orden no encontrada"
            )
        ]
    )]
    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'medications'])->loadCount('alerts');

        return new OrderResource($order);
    }
}
