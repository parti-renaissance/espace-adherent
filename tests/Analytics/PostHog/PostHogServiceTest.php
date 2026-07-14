<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PostHogServiceTest extends TestCase
{
    private function makeService(
        MockHttpClient $client,
        bool $enabled = true,
        string $site = 'parti-renaissance',
    ): PostHogService {
        $ctx = new SiteContext();
        $ctx->setSite($site);
        $hash = $this->createStub(HashEmailService::class);
        $hash->method('hash')->willReturn('hashed-distinct-id');

        return new PostHogService(
            httpClient: $client,
            apiHost: 'https://eu.i.posthog.com',
            apiKey: 'phc_test',
            enabled: $enabled,
            context: $ctx,
            hashEmail: $hash,
            environment: 'test',
            deploySha: 'abc1234',
            deployVersion: '1.0.0',
            logger: $this->createStub(LoggerInterface::class),
        );
    }

    public function testBuildSuperPropertiesReturnsExpectedKeys(): void
    {
        $service = $this->makeService(new MockHttpClient());
        $props = $service->buildSuperProperties();

        $expected = ['site', 'platform', 'environment', 'deploy_sha', 'deploy_version', 'locale', 'is_bot'];
        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $props, "Missing super-property: $key");
        }
        $this->assertSame('parti-renaissance', $props['site']);
        $this->assertSame('test', $props['environment']);
        $this->assertSame('abc1234', $props['deploy_sha']);
        $this->assertSame('1.0.0', $props['deploy_version']);
        $this->assertSame('web', $props['platform']);
        $this->assertFalse($props['is_bot']);
    }

    public function testBuildSuperPropertiesFallbackWhenDeployShaEmpty(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('attalpresident');
        $service = new PostHogService(
            httpClient: new MockHttpClient(),
            apiHost: 'https://eu.i.posthog.com',
            apiKey: 'phc_test',
            enabled: true,
            context: $ctx,
            hashEmail: $this->createStub(HashEmailService::class),
            environment: 'test',
            deploySha: '',
            deployVersion: '',
            logger: $this->createStub(LoggerInterface::class),
        );
        $props = $service->buildSuperProperties();
        $this->assertSame('local', $props['deploy_sha']);
        $this->assertSame('unknown', $props['deploy_version']);
    }

    public function testCaptureServerSideSkipsWhenDisabled(): void
    {
        $mockClient = new MockHttpClient(function () {
            $this->fail('httpClient should not be called when POSTHOG_ENABLED=false');
        });
        $service = $this->makeService($mockClient, enabled: false);

        $service->captureServerSide(PostHogEventName::LOGIN_SUCCEEDED, ['method' => 'form']);
        $this->assertTrue(true);
    }

    public function testCaptureServerSidePostsCorrectPayload(): void
    {
        $capturedUrl = null;
        $capturedBody = null;
        $mockClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedUrl, &$capturedBody) {
            $capturedUrl = $url;
            $capturedBody = $options['body'] ?? '';
            return new MockResponse('', ['http_code' => 200]);
        });
        $service = $this->makeService($mockClient);

        $service->captureServerSide(PostHogEventName::LOGIN_SUCCEEDED, ['method' => 'form']);

        $this->assertStringContainsString('/capture/', $capturedUrl);
        $decoded = json_decode($capturedBody, true);
        $this->assertSame('login_succeeded', $decoded['event']);
        $this->assertSame('phc_test', $decoded['api_key']);
        $this->assertSame('form', $decoded['properties']['method']);
        $this->assertSame('parti-renaissance', $decoded['properties']['site']);
        $this->assertSame('anonymous-server', $decoded['distinct_id']); // pas de user
    }

    public function testCaptureServerSideSilentFailLogsWarning(): void
    {
        $mockClient = new MockHttpClient(function () {
            throw new \RuntimeException('boom');
        });
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('failed'));
        $ctx = new SiteContext();
        $ctx->setSite('parti-renaissance');

        $service = new PostHogService(
            httpClient: $mockClient,
            apiHost: 'https://eu.i.posthog.com',
            apiKey: 'phc_test',
            enabled: true,
            context: $ctx,
            hashEmail: $this->createStub(HashEmailService::class),
            environment: 'test',
            deploySha: 'abc',
            deployVersion: '1.0',
            logger: $logger,
        );

        // Ne throw pas, log warning
        $service->captureServerSide(PostHogEventName::LOGIN_SUCCEEDED, ['method' => 'form']);
    }
}
