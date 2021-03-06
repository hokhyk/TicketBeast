<?php

namespace App\Repositories\Ticket;

use Hashids\Hashids;
use Illuminate\Support\Facades\Log;


class TicketCodeGeneratorRepository implements TicketCodeGeneratorContract
{
    private $hashids;

    public function __construct($salt = null)
    {
        if($salt===null) {
            $salt = config('hashids.salt');
            Log::info('Default HashIds', ['hash' => config('hasids')]);
        }

        $this->hashids = new Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function generateFor(\App\Models\Ticket $ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}