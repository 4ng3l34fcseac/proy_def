<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materiales';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'descripcion',
        'stock',
        'stock_minimo',
        'medida',
        'estado',
    ];

    /**
     * Estado por defecto al crear un material desde el modelo.
     */
    protected $attributes = [
        'estado' => 'Activo',
    ];

    /**
     * Scope local: solo materiales activos (catalogo visible).
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Scope local: solo materiales inactivos (dados de baja).
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'Inactivo');
    }

    /**
     * Helper: saber si el material esta dado de baja.
     */
    public function isInactivo(): bool
    {
        return $this->estado === 'Inactivo';
    }

    /**
     * Get the category that owns the material.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Get the stock movements for the material.
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'material_id');
    }

    /**
     * Get the orders associated with this material.
     */
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_materiales')
                    ->withPivot('cantidad_solicitada', 'cantidad_entregada')
                    ->withTimestamps();
    }

    /**
     * Check if material stock is below stock_minimo.
     */
    public function isStockLow(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }
}
