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
        $secret = 'generator-test-secret-0123456789abcd';

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnCallback(static fn (string $name, array $params): string => 'https://vox.test/desabonnement/'.$params['token']);

        $url = new UnsubscribeUrlGenerator($urlGenerator, $secret)->generate(self::UUID);

        $token = substr($url, (int) strrpos($url, '/') + 1);
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        self::assertSame(self::UUID, $decoded->uuid);
    }
}
