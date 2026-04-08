<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialDate extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
        'date',
        'type',
        'description',
        'capacity_override',
        'booking_allowed',
        'color',
    ];

    protected $casts = [
        'date'             => 'date',
        'booking_allowed'  => 'boolean',
        'capacity_override'=> 'integer',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'event'   => 'Evento especial',
            'blocked' => 'Fecha bloqueada',
            'limited' => 'Aforo limitado',
            default   => ucfirst($this->type),
        };
    }
}
