<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'address',
        'city',
        'country',
        'capacity',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
