<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? (string) Str::uuid();
        });
    }

    /**
     * Las rutas usan UUID en lugar de ID para no exponer identificadores internos.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function createdReservations()
    {
        return $this->hasMany(Reservation::class, 'created_by');
    }

    public function statusLogs()
    {
        return $this->hasMany(ReservationStatusLog::class);
    }
}
