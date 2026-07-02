<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\AdherentMessage;

use App\DataFixtures\ORM\LoadAdherentMessageData;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class PreviewControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testDraftRendersAssembledEmailHtml(): void
    {
        $this->client->request('GET', '/publications/'.LoadAdherentMessageData::MESSAGE_01_UUID);

        self::assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        // Stable campaign-chrome markers, independent of the faker-random body (cf. SesMessageAssemblerTest).
        self::assertStringContainsString('Si vous ne souhaitez plus recevoir nos communications', $content);
        // Recipient-level Dictionary code left raw in the preview (no recipient at compose time).
        self::assertStringContainsString('{{unsubscribe_url}}', $content);
    }

    public function testDraftPreviewCarriesHardeningHeaders(): void
    {
        $this->client->request('GET', '/publications/'.LoadAdherentMessageData::MESSAGE_01_UUID);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Security-Policy', "script-src 'none'");
        self::assertResponseHeaderSame('X-Robots-Tag', 'noindex, nofollow');
        self::assertResponseHeaderSame('Referrer-Policy', 'no-referrer');
        // The framework prepends max-age/must-revalidate; assert our no-store directive is present.
        self::assertStringContainsString('no-store', (string) $this->client->getResponse()->headers->get('Cache-Control'));
    }

    public function testSentMessageReturns404(): void
    {
        $this->client->request('GET', '/publications/'.LoadAdherentMessageData::MESSAGE_02_UUID);

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnknownUuidReturns404(): void
    {
        $this->client->request('GET', '/publications/a3e3a3e3-0000-4000-8000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }

    public function testMalformedUuidReturns404(): void
    {
        // %pattern_uuid% requirement rejects the route → 404, NOT a 500 from the uuid type conversion.
        $this->client->request('GET', '/publications/not-a-uuid');

        self::assertResponseStatusCodeSame(404);
    }

    public function testSentMessagePreviewableByAdminOnAdminHost(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('admin_renaissance_host'));
        $this->authenticateAsAdmin($this->client);

        // A sent message 404s publicly, but ROLE_ADMIN_DASHBOARD lifts the guard on the admin host.
        $this->client->request('GET', '/publications/'.LoadAdherentMessageData::MESSAGE_02_UUID);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Si vous ne souhaitez plus recevoir nos communications', (string) $this->client->getResponse()->getContent());
    }

    public function testSentMessageNotPreviewableForAnonymousOnAdminHost(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('admin_renaissance_host'));

        $this->client->request('GET', '/publications/'.LoadAdherentMessageData::MESSAGE_02_UUID);

        // Admin host access_control requires ROLE_ADMIN_DASHBOARD → anonymous is redirected to the admin login.
        self::assertResponseStatusCodeSame(302);
    }

    public function testRouteAbsentOnCampaignHostReturns404(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_campaign_host'));

        $this->client->request('GET', '/publications/'.LoadAdherentMessageData::MESSAGE_01_UUID);

        // Route is imported under Renaissance/ → %user_vox_host% only; absent on campaign host.
        self::assertResponseStatusCodeSame(404);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }
}
