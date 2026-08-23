<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumOptions;

enum AlertChannel: string
{
    use HasEnumOptions;

    case EMAIL = 'email';
    case SMS = 'sms';
    case WHATSAPP = 'whatsapp';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Correo Electrónico',
            self::SMS => 'Mensaje SMS',
            self::WHATSAPP => 'WhatsApp Business',
        };
    }
}
