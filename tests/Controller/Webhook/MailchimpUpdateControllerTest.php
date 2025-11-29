<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\Mailchimp\Contact\ContactStatusEnum;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class MailchimpUpdateControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    private const URL = '/mailchimp/abc';

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
        self::assertCount(8, $adherent->getSubscriptionTypeCodes());
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
