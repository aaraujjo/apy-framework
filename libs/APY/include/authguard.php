<?php

class AuthGuard
{
    private $SecretKey = "";
    private $Algorithm = "";
    private $Hash = "";

    function __construct($args)
    {
        $this->SecretKey = openssl_digest($args['secret'], $args['hash'], TRUE);
        $this->Algorithm = $args['algorithm'];
        $this->Hash = $args['hash'];
    }

    function createToken($user_id)
    {
        $user = [
            "userID" => $user_id,
            "issuedAt" => time(),
            "expiresIn" => (time() + 3600)
        ];

        $ivlen = openssl_cipher_iv_length($this->Algorithm);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $raw = openssl_encrypt(json_encode($user), $this->Algorithm, $this->SecretKey, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac($this->Hash, $raw, $this->SecretKey, true);
        return base64_encode($iv . $hmac . $raw);
    }

    function isValidToken($token)
    {
        $token = base64_decode($token);

        $ivlen = openssl_cipher_iv_length($this->Algorithm);
        $iv = substr($token, 0, $ivlen);

        $hmac = substr($token, $ivlen, $sha2len = 32);
        $raw = substr($token, $ivlen + $sha2len);

        try {
            if (hash_equals($hmac, hash_hmac($this->Hash, $raw, $this->SecretKey, true))) {
                if ($decode = openssl_decrypt($raw, $this->Algorithm, $this->SecretKey, OPENSSL_RAW_DATA, $iv)) {
                    return true;
                }
            }
            return false;
        } catch (Exception $err) {
            return false;
        }
    }
}
