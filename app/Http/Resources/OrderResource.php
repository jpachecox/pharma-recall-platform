<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'purchase_date' => $this->purchase_date,
            'customer'      => [
                'id'    => $this->customer->id,
                'name'  => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ],
            'medications'    => $this->medications->map(fn ($medication) => [
                'id'         => $medication->id,
                'name'       => $medication->name,
                'lot_number' => $medication->lot_number,
                'quantity'   => $medication->pivot->quantity,
                'unit_price' => $medication->pivot->unit_price,
            ]),
            'alerts_sent'   => $this->when(
                $this->alerts_count !== null,
                fn () => $this->alerts_count
            ),
        ];
    }
}
