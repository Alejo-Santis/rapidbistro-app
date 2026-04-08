<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'uuid',
        'zone_id',
        'number',
        'capacity',
        'min_capacity',
        'status',
    ];

    protected $casts = [
        'capacity'     => 'integer',
        'min_capacity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Uuid::uuid4()->toString();
        });
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'Disponible',
            'reserved'    => 'Reservada',
            'occupied'    => 'Ocupada',
            'maintenance' => 'Mantenimiento',
            'unavailable' => 'No disponible',
            default       => ucfirst($this->status),
        };
    }
}
