<?php

namespace App\Http\Requests\Alert;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AlertChannel;

class SendAlertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lot_number'    => ['required', 'string', 'max:50'],
            'order_ids'     => ['required', 'array', 'min:1'],
            'order_ids.*'   => ['required', 'integer', 'distinct', 'exists:orders,id'],
            'channel'       => ['nullable', new Enum(AlertChannel::class)],
            'message'       => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lot_number.required'  => 'El número de lote es obligatorio.',
            'order_ids.required'   => 'Debe indicar al menos una orden a notificar.',
            'order_ids.min'        => 'Debe indicar al menos una orden a notificar.',
            'order_ids.*.exists'   => 'Una o más órdenes indicadas no existen.',
            'order_ids.*.distinct' => 'No repita el mismo order_id en la lista.',
        ];
    }
}
