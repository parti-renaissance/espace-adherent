<?php

namespace Tests\App\Controller\EnMarche;

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
        $adherent = $this->getAdherentRepository()->findOneByEmail('deputy-75-2@en-marche-dev.fr');

        self::assertTrue($adherent->isEmailUnsubscribed());
        self::assertCount(0, $adherent->getSubscriptionTypeCodes());

        $data = [
            'type' => 'subscribe',
            'data' => [
                'list_id' => '123',
                'email' => 'deputy-75-2@en-marche-dev.fr',
            ],
        ];

        $this->client->request(Request::METHOD_POST, self::URL, $data);

        self::assertResponseIsSuccessful();

        self::assertFalse($adherent->isEmailUnsubscribed());
        self::assertCount(7, $adherent->getSubscriptionTypeCodes());
    }
}
