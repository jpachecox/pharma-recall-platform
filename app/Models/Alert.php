<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AlertChannel;
use App\Enums\AlertStatus;

class Alert extends Model
{
    use HasFactory;

    protected $table = 'alerts';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'order_id',
        'user_id',
        'lot_number',
        'channel',
        'status',
        'message_body',
        'sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'channel' => AlertChannel::class,
        'status' => AlertStatus::class,
        'sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Filtra alertas por el usuario que las disparó.
     *
     * @param  Builder  $query
     * @param  integer  $userId
     * @return Builder
     */
    public function scopeTriggeredBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Normaliza un valor string|enum a su valor primitivo, validando contra el enum indicado.
     *
     * @param string $enumClass
     * @param \BackedEnum|string $value
     * @return string
     */
    private function resolveEnumValue(string $enumClass, \BackedEnum|string $value): string
    {
        return ($value instanceof $enumClass ? $value : $enumClass::from($value))->value;
    }

    /**
     * Filtra alertas por estado de forma condicional y segura.
     *
     * @param  Builder  $query
     * @param  AlertStatus|string  $status
     * @return Builder
     */
    public function scopeStatus(Builder $query, AlertStatus|string $status): Builder
    {
        return $query->where('status', $this->resolveEnumValue(AlertStatus::class, $status));
    }

    /**
     * Filtra alertas por canal de envío de forma condicional y segura.
     *
     * @param  Builder  $query
     * @param  AlertChannel|string  $channel
     * @return Builder
     */
    public function scopeByChannel(Builder $query, AlertChannel|string $channel): Builder
    {
        return $query->where('channel', $this->resolveEnumValue(AlertChannel::class, $channel));
    }
}
