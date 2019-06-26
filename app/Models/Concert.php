<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute() 
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInFloatAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $ticket_quantity)
    {
        //get existing tickets
        $tickets = $this->findTickets($ticket_quantity);

        return $this->createOrder($email, $tickets);
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()
            ->take($quantity)->get();

        if($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException;
        }

        return $tickets;
    }

    public function createOrder($email, $tickets)
    {
        $order = $this->orders()->create([
            'email' => $email,
            'amount' => $tickets->count() * $this->ticket_price,
        ]);

        foreach($tickets as $ticket) {
            $order->tickets()->save($ticket);//associate the two
        }

        return $order;
    }

    public function addTickets($quantity)
    {
        foreach(range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }
    }

    public function remainingTickets()
    {
        return $this->tickets()->available()->count();
    }
}
