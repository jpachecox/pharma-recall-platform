<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    use HasFactory;

    protected $table = 'medications';

    protected $primaryKey = 'id';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'description',
        'lot_number',
    ];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    // Filtra medicamentos por coincidencia exacta o parcial de lote
    public function scopeSearchByLot(Builder $query, string $lotNumber): Builder
    {
        return $query->where('lot_number', 'LIKE', "%{$lotNumber}%");
    }
}
