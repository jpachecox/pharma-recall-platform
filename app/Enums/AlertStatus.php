<?php

namespace App\Enums;

use App\Enums\Concerns\HasEnumOptions;

/**
 * @method static array values()
 * @method static array options()
 */
enum AlertStatus: string
{
    use HasEnumOptions;

    case PENDING = 'pending';
    case SENT = 'sent';
    case QUEUED = 'queued';
    case FAILED = 'failed';

    /**
     * Retorna una etiqueta legible para la interfaz/API.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente de envío',
            self::SENT => 'Enviada correctamente',
            self::QUEUED => 'En cola',
            self::FAILED => 'Error al enviar',
        };
    }
}
