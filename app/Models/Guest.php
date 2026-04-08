<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'restaurant_id',
        'name',
        'email',
        'phone',
        'birthday',
        'anniversary',
        'allergies',
        'preferences',
        'staff_notes',
        'is_vip',
    ];

    protected $casts = [
        'birthday'    => 'date',
        'anniversary' => 'date',
        'is_vip'      => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($m) => $m->uuid = $m->uuid ?? Uuid::uuid4()->toString());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function reservations()
    {
        return Reservation::where('guest_email', $this->email)
            ->where('restaurant_id', $this->restaurant_id)
            ->orderBy('reservation_date', 'desc');
    }
}
