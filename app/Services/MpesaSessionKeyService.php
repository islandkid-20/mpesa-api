<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\RSA;

class MpesaSessionKeyService {
    protected $mpesaEncryptionService;
    protected $client;
    protected $cacheKey = 'mpesa_session_key';

    public function __construct(MpesaEncryptionService $mpesaEncryptionService, Client $client) {
        $this->mpesaEncryptionService = $mpesaEncryptionService;
        $this->client = $client;
    }

    public function generateSessionKey() {
        $apiKey = config('vodacom.mpesa_auth.API_KEY');
        $publicKey = config('vodacom.mpesa_auth.PUBLIC_KEY');
        $url = config('vodacom.mpesa_auth.SESSION_URL');

        $encryptedKey = $this->mpesaEncryptionService->encryptApiKey($apiKey, $publicKey);

        try {
            $response = $this->client->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $encryptedKey,
                    'origin' => '*'
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['output_ResponseCode']) && $responseData['output_ResponseCode'] === 'INS-0') {
                return $responseData['output_SessionID'];
            } else {
                throw new \Exception('Error: ' . ($responseData['output_ResponseDesc'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate session key: ' . $e->getMessage());
        }
    }

    public function getSessionKey() {
        $publicKey = config('vodacom.mpesa_auth.PUBLIC_KEY');
        $generatedSessionKey = $this->generateSessionKey();

        // Encrypt session key with public key
        $rsa = RSA::loadPublicKey($publicKey);
        $encryptedSessionKey = $rsa ->withPadding(RSA::ENCRYPTION_PKCS1) ->encrypt($generatedSessionKey);
        Log::info("Encrypted session key: " . $encryptedSessionKey);
        return base64_encode($encryptedSessionKey);
       
    }
}
