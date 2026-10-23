<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'cargo',
        'telefono',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the orders (pedidos) made by the user.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * Get the stock movements registered by the user.
     */
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    /**
     * Helper to check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'administrador';
    }

    /**
     * Helper to check if user is funcionario.
     */
    public function isFuncionario(): bool
    {
        return $this->role === 'funcionario';
    }

    /**
     * Helper to check if user is active.
     * La columna en BD es 'status' y los valores son 'Activo' / 'Inactivo'.
     */
    public function isActive(): bool
    {
        return strtolower(trim((string) $this->status)) === 'activo';
    }

    /**
     * Normaliza el rol del usuario desde la base de datos.
     * Acepta 'Administrador', 'administrador', 'Funcionario', 'funcionario'
     * y devuelve el nombre legible con mayúsculas.
     */
    public function getRoleDisplayAttribute(): string
    {
        $rol = strtolower(trim((string) $this->role));
        return match ($rol) {
            'administrador', 'admin' => 'Administrador',
            'funcionario', 'usuario', 'usu' => 'Funcionario',
            default => ucfirst($rol ?: 'Funcionario'),
        };
    }

    /**
     * Devuelve true si el usuario (en cualquier variante de mayúsculas) es Administrador.
     */
    public function getIsAdminAttribute(): bool
    {
        return strtolower(trim((string) $this->role)) === 'administrador' || strtolower(trim((string) $this->role)) === 'admin';
    }

    /**
     * Devuelve true si el usuario (en cualquier variante de mayúsculas) es Funcionario.
     */
    public function getIsFuncionarioAttribute(): bool
    {
        return strtolower(trim((string) $this->role)) === 'funcionario';
    }
}
