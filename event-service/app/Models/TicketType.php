<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'quantity_sold',
        'status',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'quantity'      => 'integer',
        'quantity_sold' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->quantity - $this->quantity_sold;
    }
}
