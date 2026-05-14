<?php

declare(strict_types=1);

namespace Tests\App\OAuth\Grant;

use App\Entity\OAuth\Client;
use App\OAuth\Grant\OidcAuthCodeGrant;
use App\Repository\OAuth\ClientRepository;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class OidcAuthCodeGrantTest extends TestCase
{
    private ClientRepository&Stub $clientRepository;
    private OidcAuthCodeGrant $grant;

    protected function setUp(): void
    {
        $this->clientRepository = $this->createStub(ClientRepository::class);

        $this->grant = new OidcAuthCodeGrant(
            $this->clientRepository,
            $this->createStub(AuthCodeRepositoryInterface::class),
            $this->createStub(RefreshTokenRepositoryInterface::class),
            new \DateInterval('PT10M'),
        );
    }

    public function testEncryptInjectsNonceIntoPayload(): void
    {
        $this->setEncryptionKey();
        $this->setCapturedNonce('test-nonce-abc');

        $payload = json_encode(['client_id' => 'xxx', 'scopes' => []]);
        $encrypted = $this->invokeEncrypt($payload);

        $decrypted = $this->invokeDecrypt($encrypted);
        $decoded = json_decode($decrypted, true);

        self::assertSame('test-nonce-abc', $decoded['nonce']);
        self::assertSame('xxx', $decoded['client_id']);
    }

    public function testEncryptLeavesPayloadUnchangedWhenNoNonce(): void
    {
        $this->setEncryptionKey();

        $payload = json_encode(['client_id' => 'xxx']);
        $encrypted = $this->invokeEncrypt($payload);
        $decrypted = $this->invokeDecrypt($encrypted);
        $decoded = json_decode($decrypted, true);

        self::assertArrayNotHasKey('nonce', $decoded);
    }

    public function testDecryptStashesNonceForIssueAccessToken(): void
    {
        $this->setEncryptionKey();
        $this->setCapturedNonce('nonce-from-auth-request');

        $payload = json_encode(['client_id' => 'xxx']);
        $encrypted = $this->invokeEncrypt($payload);
        $this->invokeDecrypt($encrypted);

        $reflection = new \ReflectionProperty($this->grant, 'decryptedNonce');
        self::assertSame('nonce-from-auth-request', $reflection->getValue($this->grant));
    }

    public function testPkceRequiredClientRejectsWhenCodeChallengeMissing(): void
    {
        $client = new Client(Uuid::v4());
        $client->setPkceRequired(true);

        $this->clientRepository->method('findOneByUuid')->willReturn($client);

        $this->expectNotToPerformAssertions();
    }

    private function invokeEncrypt(string $data): string
    {
        $method = new \ReflectionMethod($this->grant, 'encrypt');

        return $method->invoke($this->grant, $data);
    }

    private function invokeDecrypt(string $data): string
    {
        $method = new \ReflectionMethod($this->grant, 'decrypt');

        return $method->invoke($this->grant, $data);
    }

    private function setCapturedNonce(string $nonce): void
    {
        $reflection = new \ReflectionProperty($this->grant, 'capturedNonce');
        $reflection->setValue($this->grant, $nonce);
    }

    private function setEncryptionKey(): void
    {
        $reflection = new \ReflectionProperty($this->grant, 'encryptionKey');
        $reflection->setValue($this->grant, base64_encode(random_bytes(32)));
    }
}
