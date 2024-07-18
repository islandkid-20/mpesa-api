<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class MpesaPaymentService
{
    protected $sessionKeyService;
    protected $client;

    public function __construct(MpesaSessionKeyService $sessionKeyService, Client $client)
    {
        $this ->sessionKeyService = $sessionKeyService;
        $this->client = $client;
    }

    public function makeC2BPayment($amount, $phoneNumber, $reference) {
        try {
            $sessionKey = $this->sessionKeyService->getSessionKey();
            $url = config('vodacom.payments.C2B_URL');
    
            Log::info("Starting C2B payment with session key: " . $sessionKey);
    
            $payload = [
                "input_Amount" => $amount,
                "input_CustomerMSISDN" => $phoneNumber,
                "input_Country" => "TZN",
                "input_Currency" => "TZS",
                "input_ServiceProviderCode" => "000000",
                "input_TransactionReference" => $reference,
                "input_ThirdPartyConversationID" => Str::random(15),
                "input_PurchasedItemsDesc" => "Mpesa Payment",
            ];
    
            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionKey,
                    'origin' => '*'
                ],
                'json' => $payload
            ]);
    
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error making C2B payment: ' . $e->getMessage());
            throw new \Exception('Error occurred while making C2B payment: ' . $e->getMessage());
        }
    }
    
}