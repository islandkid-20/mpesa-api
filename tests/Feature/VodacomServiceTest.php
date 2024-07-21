<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\VodacomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use GuzzleHttp\Client;

class VodacomServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $client = new Client();
        $vodacomService = new VodacomService($client);
        $order = Order::first();
        echo $order->id;
        $result = $vodacomService->makeC2BPayment(1000,  "0742892731",  $order);

        var_dump($result);
        $this->assertNotEmpty($result);
    }
}
