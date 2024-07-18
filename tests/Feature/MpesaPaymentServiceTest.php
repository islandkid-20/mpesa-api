<?php

namespace Tests\Feature;

use App\Services\MpesaEncryptionService;
use App\Services\MpesaPaymentService;
use App\Services\MpesaSessionKeyService;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class MpesaPaymentServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $mpesaEncryptionService = new MpesaEncryptionService();
        $client = new Client();
        $mpesaSessionkey = new MpesaSessionKeyService($mpesaEncryptionService, $client);
        $mpesaPaymentService = new MpesaPaymentService($mpesaSessionkey, $client);
        $reference = Str::random(6);
        $result = $mpesaPaymentService->makeC2BPayment("100",  "255753707326", $reference);
        echo $result;
        $this->assertNotEmpty($result);

    }
}
