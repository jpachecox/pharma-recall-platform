<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $primaryKey = 'id';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'email',
        'phone'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Filtrar clientes por término de búsqueda en nombre o correo.
     *
     * @param  Builder  $query
     * @param  string  $str
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $str): Builder
    {
        return $query->where(function (Builder $q) use ($str) {
            $q->where('name', 'LIKE', "%{$str}%")
            ->orWhere('email', 'LIKE', "%{$str}%");
        });
    }
}
