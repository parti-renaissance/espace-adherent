<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog\Twig;

use App\Analytics\PostHog\ConsentCookieHelper;
use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\PostHogService;
use App\Analytics\PostHog\SiteContext;
use App\Analytics\PostHog\Twig\PostHogTwigExtension;
use App\Entity\Adherent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PostHogTwigExtensionTest extends TestCase
{
    private function makeExtension(
        SiteContext $ctx,
        ?ConsentCookieHelper $cookieHelper = null,
        ?Request $request = null,
        bool $enabled = true,
        string $apiKey = 'phc_test',
    ): PostHogTwigExtension {
        $service = $this->createStub(PostHogService::class);
        $service->method('buildSuperProperties')->willReturn(['site' => $ctx->isInitialized() ? $ctx->getSite() : 'none']);
        $hash = $this->createStub(HashEmailService::class);
        $hash->method('hash')->willReturn('hashed-distinct-id');

        $stack = new RequestStack();
        if ($request !== null) {
            $stack->push($request);
        }
        $cookieHelper ??= $this->createStub(ConsentCookieHelper::class);

        return new PostHogTwigExtension(
            context: $ctx,
            service: $service,
            hashEmail: $hash,
            cookieHelper: $cookieHelper,
            requestStack: $stack,
            enabled: $enabled,
            apiKey: $apiKey,
        );
    }

    public function testGlobalsExposeSiteAndCookieAndConfig(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('attalpresident');
        $request = Request::create('/', 'GET', cookies: ['ap_consent' => '1']);
        $cookieHelper = $this->createMock(ConsentCookieHelper::class);
        $cookieHelper->method('read')->willReturn(true);
        $ext = $this->makeExtension($ctx, $cookieHelper, $request);

        $globals = $ext->getGlobals();
        $this->assertSame('attalpresident', $globals['posthog_site']);
        $this->assertSame('ap_consent', $globals['posthog_consent_cookie_name']);
        $this->assertSame('.attalpresident.fr', $globals['posthog_consent_cookie_domain']);
        $this->assertTrue($globals['posthog_config_enabled']);
        $this->assertSame('phc_test', $globals['posthog_config_api_key']);
        $this->assertTrue($globals['posthog_consent_state']);
    }

    public function testGlobalsWhenSiteContextNotInitialized(): void
    {
        $ctx = new SiteContext(); // pas init (admin/api/webhooks)
        $ext = $this->makeExtension($ctx);

        $globals = $ext->getGlobals();
        $this->assertFalse($globals['posthog_config_enabled']);
        $this->assertSame('', $globals['posthog_config_api_key']);
        $this->assertNull($globals['posthog_consent_state']);
        $this->assertNull($globals['posthog_site']);
        $this->assertNull($globals['posthog_consent_cookie_name']);
        $this->assertNull($globals['posthog_consent_cookie_domain']);
    }

    public function testIdentifyPayloadNullWithoutUser(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('parti-renaissance');
        $ext = $this->makeExtension($ctx);
        $this->assertNull($ext->identifyPayload(null));
    }

    public function testIdentifyPayloadContainsHashAndSetOnce(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('parti-renaissance');
        $ext = $this->makeExtension($ctx);

        $user = $this->createMock(Adherent::class);
        $user->method('getEmailAddress')->willReturn('test@example.com');
        $user->method('getPublicId')->willReturn('123ABCD');

        $payload = $ext->identifyPayload($user);

        $this->assertNotNull($payload);
        $this->assertSame('hashed-distinct-id', $payload['distinct_id']);
        $this->assertSame('123ABCD', $payload['$set']['public_id']);
        $this->assertSame('parti-renaissance', $payload['$set_once']['identified_from_site']);
        $this->assertArrayHasKey('identified_at', $payload['$set_once']);
    }
}
