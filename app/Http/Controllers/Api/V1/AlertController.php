<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AlertChannel;
use App\Enums\AlertStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alert\SendAlertRequest;
use App\Mail\LotRecallAlertMail;
use App\Models\Alert;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;
use Throwable;

class AlertController extends Controller
{
    #[OA\Post(
        path: "/alerts/send",
        summary: "Procesar y enviar alertas de retiro de lote",
        description: "Envía notificaciones a los clientes asociados a las órdenes afectadas por un lote defectuoso. Valida la existencia del lote en la orden y previene notificaciones duplicadas por canal.",
        tags: ["Alertas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["lot_number", "order_ids", "message"],
                properties: [
                    new OA\Property(property: "lot_number", type: "string", example: "951357"),
                    new OA\Property(
                        property: "order_ids",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 2]
                    ),
                    new OA\Property(property: "channel", type: "string", enum: ["email", "sms", "whatsapp"], example: "email"),
                    new OA\Property(property: "message", type: "string", example: "Estimado cliente, se ha detectado una novedad sanitaria en el lote 951357 de su medicamento.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Alertas procesadas correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Alertas procesadas."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "lot_number", type: "string", example: "951357"),
                                new OA\Property(property: "sent", type: "array", items: new OA\Items(type: "integer"), example: [1]),
                                new OA\Property(property: "skipped_duplicate", type: "array", items: new OA\Items(type: "integer"), example: []),
                                new OA\Property(property: "failed", type: "array", items: new OA\Items(type: "integer"), example: []),
                                new OA\Property(property: "invalid_order_ids", type: "array", items: new OA\Items(type: "integer"), example: [])
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: "Conflicto: Todas las órdenes indicadas ya habían sido notificadas previamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Las órdenes indicadas ya habían sido notificadas para este lote y canal."),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación o ninguna orden contiene el lote especificado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Ninguna de las órdenes indicadas contiene el lote especificado."),
                        new OA\Property(property: "errors", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "No autenticado (Token no provisto o inválido)"
            )
        ]
    )]
    public function send(SendAlertRequest $request): JsonResponse
    {
        $lotNumber = $request->validated('lot_number');
        $orderIds = $request->validated('order_ids');
        $channel = $request->filled('channel')
            ? AlertChannel::from($request->validated('channel'))
            : AlertChannel::EMAIL;
        $message = $request->validated('message');

        // Solo se notifican órdenes que realmente contienen el lote
        $orders = Order::whereIn('id', $orderIds)
            ->whereLotNumber($lotNumber)
            ->with('customer')
            ->get();

        $invalidOrderIds = collect($orderIds)->diff($orders->pluck('id'))->values();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'Ninguna de las órdenes indicadas contiene el lote especificado.',
                'errors'  => ['order_ids' => $invalidOrderIds],
            ], 422);
        }

        $sent = [];
        $skippedDuplicate = [];
        $failed = [];

        foreach ($orders as $order) {
            $alreadyAlerted = Alert::where('customer_id', $order->customer_id)
                ->where('order_id', $order->id)
                ->where('lot_number', $lotNumber)
                ->where('channel', $channel)
                ->exists();

            if ($alreadyAlerted) {
                $skippedDuplicate[] = $order->id;

                continue;
            }

            try {
                Mail::to($order->customer->email)
                    ->send(new LotRecallAlertMail($order, $lotNumber, $message));

                Alert::create([
                    'customer_id'  => $order->customer_id,
                    'order_id'     => $order->id,
                    'user_id'      => $request->user()->id,
                    'lot_number'   => $lotNumber,
                    'channel'      => $channel,
                    'status'       => AlertStatus::SENT,
                    'message_body' => $message,
                    'sent_at'      => now(),
                ]);

                $sent[] = $order->id;
            } catch (Throwable $e) {

                Log::warning('No se pudo enviar la alerta de retiro de lote.', [
                    'order_id'   => $order->id,
                    'lot_number' => $lotNumber,
                    'error'      => $e->getMessage(),
                ]);

                $failed[] = $order->id;
            }
        }

        $data = [
            'lot_number'         => $lotNumber,
            'sent'               => $sent,
            'skipped_duplicate'  => $skippedDuplicate,
            'failed'             => $failed,
            'invalid_order_ids'  => $invalidOrderIds,
        ];

        if (empty($sent) && empty($failed) && ! empty($skippedDuplicate)) {
            return response()->json([
                'message' => 'Las órdenes indicadas ya habían sido notificadas para este lote y canal.',
                'data'    => $data,
            ], 409);
        }

        return response()->json([
            'message' => 'Alertas procesadas.',
            'data'    => $data,
        ], 200);
    }
}