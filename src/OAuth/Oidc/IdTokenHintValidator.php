<?php

declare(strict_types=1);

namespace App\OAuth\Oidc;

use App\Exception\InvalidUuidException;
use App\OAuth\Oidc\Exception\InvalidIdTokenHintException;
use App\Repository\OAuth\ClientRepository;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use League\OAuth2\Server\CryptKey;

class IdTokenHintValidator
{
    public function __construct(
        private readonly CryptKey $publicKey,
        private readonly string $issuer,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    public function validate(string $idTokenHint): ValidatedIdTokenHint
    {
        try {
            $token = new Parser(new JoseEncoder())->parse($idTokenHint);
        } catch (\Throwable $e) {
            throw new InvalidIdTokenHintException('Unable to parse id_token_hint', 0, $e);
        }

        \assert($token instanceof UnencryptedToken);

        $audience = $token->claims()->get(RegisteredClaims::AUDIENCE);

        if (\is_array($audience)) {
            $audience = $audience[0] ?? null;
        }

        if (!\is_string($audience) || '' === $audience) {
            throw new InvalidIdTokenHintException('id_token_hint missing aud claim');
        }

        try {
            $client = $this->clientRepository->findOneByUuid($audience);
        } catch (InvalidUuidException $e) {
            throw new InvalidIdTokenHintException('id_token_hint aud is not a valid UUID', 0, $e);
        }

        if (null === $client) {
            throw new InvalidIdTokenHintException('id_token_hint aud does not match a known client');
        }

        $publicKey = InMemory::file($this->publicKey->getKeyPath());

        $isValid = new Validator()->validate(
            $token,
            new SignedWith(new Sha256(), $publicKey),
            new IssuedBy($this->issuer),
            new PermittedFor($audience),
        );

        if (!$isValid) {
            throw new InvalidIdTokenHintException('id_token_hint failed signature/iss/aud validation');
        }

        $subject = $token->claims()->get(RegisteredClaims::SUBJECT);

        if (!\is_string($subject) || '' === $subject) {
            throw new InvalidIdTokenHintException('id_token_hint missing sub claim');
        }

        return new ValidatedIdTokenHint($subject, $audience);
    }
}
