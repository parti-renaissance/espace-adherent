<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Unsubscribe;

use App\Ses\Unsubscribe\UnsubscribeUrlGenerator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UnsubscribeUrlGeneratorTest extends KernelTestCase
{
    private const UUID = '11111111-1111-4111-8111-111111111111';
    private const MESSAGE_UUID = '22222222-2222-4222-8222-222222222222';
    private const TOKEN_SECRET = 'generator-test-secret-0123456789abcd';

    /**
     * Campaign emails are built in a Messenger worker with no HTTP request: the generator must still
     * produce an absolute URL on the configured host (route default %user_vox_host%).
     */
    public function testGenerateProducesAbsoluteUrlWithoutRequestContext(): void
    {
        self::bootKernel();

        $url = self::getContainer()->get(UnsubscribeUrlGenerator::class)->generate(self::UUID);
        $voxHost = self::getContainer()->getParameter('user_vox_host');

        self::assertMatchesRegularExpression('#^https?://#', $url);
        self::assertStringContainsString($voxHost, $url);
        self::assertStringContainsString('/desabonnement/', $url);
    }

    public function testGeneratedTokenDecodesToUuid(): void
    {
        $decoded = $this->decodeToken($this->generate(self::UUID));

        self::assertSame(self::UUID, $decoded->uuid);
    }

    public function testGeneratedTokenEncodesMemberIdWhenProvided(): void
    {
        $decoded = $this->decodeToken($this->generate(self::UUID, 42));

        self::assertSame(self::UUID, $decoded->uuid);
        self::assertSame(42, $decoded->member_id);
    }

    public function testGeneratedTokenOmitsMemberIdWhenAbsent(): void
    {
        // A plain link (no send context) must stay backward-compatible: no member_id claim at all.
        $decoded = $this->decodeToken($this->generate(self::UUID));

        self::assertObjectNotHasProperty('member_id', $decoded);
    }

    public function testGeneratedTokenEncodesMessageUuidWhenProvided(): void
    {
        $decoded = $this->decodeToken($this->generate(self::UUID, 42, self::MESSAGE_UUID));

        self::assertSame(self::MESSAGE_UUID, $decoded->message_uuid);
    }

    public function testGeneratedTokenOmitsMessageUuidWhenAbsent(): void
    {
        $decoded = $this->decodeToken($this->generate(self::UUID, 42));

        self::assertObjectNotHasProperty('message_uuid', $decoded);
    }

    private function generate(string $uuid, ?int $memberId = null, ?string $messageUuid = null): string
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnCallback(static fn (string $name, array $params): string => 'https://vox.test/desabonnement/'.$params['token']);

        return new UnsubscribeUrlGenerator($urlGenerator, self::TOKEN_SECRET)->generate($uuid, $memberId, $messageUuid);
    }

    private function decodeToken(string $url): object
    {
        $token = substr($url, (int) strrpos($url, '/') + 1);

        return JWT::decode($token, new Key(self::TOKEN_SECRET, 'HS256'));
    }
}
