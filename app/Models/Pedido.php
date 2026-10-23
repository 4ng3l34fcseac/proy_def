<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'codigo',
        'user_id',
        'justificacion',
        'estado', // pendiente, autorizado, rechazado, despachado
        'observaciones',
        'fecha_entrega',
        'recibido_por',
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime',
    ];

    /**
     * Get the user who placed the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the materials requested in this order.
     */
    public function materiales()
    {
        return $this->belongsToMany(Material::class, 'pedido_materiales')
                    ->withPivot('cantidad_solicitada', 'cantidad_entregada')
                    ->withTimestamps();
    }

    /**
     * Get explicit pivot items.
     */
    public function items()
    {
        return $this->hasMany(PedidoMaterial::class, 'pedido_id');
    }
}
