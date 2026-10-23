<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correspondencia extends Model
{
    use HasFactory;

    protected $table = 'correspondencias';

    protected $fillable = [
        'numero_documento', // CITE o similar
        'tipo_documento', // nota_interna, nota_externa, informe, instructivo, memorandum
        'direccion', // ingreso, egreso
        'fecha',
        'remitente',
        'destinatario',
        'asunto',
        'archivo_adjunto',
        'estado_atencion', // pendiente, en_proceso, atendido
        'user_id',
    ];

    /**
     * Get the user that created the correspondence.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $casts = [
        'fecha' => 'date',
    ];
}
