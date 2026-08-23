<?php

namespace App\Enums\Concerns;

/**
 * @method static array<static> cases()
 * @method string label()
 *
 * @property string $value
 */
trait HasEnumOptions
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[] = ['value' => $case->value, 'label' => $case->label()];
        }

        return $options;
    }

    /**
     * Devuelve la etiqueta del valor si está mapeado; de lo contrario el valor crudo.
     * Útil para campos libres que no deben romper ante valores desconocidos.
     */
    public static function labelFor(?string $value): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case->label();
            }
        }

        return $value;
    }
}
