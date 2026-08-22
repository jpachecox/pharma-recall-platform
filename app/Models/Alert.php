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

    /** @var array<int, string> */
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
     * Filtra alertas por estado de forma condicional.
     * Acepta tanto un string ('pending') como una instancia de AlertStatus.
     *
     * @param  Builder  $query
     * @param  AlertStatus|string|null  $status
     * @return Builder
     */
    public function scopeStatus(Builder $query, AlertStatus|string|null $status): Builder
    {
        return $query->when($status, function (Builder $q, AlertStatus|string $value) {
            $statusValue = $value instanceof AlertStatus ? $value->value : $value;

            return $q->where('status', $statusValue);
        });
    }

    /**
     * Filtra alertas por canal de envío.
     * Acepta tanto un string ('email') como una instancia de AlertChannel.
     *
     * @param  Builder  $query
     * @param  AlertChannel|string  $channel
     * @return Builder
     */
    public function scopeByChannel(Builder $query, AlertChannel|string $channel): Builder
    {
        $channelValue = $channel instanceof AlertChannel ? $channel->value : $channel;

        return $query->where('channel', $channelValue);
    }
}