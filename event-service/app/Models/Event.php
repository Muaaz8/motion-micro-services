<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    public function venue()
    {
        return $this->hasOne(Venue::class);
    }

    public function schedules()
    {
        return $this->hasMany(EventSchedule::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }
}
