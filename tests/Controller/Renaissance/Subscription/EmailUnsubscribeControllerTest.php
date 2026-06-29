<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Subscription;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Subscription\SubscriptionTypeEnum;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class EmailUnsubscribeControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testGetRendersConfirmationWithoutMutating(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr');
        self::assertSame(ContactStatusEnum::SUBSCRIBED, $adherent->getMailchimpStatus());

        $this->client->request('GET', '/desabonnement/'.$this->tokenFor($adherent));

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Confirmer le désabonnement', $this->client->getResponse()->getContent());

        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr');
        self::assertSame(ContactStatusEnum::SUBSCRIBED, $reloaded->getMailchimpStatus());
    }

    public function testPostUnsubscribesEmailKeepingSms(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');
        self::assertSame(ContactStatusEnum::SUBSCRIBED, $adherent->getMailchimpStatus());
        self::assertContains(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $adherent->getSubscriptionTypeCodes());

        $uuid = $adherent->getUuid();
        $historyRepository = $this->manager->getRepository(EmailSubscriptionHistory::class);
        $historyBefore = $historyRepository->count(['adherentUuid' => $uuid]);

        $this->client->request('POST', '/desabonnement/'.$this->tokenFor($adherent));

        self::assertResponseIsSuccessful();

        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail('gisele-berthoux@caramail.com');
        self::assertSame(ContactStatusEnum::UNSUBSCRIBED, $reloaded->getMailchimpStatus());
        // All email types removed, SMS consent preserved.
        self::assertSame([SubscriptionTypeEnum::MILITANT_ACTION_SMS], $reloaded->getSubscriptionTypeCodes());
        // GDPR audit trail written for the removed email types.
        self::assertGreaterThan($historyBefore, $historyRepository->count(['adherentUuid' => $uuid]));
    }

    public function testPostWithInvalidTokenReturns400(): void
    {
        $this->client->request('POST', '/desabonnement/invalid-token');

        self::assertResponseStatusCodeSame(400);
    }

    public function testGetWithInvalidTokenReturnsErrorPage(): void
    {
        $this->client->request('GET', '/desabonnement/invalid-token');

        self::assertResponseStatusCodeSame(404);
        self::assertStringContainsString('invalide', $this->client->getResponse()->getContent());
    }

    public function testRouteAnswersOnCampaignHost(): void
    {
        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_campaign_host'));

        $adherent = $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr');
        $this->client->request('GET', '/desabonnement/'.$this->tokenFor($adherent));

        self::assertResponseIsSuccessful();
    }

    private function tokenFor(Adherent $adherent): string
    {
        return JWT::encode(
            ['uuid' => $adherent->getUuid()->toRfc4122()],
            static::getContainer()->getParameter('kernel.secret'),
            'HS256'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }
}
