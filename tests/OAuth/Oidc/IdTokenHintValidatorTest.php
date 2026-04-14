<?php

declare(strict_types=1);

namespace Tests\App\OAuth\Oidc;

use App\Entity\OAuth\Client;
use App\Exception\InvalidUuidException;
use App\OAuth\Oidc\Exception\InvalidIdTokenHintException;
use App\OAuth\Oidc\IdTokenHintValidator;
use App\Repository\OAuth\ClientRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use PHPUnit\Framework\TestCase;

class IdTokenHintValidatorTest extends TestCase
{
    private const ISSUER = 'https://utilisateur.renaissance.code';
    private const CLIENT_UUID = 'd4f1f7ea-9c8e-4b1f-9a2c-7f2b3c4d5e6f';

    private string $privateKeyPath;
    private string $publicKeyPath;
    private string $otherPrivateKeyPath;

    protected function setUp(): void
    {
        [$this->privateKeyPath, $this->publicKeyPath] = $this->generateRsaKeyPair();
        [$this->otherPrivateKeyPath] = $this->generateRsaKeyPair();
    }

    protected function tearDown(): void
    {
        foreach ([$this->privateKeyPath, $this->publicKeyPath, $this->otherPrivateKeyPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testValidateAcceptsCorrectlySignedToken(): void
    {
        $jwt = $this->buildJwt(audience: self::CLIENT_UUID, subject: 'user-uuid-123');

        $result = $this->createValidator(clientExists: true)->validate($jwt);

        self::assertSame('user-uuid-123', $result->userUuid);
        self::assertSame(self::CLIENT_UUID, $result->clientUuid);
    }

    public function testValidateAcceptsExpiredTokenWithValidSignature(): void
    {
        $jwt = $this->buildJwt(
            audience: self::CLIENT_UUID,
            subject: 'user-uuid-123',
            issuedAt: new \DateTimeImmutable('-2 hours'),
            expiresAt: new \DateTimeImmutable('-1 hour'),
        );

        $result = $this->createValidator(clientExists: true)->validate($jwt);

        self::assertSame('user-uuid-123', $result->userUuid);
    }

    public function testValidateRejectsMalformedJwt(): void
    {
        $this->expectException(InvalidIdTokenHintException::class);

        $this->createValidator()->validate('not-a-jwt');
    }

    public function testValidateRejectsWrongSignature(): void
    {
        $jwt = $this->buildJwt(
            audience: self::CLIENT_UUID,
            subject: 'user-uuid-123',
            privateKeyPath: $this->otherPrivateKeyPath,
        );

        $this->expectException(InvalidIdTokenHintException::class);

        $this->createValidator(clientExists: true)->validate($jwt);
    }

    public function testValidateRejectsWrongIssuer(): void
    {
        $jwt = $this->buildJwt(
            audience: self::CLIENT_UUID,
            subject: 'user-uuid-123',
            issuer: 'https://wrong-issuer.example.com',
        );

        $this->expectException(InvalidIdTokenHintException::class);

        $this->createValidator(clientExists: true)->validate($jwt);
    }

    public function testValidateRejectsUnknownAudience(): void
    {
        $jwt = $this->buildJwt(audience: self::CLIENT_UUID, subject: 'user-uuid-123');

        $this->expectException(InvalidIdTokenHintException::class);

        $this->createValidator(clientExists: false)->validate($jwt);
    }

    public function testValidateRejectsTokenMissingSub(): void
    {
        $jwt = $this->buildJwt(audience: self::CLIENT_UUID, subject: null);

        $this->expectException(InvalidIdTokenHintException::class);

        $this->createValidator(clientExists: true)->validate($jwt);
    }

    public function testValidateRejectsTokenMissingAud(): void
    {
        $jwt = $this->buildJwt(audience: null, subject: 'user-uuid-123');

        $this->expectException(InvalidIdTokenHintException::class);

        $this->createValidator(clientExists: true)->validate($jwt);
    }

    public function testValidateRejectsNonUuidAudience(): void
    {
        $jwt = $this->buildJwt(audience: 'not-a-uuid', subject: 'user-uuid-123');

        $clientRepository = $this->createStub(ClientRepository::class);
        $clientRepository->method('findOneByUuid')->willThrowException(new InvalidUuidException('Uuid "not-a-uuid" is not valid.'));

        $validator = new IdTokenHintValidator(
            new CryptKey($this->publicKeyPath, null, false),
            self::ISSUER,
            $clientRepository,
        );

        $this->expectException(InvalidIdTokenHintException::class);

        $validator->validate($jwt);
    }

    private function generateRsaKeyPair(): array
    {
        $privateResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => \OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($privateResource, $privateKeyPem);
        $details = openssl_pkey_get_details($privateResource);

        $privatePath = tempnam(sys_get_temp_dir(), 'oidc_validator_priv_');
        file_put_contents($privatePath, $privateKeyPem);
        chmod($privatePath, 0o600);

        $publicPath = tempnam(sys_get_temp_dir(), 'oidc_validator_pub_');
        file_put_contents($publicPath, $details['key']);
        chmod($publicPath, 0o600);

        return [$privatePath, $publicPath];
    }

    private function buildJwt(
        ?string $audience,
        ?string $subject,
        ?string $issuer = self::ISSUER,
        ?string $privateKeyPath = null,
        ?\DateTimeImmutable $issuedAt = null,
        ?\DateTimeImmutable $expiresAt = null,
    ): string {
        $signingKey = InMemory::file($privateKeyPath ?? $this->privateKeyPath);
        $config = Configuration::forAsymmetricSigner(new Sha256(), $signingKey, $signingKey);

        $builder = $config->builder()
            ->issuedBy($issuer)
            ->issuedAt($issuedAt ?? new \DateTimeImmutable('now'))
            ->expiresAt($expiresAt ?? new \DateTimeImmutable('+1 hour'))
        ;

        if (null !== $audience) {
            $builder = $builder->permittedFor($audience);
        }

        if (null !== $subject) {
            $builder = $builder->relatedTo($subject);
        }

        return $builder->getToken($config->signer(), $config->signingKey())->toString();
    }

    private function createValidator(bool $clientExists = false): IdTokenHintValidator
    {
        $clientRepository = $this->createStub(ClientRepository::class);
        $clientRepository->method('findOneByUuid')->willReturn(
            $clientExists ? $this->createStub(Client::class) : null,
        );

        return new IdTokenHintValidator(
            new CryptKey($this->publicKeyPath, null, false),
            self::ISSUER,
            $clientRepository,
        );
    }
}
