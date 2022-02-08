<?php

namespace Tests\App\Controller\EnMarche;

use App\Mailchimp\Contact\ContactStatusEnum;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class MailchimpWebhookControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const URL = '/mailchimp/webhook/abc';

    public function testSubscribeAdherentViaMailchimpWebhook(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('adherent-female-f@en-marche-dev.fr');

        self::assertFalse($adherent->isEmailSubscribed());
        self::assertCount(0, $adherent->getSubscriptionTypeCodes());

        $data = [
            'type' => 'subscribe',
            'data' => [
                'list_id' => '123',
                'email' => 'adherent-female-f@en-marche-dev.fr',
            ],
        ];

        $this->client->request(Request::METHOD_POST, self::URL, $data);

        self::assertResponseIsSuccessful();

        self::assertTrue($adherent->isEmailSubscribed());
        self::assertCount(7, $adherent->getSubscriptionTypeCodes());
    }

    public function testCleanAdherentViaMailchimpWebhook(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');

        self::assertSame(ContactStatusEnum::SUBSCRIBED, $adherent->getMailchimpStatus());

        $data = [
            'type' => 'cleaned',
            'data' => [
                'list_id' => '123',
                'email' => 'adherent-male-a@en-marche-dev.fr',
            ],
        ];

        $this->client->request(Request::METHOD_POST, self::URL, $data);

        self::assertResponseIsSuccessful();

        self::assertSame(ContactStatusEnum::CLEANED, $adherent->getMailchimpStatus());
    }
}
