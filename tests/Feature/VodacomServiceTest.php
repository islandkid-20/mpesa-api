<?php

namespace Tests\Feature;

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
        $result = $vodacomService->makeC2BPayment("1000",  "0742892731", mt_rand(1000000, 9999999));

        echo $result;
        $this->assertNotEmpty($result);
    }
}
