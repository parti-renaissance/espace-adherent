<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Audience;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\Handler\PrepareCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional proof of the Mailchimp fallback branch (PUBLICATION_SEND_VIA_MAILCHIMP=1): the real
 * PrepareCampaignAudienceHandler stages the audience in the real DB as Pending (awaiting the remote
 * push), wipes the remote segment and fans out one push per chunk — never through SES.
 *
 * The handler is built with the fallback flag ON (the container default is off) and a spy bus, so
 * the downstream Mailchimp handlers (which would hit the real Mailchimp HTTP API) are not executed;
 * their dispatch is asserted instead. The Mailchimp segment service is doubled — zero real HTTP.
 * Finalize's channel choice (SendMailchimpCampaignCommand + 60s delay) is covered by the unit test.
 */
#[Group('functional')]
class SendPublicationViaMailchimpTest extends AbstractKernelTestCase
{
    private const string AUDIENCE_FIRST_NAME = 'McFallbackAudience';
    private const int REMOTE_SEGMENT_ID = 555;

    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testFallbackStagesAudiencePendingWipesSegmentAndFansOutViaMailchimp(): void
    {
        $campaign = $this->createCampaignWithRemoteSegment(3);

        // The remote segment is wiped once before the refill; zero real Mailchimp HTTP.
        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::once())->method('update')->with(self::REMOTE_SEGMENT_ID, [], self::anything())->willReturn(true);

        $dispatched = [];
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (object $message) use (&$dispatched): Envelope {
            $dispatched[] = $message;

            return new Envelope($message);
        });

        $handler = $this->buildPrepareHandler($bus, $segmentService, sendViaMailchimp: true);
        $handler(new PrepareCampaignAudienceMessage($campaign->getId(), $campaign->getMessage()->getSender()->getId()));

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        // Real DB: every recipient staged Pending (awaiting the remote push), never Added (the SES state).
        self::assertSame(3, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Pending));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));

        // Fan-out one push per chunk + the finalize, and never a SES trigger.
        self::assertNotEmpty(array_filter($dispatched, static fn (object $m): bool => $m instanceof ProcessAudienceChunkMessage));
        self::assertNotEmpty(array_filter($dispatched, static fn (object $m): bool => $m instanceof FinalizeCampaignAudienceMessage));
        self::assertEmpty(array_filter($dispatched, static fn (object $m): bool => $m instanceof TriggerSesCampaignMessage));
    }

    private function buildPrepareHandler(
        MessageBusInterface $bus,
        MailchimpStaticSegmentServiceInterface $segmentService,
        bool $sendViaMailchimp,
    ): PrepareCampaignAudienceHandler {
        $container = self::getContainer();

        return new PrepareCampaignAudienceHandler(
            $this->manager,
            $container->get(AdherentRepository::class),
            $container->get(MailchimpStaticSegmentMemberRepository::class),
            $container->get(BulkInsertHelper::class),
            $bus,
            $container->get(NormalizerInterface::class),
            $segmentService,
            $container->get(MailchimpObjectIdMapping::class),
            $sendViaMailchimp,
        );
    }

    private function createCampaignWithRemoteSegment(int $audienceSize): MailchimpCampaign
    {
        $author = $this->makeSubscribedAdherent('Author', 'author');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $filter->setFirstName(self::AUDIENCE_FIRST_NAME);
        $message->setFilter($filter);

        $campaign = new MailchimpCampaign($message);
        // Fallback mode: the remote Mailchimp segment id is provisioned at prepare-time (Phase 1).
        // Here it is pre-set to isolate the Prepare push branch from the remote provisioning.
        $campaign->setStaticSegmentId(self::REMOTE_SEGMENT_ID);
        $segment = new MailchimpStaticSegment($campaign);
        $segment->mailchimpSegmentId = self::REMOTE_SEGMENT_ID;
        $campaign->setMailchimpStaticSegment($segment);

        $this->manager->persist($author);
        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);

        for ($i = 0; $i < $audienceSize; ++$i) {
            $this->manager->persist($this->makeSubscribedAdherent(self::AUDIENCE_FIRST_NAME, 'recipient'));
        }

        $this->manager->flush();

        return $campaign;
    }

    private function makeSubscribedAdherent(string $firstName, string $emailPrefix): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('mc-fallback-%s-%d@test.dev', $emailPrefix, $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        // status ENABLED + subscribed default: the audience SQL only targets enabled, consenting adherents.
        return Adherent::create(
            Adherent::createUuid($email),
            \sprintf('MC-%d', $seq),
            $email,
            'super-password',
            'female',
            $firstName,
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );
    }

    private function countByStatus(int $segmentId, SegmentMemberStatusEnum $status): int
    {
        return (int) $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->andWhere('m.processingStatus = :st')
            ->setParameter('sid', $segmentId)
            ->setParameter('st', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
