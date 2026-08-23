<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Pharmacovigilance API",
    description: "API REST Core para la gestión de retiros de lotes sanitarios, órdenes, clientes y alertas."
)]
#[OA\Server(
    url: "/api/v1",
    description: "Servidor Principal API v1"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}