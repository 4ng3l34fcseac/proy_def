<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'material_id',
        'tipo', // ingreso, salida
        'cantidad',
        'fecha',
        'motivo',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'cantidad' => 'integer',
    ];

    /**
     * Get the material affected by this movement.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    /**
     * Get the user who registered this movement.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
