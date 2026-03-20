<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'table_id',
        'user_id',
        'created_by',
        'reservation_date',
        'starts_at',
        'ends_at',
        'party_size',
        'status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'notes',
        'internal_notes',
        'confirmation_code',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'cancelled_at'     => 'datetime',
        'party_size'       => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Uuid::uuid4()->toString();
            $model->confirmation_code = $model->confirmation_code ?? strtoupper(Str::random(8));
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLogs()
    {
        return $this->hasMany(ReservationStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Las rutas usan UUID en lugar de ID para no exponer identificadores internos.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendiente',
            'confirmed' => 'Confirmada',
            'seated'    => 'En mesa',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'no_show'   => 'No se presentó',
            default     => ucfirst($this->status),
        };
    }
}
