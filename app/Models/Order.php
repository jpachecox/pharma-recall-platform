<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'purchase_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class, 'order_items')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Filtrar pedidos que contengan un lote específico de medicamento.
     *
     * @param  Builder  $query
     * @param  string  $lotNumber
     * @return Builder
     */
    public function scopeWhereLotNumber(Builder $query, string $lotNumber): Builder
    {
        return $query->whereHas('medications', function (Builder $q) use ($lotNumber) {
            $q->where('lot_number', $lotNumber);
        });
    }

    /**
     * Scope para filtrar pedidos por rango de fechas de compra.
     * Si no se especifica endDate, consulta hasta el día de hoy.
     * Si no se especifica startDate, consulta desde los últimos 30 días.
     *
     * @param  Builder  $query
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @return Builder
     */
    public function scopeWherePurchaseDateBetween(
        Builder $query,
        ?string $startDate = null,
        ?string $endDate = null
    ): Builder
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        return $query->whereBetween('purchase_date', [$start, $end]);
    }

    /**
     * Filtra únicamente órdenes que tienen alertas emitidas (evita JOINs manuales)
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithAlerts(Builder $query): Builder
    {
        return $query->has('alerts');
    }

    /**
     * Carga ansiosa (Eager Loading) optimizada para evitar el problema N+1 en la API.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithFullDetails(Builder $query): Builder
    {
        return $query->with([
            'customer' => function ($q) {
                $q->select('id', 'name', 'email');
            },
            'medications' => function ($q) {
                $q->select('medications.id', 'name', 'description', 'lot_number');
            }
        ]);
    }

    /**
     * Scope unificado para buscar órdenes por número de lote y rango de fechas de compra.
     * Carga ansiosa para evitar el problema N+1.
     *
     * @param Builder $query
     * @param string $lotNumber
     * @param mixed $startDate
     * @param mixed $endDate
     * @return Builder
     */
    public function scopeByLotAndDateRange(
        Builder $query,
        string $lotNumber,
        ?string $startDate = null,
        ?string $endDate = null
    ): Builder {
        return $query
            ->whereLotNumber($lotNumber)
            ->wherePurchaseDateBetween($startDate, $endDate)
            ->with(['customer', 'medications']);
    }
}
