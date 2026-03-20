<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'reservation_id',
        'user_id',
        'previous_status',
        'new_status',
        'reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
