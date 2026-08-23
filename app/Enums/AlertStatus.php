<?php

namespace App\Enums;

/**
 * @method static array values()
 * @method static array options()
 */
enum AlertStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case QUEUED = 'queued';
    case FAILED = 'failed';

    /**
     * Retorna una etiqueta legible para la interfaz/API.
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::SENT => 'Enviado',
            self::QUEUED => 'En cola',
            self::FAILED => 'Fallido',
        };
    }
}
