<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $gateway)
    {
        $this->paymentGateway = $gateway;
        //Log::info('Constructor has been called to do the binding');
    }

    public function store($id) 
    {
        $concert = Concert::published()->findOrFail($id);

        $this->validate(request(), [
            'email' => 'bail|required|email',
            'ticket_quantity' => 'bail|required|integer|min:1',
            'payment_token' => 'bail|required'
        ]);

        try{
            //Find some tickets lying around
            $tickets = $concert->findTickets(request('ticket_quantity'));

            //Charging the customer
            $this->paymentGateway
                ->charge($tickets->sum('price'), request('payment_token'));

            //Creating the order
            $order = Order::forTickets($tickets, request('email'));

            return response()->json($order->toArray(), 201);
        }catch(PaymentFailedException $e) {
            return response()->json([], 422);
        }catch(NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
