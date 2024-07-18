<?php
namespace App\Services;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class MpesaEncryptionService
{
    public function encryptApiKey($apiKey, $publicKey) {
        // $rsa = PublicKeyLoader::load($publicKey)->withPadding(RSA::ENCRYPTION_PKCS1);
        $rsa = RSA::loadPublicKey($publicKey);
        $encryptedApiKey = $rsa ->withPadding(RSA::ENCRYPTION_PKCS1) ->encrypt($apiKey);
        return base64_encode($encryptedApiKey);
    }
}