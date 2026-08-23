<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Medication\MedicationSearchRequest;
use App\Http\Resources\MedicationResource;
use App\Models\Medication;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Attributes as OA;

class MedicationController extends Controller
{
    #[OA\Get(
        path: "/medications/search",
        summary: "Buscar medicamentos por lote y rango de fechas",
        description: "Permite filtrar medicamentos por coincidencia de prefijo de lote y opcionalmente por rango de fechas de compra de sus órdenes asociadas.",
        tags: ["Medicamentos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "lot",
                in: "query",
                required: true,
                description: "Número o prefijo del lote a buscar",
                schema: new OA\Schema(type: "string", example: "951357")
            ),
            new OA\Parameter(
                name: "start_date",
                in: "query",
                required: false,
                description: "Fecha inicial de filtro para las órdenes asociadas (YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-01-01")
            ),
            new OA\Parameter(
                name: "end_date",
                in: "query",
                required: false,
                description: "Fecha final de filtro para las órdenes asociadas (YYYY-MM-DD)",
                schema: new OA\Schema(type: "string", format: "date", example: "2026-08-31")
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Cantidad de registros por página (por defecto 15)",
                schema: new OA\Schema(type: "integer", example: 15)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Búsqueda procesada correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Paracetamol 500mg"),
                                    new OA\Property(property: "description", type: "string", example: "Lote bajo investigación de farmacovigilancia"),
                                    new OA\Property(property: "lot_number", type: "string", example: "951357"),
                                    new OA\Property(property: "orders_count", type: "integer", example: 8)
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
                description: "Error de validación en los parámetros de búsqueda"
            )
        ]
    )]
    public function search(MedicationSearchRequest $request): AnonymousResourceCollection
    {
        $lot = $request->validated('lot');
        $startDate = $request->validated('start_date');
        $endDate = $request->validated('end_date');

        $medications = Medication::searchByLot($lot)
            ->when(
                $startDate || $endDate,
                fn (Builder $query) => $query->whereHas(
                    'orders',
                    fn (Builder $q) => $q->wherePurchaseDateBetween($startDate, $endDate)
                )
            )
            ->withCount('orders')
            ->paginate($request->integer('per_page', 15));

        return MedicationResource::collection($medications);
    }
}
