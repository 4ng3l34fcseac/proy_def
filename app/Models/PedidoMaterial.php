<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoMaterial extends Model
{
    use HasFactory;

    protected $table = 'pedido_materiales';

    protected $fillable = [
        'pedido_id',
        'material_id',
        'cantidad_solicitada',
        'cantidad_entregada',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'integer',
        'cantidad_entregada' => 'integer',
    ];

    /**
     * Get the order.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Get the material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
