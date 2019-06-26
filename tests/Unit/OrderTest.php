<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function convert_order_to_array_works()
    {
        //$this->disableExceptionHandling();

        $concert = factory(Concert::class)->create([
            'ticket_price' => 100
        ]);

        $concert->addTickets(10);

        $order = $concert->orderTickets('array@order.com', 6);

        $result = $order->toArray();

        $this->assertEquals([
            'email' => 'array@order.com',
            'ticket_quantity' => 6,
            'amount' => 600 //100 * 6
        ], $result);
    }

    /**
     * @test
     */
    function tickets_are_released_when_an_order_is_cancelled()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(15);
        $order = $concert->orderTickets('tu3@order.com', 6);
        $this->assertEquals(9, $concert->remainingTickets());//9+6==15

        $order->cancel();

        $this->assertEquals(15, $concert->remainingTickets());
        $this->assertNull(Order::find($order->id));
    }

    /**
     * @test
     */
    function can_create_order_given_tickets_email_and_amount()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(15);

        $this->assertEquals(15, $concert->remainingTickets());

        $order = Order::forTickets($concert->findTickets(10),
            'order@tickets.com', 22500);

        $this->assertEquals('order@tickets.com', $order->email);

        $this->assertEquals(10, $order->ticket_quantity());
        $this->assertEquals(22500, $order->amount);
        $this->assertEquals(5, $concert->remainingTickets());
    }
}