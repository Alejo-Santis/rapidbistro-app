<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Zone extends Model
{
    protected $fillable = [
        'uuid',
        'restaurant_id',
        'name',
        'description',
        'location',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Uuid::uuid4()->toString();
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Las rutas usan UUID en lugar de ID para no exponer identificadores internos.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getLocationLabelAttribute(): string
    {
        return match ($this->location) {
            'indoor'  => 'Interior',
            'outdoor' => 'Exterior',
            'rooftop' => 'Terraza',
            'bar'     => 'Bar',
            'private' => 'Privado',
            'lounge'  => 'Lounge',
            default   => ucfirst($this->location),
        };
    }
}
