<?php

declare(strict_types=1);

namespace App\OAuth\Oidc;

use League\OAuth2\Server\CryptKey;

class JwkSetProvider
{
    private ?string $kid = null;
    private ?array $jwk = null;

    public function __construct(private readonly CryptKey $publicKey)
    {
    }

    public function getKid(): string
    {
        $this->loadKey();

        return $this->kid;
    }

    public function getJwkSet(): array
    {
        $this->loadKey();

        return ['keys' => [$this->jwk]];
    }

    private function loadKey(): void
    {
        if (null !== $this->kid) {
            return;
        }

        $pem = $this->publicKey->getKeyContents();

        $resource = openssl_pkey_get_public($pem);
        if (false === $resource) {
            throw new \RuntimeException('Unable to load public key for JWKS.');
        }

        $details = openssl_pkey_get_details($resource);
        if (false === $details || !isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new \RuntimeException('Public key is not an RSA key.');
        }

        $der = $this->pemToDer($pem);
        $kid = substr(hash('sha256', $der), 0, 16);

        $this->kid = $kid;
        $this->jwk = [
            'kty' => 'RSA',
            'use' => 'sig',
            'alg' => 'RS256',
            'kid' => $kid,
            'n' => self::base64UrlEncode($details['rsa']['n']),
            'e' => self::base64UrlEncode($details['rsa']['e']),
        ];
    }

    private function pemToDer(string $pem): string
    {
        $stripped = preg_replace('/-----(BEGIN|END)[^-]+-----|\s+/', '', $pem) ?? '';

        $der = base64_decode($stripped, true);
        if (false === $der) {
            throw new \RuntimeException('Unable to decode PEM public key to DER.');
        }

        return $der;
    }

    private static function base64UrlEncode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
