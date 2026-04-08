<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'preferred_date',
        'preferred_time',
        'party_size',
        'notes',
        'status',
        'notified_at',
        'source',
        'arrived_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'notified_at'    => 'datetime',
        'arrived_at'     => 'datetime',
        'party_size'     => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Uuid::uuid4()->toString();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting'  => 'En espera',
            'notified' => 'Notificado',
            'booked'   => 'Reservó',
            'expired'  => 'Expirado',
            default    => ucfirst($this->status),
        };
    }
}
