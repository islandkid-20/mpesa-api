<?php

namespace App\Services;

use GuzzleHttp\Client;
use phpseclib3\Crypt\RSA;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class VodacomService
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    private function encryptApiKey($apiKey, $publicKey)
    {
        try {
            $rsa = RSA::loadPublicKey($publicKey);
            $encryptedApiKey = $rsa->withPadding(RSA::ENCRYPTION_PKCS1)->encrypt($apiKey);
            return base64_encode($encryptedApiKey);
        } catch (Exception $e) {
            $errorMessage = 'Error encrypting API key: ' . $e->getMessage();
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    public function generateSessionKey()
    {
        try {
            $publicKey = config('vodacom.mpesa_auth.PUBLIC_KEY');
            $apiKey = config('vodacom.mpesa_auth.API_KEY');
            $url = config('vodacom.mpesa_auth.SESSION_URL');
            $encryptedApiKey = $this->encryptApiKey($apiKey, $publicKey);

            $response = $this->client->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $encryptedApiKey,
                    'origin' => '*'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['output_ResponseCode']) && $responseData['output_ResponseCode'] === 'INS-0') {
                return $responseData['output_SessionID'];
            } else {
                $errorMessage = 'Error generating session key: ' . ($responseData['output_ResponseDesc'] ?? 'Unknown error');
                Log::error($errorMessage);
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            $errorMessage = 'Error generating session key: ' . $e->getMessage();
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    public function getSessionKey()
    {
        try {
            $publicKey = config('vodacom.mpesa_auth.PUBLIC_KEY');
            $generatedSessionKey = $this->generateSessionKey();
            $rsa = RSA::loadPublicKey($publicKey);
            $encryptedSessionKey = $rsa->withPadding(RSA::ENCRYPTION_PKCS1)->encrypt($generatedSessionKey);
            return base64_encode($encryptedSessionKey);
        } catch (Exception $e) {
            $errorMessage = 'Error getting session key: ' . $e->getMessage();
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    public function validVodacomPhoneNumber($phoneNumber)
    {
        try {
            $pattern = '/^(?:255|\+255|0)([6-7]{1}[4-6]{1}[0-9]{7})$/';
            if (preg_match($pattern, $phoneNumber, $matches)) {
                if (strpos($phoneNumber, '0') === 0) {
                    $phoneNumber = '255' . substr($phoneNumber, 1);
                } elseif (strpos($phoneNumber, '+255') === 0) {
                    $phoneNumber = '255' . substr($phoneNumber, 4);
                }
                return $phoneNumber;
            } else {
                $errorMessage = 'Invalid phone number format';
                Log::error($errorMessage);
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            $errorMessage = 'Error validating phone number: ' . $e->getMessage();
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    public function makeC2BPayment($amount, $phoneNumber, $reference)
    {
        try {
            $url = config('vodacom.payments.C2B_URL');
            $sessionKey = $this->getSessionKey();
            $validPhoneNumber = $this->validVodacomPhoneNumber($phoneNumber);

            if (!$validPhoneNumber) {
                $errorMessage = 'Invalid phone number';
                Log::error($errorMessage);
                throw new Exception($errorMessage);
            }

            $payload = [
                "input_Amount" => $amount,
                "input_CustomerMSISDN" => $validPhoneNumber,
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
        } catch (Exception $e) {
            $errorMessage = 'Error making C2B payment: ' . $e->getMessage();
            Log::error($errorMessage);
            throw new Exception($errorMessage);
        }
    }
}
