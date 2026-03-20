<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'restaurant_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'slot_duration_minutes',
        'max_concurrent_reservations',
    ];

    protected $casts = [
        'slot_duration_minutes' => 'integer',
        'max_concurrent_reservations' => 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getDayLabelAttribute(): string
    {
        return match ($this->day_of_week) {
            'monday'    => 'Lunes',
            'tuesday'   => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday'  => 'Jueves',
            'friday'    => 'Viernes',
            'saturday'  => 'Sábado',
            'sunday'    => 'Domingo',
            default     => ucfirst($this->day_of_week),
        };
    }
}
