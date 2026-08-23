<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use OpenApi\Attributes as OA;

class CustomerController extends Controller
{
    #[OA\Get(
        path: "/customers/{customer}",
        summary: "Obtener detalle de un cliente",
        description: "Devuelve los datos de perfil de un comprador registrado por su ID.",
        tags: ["Clientes"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "customer",
                in: "path",
                required: true,
                description: "ID único del cliente",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalle del cliente obtenido exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                new OA\Property(property: "email", type: "string", example: "juan.perez@email.com"),
                                new OA\Property(property: "phone", type: "string", example: "+573001234567")
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
                description: "Cliente no encontrado"
            )
        ]
    )]
    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }
}
