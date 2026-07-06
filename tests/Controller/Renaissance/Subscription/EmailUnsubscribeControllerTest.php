<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Subscription;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\History\Command\UserActionHistoryCommand;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
#[Group('controller')]
class EmailUnsubscribeControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

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

    public function testPostAttributesFunnelAndDurableAuditToTheSend(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr');
        self::assertSame(ContactStatusEnum::SUBSCRIBED, $adherent->getMailchimpStatus());
        [$memberId, $messageUuid] = $this->createSentCampaignMemberRow($adherent);

        $this->client->request('POST', '/desabonnement/'.$this->tokenFor($adherent, $memberId, $messageUuid));

        self::assertResponseIsSuccessful();

        // The durable audit of "from which send" is dispatched.
        $this->assertMessageIsDispatched(UserActionHistoryCommand::class);

        $this->manager->clear();
        self::assertSame(
            ContactStatusEnum::UNSUBSCRIBED,
            $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr')->getMailchimpStatus()
        );
        // The exact sent row is attributed the unsubscribe (funnel).
        $member = $this->manager->getRepository(MailchimpStaticSegmentMember::class)->find($memberId);
        self::assertNotNull($member->unsubscribedAt);
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

    private function tokenFor(Adherent $adherent, ?int $memberId = null, ?string $messageUuid = null): string
    {
        $payload = ['uuid' => $adherent->getUuid()->toRfc4122()];
        if (null !== $memberId) {
            $payload['member_id'] = $memberId;
        }
        if (null !== $messageUuid) {
            $payload['message_uuid'] = $messageUuid;
        }

        return JWT::encode(
            $payload,
            static::getContainer()->getParameter('kernel.secret'),
            'HS256'
        );
    }

    /**
     * @return array{int, string}
     */
    private function createSentCampaignMemberRow(Adherent $recipient): array
    {
        $message = new AdherentMessage(null, $recipient);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $message->markAsSent();

        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
        $segment = new MailchimpStaticSegment($campaign);
        $campaign->setMailchimpStaticSegment($segment);

        $member = new MailchimpStaticSegmentMember($segment, $recipient, 1);
        $member->processingStatus = SegmentMemberStatusEnum::Sent;

        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);
        $this->manager->persist($member);
        $this->manager->flush();

        return [(int) $member->id, $message->getUuid()->toRfc4122()];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }
}
