<?php

declare(strict_types=1);

namespace App\Utils;

class Rsa
{
    public static function decrypt($data)
    {
        $prvKey = file_get_contents(storage_path('app/prv'));
        $data = base64_decode($data);
        openssl_private_decrypt($data, $text, $prvKey);
        return $text;
    }

    public static function encrypt($data): string
    {
        $pubKey = file_get_contents(storage_path('app/pub'));
        openssl_public_encrypt($data, $cipherText, $pubKey);
        return base64_encode($cipherText);
    }
}
