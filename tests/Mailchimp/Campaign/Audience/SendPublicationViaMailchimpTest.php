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
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\Handler\PrepareCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use App\Mailchimp\Campaign\MailchimpChannelInitializer;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Mailchimp\Campaign\StaticSegmentInitializer;
use App\Mailchimp\Manager;
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
 * Functional proof of the publication send routing (SES vs Mailchimp). The channel is decided from the
 * recipient count vs PUBLICATION_SEND_VIA_MAILCHIMP_THRESHOLD and pinned on MailchimpCampaign::$sendViaMailchimp:
 *
 * - testFallbackStages...: campaign pinned to Mailchimp → the real PrepareCampaignAudienceHandler stages the
 *   audience in the real DB as Pending, wipes the remote segment and fans out one push per chunk — never SES.
 *   Downstream Mailchimp handlers (real HTTP) are not run: their dispatch is asserted on a spy bus and the
 *   segment service is doubled — zero real HTTP.
 * - testPrepareRoutesTo{Ses,Mailchimp}...: AudienceMessagePreparer::prepare() runs the real audience COUNT
 *   and pins the channel per the threshold (strict >), with the channel initializers doubled to stay HTTP-free.
 */
#[Group('functional')]
class SendPublicationViaMailchimpTest extends AbstractKernelTestCase
{
    private const string AUDIENCE_FIRST_NAME = 'McFallbackAudience';
    private const int REMOTE_SEGMENT_ID = 555;

    public function testFallbackStagesAudiencePendingWipesSegmentAndFansOutViaMailchimp(): void
    {
        $campaign = $this->createCampaignWithRemoteSegment(3);
        // Channel pinned to Mailchimp on the campaign (Phase 3 sets this from the recipient count at prepare-time).
        $campaign->sendViaMailchimp = true;
        $this->manager->flush();

        // The remote segment is wiped once before the refill; zero real Mailchimp HTTP.
        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::once())->method('update')->with(self::REMOTE_SEGMENT_ID, [], self::anything())->willReturn(true);

        $dispatched = [];
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (object $message) use (&$dispatched): Envelope {
            $dispatched[] = $message;

            return new Envelope($message);
        });

        $handler = $this->buildPrepareHandler($bus, $segmentService);
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

    public function testPrepareRoutesToSesWhenAudienceUnderThreshold(): void
    {
        // Real audience of 2 (firstName filter), threshold 10 → count 2 ≤ 10 → SES.
        $campaign = $this->createCampaignWithRemoteSegment(2);

        $local = $this->createMock(StaticSegmentInitializer::class);
        $local->expects(self::once())->method('ensureLocalSegment')->with(self::identicalTo($campaign));
        $remote = $this->createMock(MailchimpChannelInitializer::class);
        $remote->expects(self::never())->method('ensureRemoteChannel');

        $preparer = $this->buildPreparer($local, $remote, threshold: 10);
        $preparer->prepare($campaign->getMessage(), $campaign->getMessage()->getSender());

        self::assertFalse($campaign->sendViaMailchimp, 'audience under threshold must route via SES');
    }

    public function testPrepareRoutesToMailchimpWhenAudienceOverThreshold(): void
    {
        // Real audience of 2 (firstName filter), threshold 1 → count 2 > 1 → Mailchimp.
        $campaign = $this->createCampaignWithRemoteSegment(2);

        $local = $this->createMock(StaticSegmentInitializer::class);
        $local->expects(self::never())->method('ensureLocalSegment');
        $remote = $this->createMock(MailchimpChannelInitializer::class);
        $remote->expects(self::once())->method('ensureRemoteChannel')->with(self::identicalTo($campaign));

        $preparer = $this->buildPreparer($local, $remote, threshold: 1);
        $preparer->prepare($campaign->getMessage(), $campaign->getMessage()->getSender());

        self::assertTrue($campaign->sendViaMailchimp, 'audience over threshold must route via Mailchimp');
    }

    /**
     * A campaign first prepared through SES owns a local-only segment (no mailchimpSegmentId). If its
     * audience later crosses the threshold, the Mailchimp route must still provision the remote
     * segment on that existing local one — ensureRemoteChannel() reuses the local segment and
     * provisions the remote id when it is missing.
     *
     * This path only became reachable once re-preparation was fixed: before, a campaign already
     * carrying a chunksTotal could never be prepared again, so it could never switch channel.
     */
    public function testExistingLocalSegmentIsProvisionedWhenSwitchingToMailchimp(): void
    {
        $campaign = $this->createCampaignWithRemoteSegment(2);
        // Roll the segment back to a local-only, SES-shaped one carrying a previous run's grain.
        $campaign->setStaticSegmentId(null);
        $segment = $campaign->getMailchimpStaticSegment();
        $segment->mailchimpSegmentId = null;
        $segment->chunksTotal = 1;
        $segment->attempts = 1;
        $this->manager->flush();

        $segmentService = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $segmentService->expects(self::once())
            ->method('create')
            ->with(self::anything(), [], self::anything())
            ->willReturn(self::REMOTE_SEGMENT_ID)
        ;

        // Threshold 1 vs a real audience of 2 → Mailchimp. The real MailchimpChannelInitializer runs,
        // with only the segment service and the campaign manager doubled (zero real HTTP).
        $preparer = $this->buildPreparer(
            self::getContainer()->get(StaticSegmentInitializer::class),
            $this->buildChannelInitializer($segmentService),
            threshold: 1,
        );
        $preparer->prepare($campaign->getMessage(), $campaign->getMessage()->getSender());

        self::assertTrue($campaign->sendViaMailchimp);
        self::assertSame(self::REMOTE_SEGMENT_ID, $campaign->getStaticSegmentId(), 'the remote segment must be provisioned on the existing local one');
        self::assertSame(self::REMOTE_SEGMENT_ID, $segment->mailchimpSegmentId);
        // The send click cleared the previous run's grain, so the handler will rebuild at the MC grain.
        self::assertNull($segment->chunksTotal);
        self::assertSame(2, $segment->attempts);
    }

    private function buildChannelInitializer(MailchimpStaticSegmentServiceInterface $segmentService): MailchimpChannelInitializer
    {
        $container = self::getContainer();

        return new MailchimpChannelInitializer(
            $container->get(StaticSegmentInitializer::class),
            $segmentService,
            $container->get(MailchimpObjectIdMapping::class),
            $this->createStub(Manager::class),
            $this->manager,
        );
    }

    private function buildPreparer(
        StaticSegmentInitializer $staticSegmentInitializer,
        MailchimpChannelInitializer $mailchimpChannelInitializer,
        int $threshold,
    ): AudienceMessagePreparer {
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static fn (object $m): Envelope => new Envelope($m));

        return new AudienceMessagePreparer(
            $this->manager,
            $bus,
            new SendStatusFactory(),
            $staticSegmentInitializer,
            $mailchimpChannelInitializer,
            self::getContainer()->get(AdherentRepository::class),
            $threshold,
        );
    }

    private function buildPrepareHandler(
        MessageBusInterface $bus,
        MailchimpStaticSegmentServiceInterface $segmentService,
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
        );
    }

    private function createCampaignWithRemoteSegment(int $audienceSize): MailchimpCampaign
    {
        // Unique first name per campaign so the audience COUNT is isolated from other tests' recipients.
        $audienceFirstName = self::AUDIENCE_FIRST_NAME.'-'.bin2hex(random_bytes(6));
        $author = $this->makeSubscribedAdherent('Author');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $filter->setFirstName($audienceFirstName);
        $message->setFilter($filter);

        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
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
            $this->manager->persist($this->makeSubscribedAdherent($audienceFirstName));
        }

        $this->manager->flush();

        return $campaign;
    }

    private function makeSubscribedAdherent(string $firstName): Adherent
    {
        // Random token keeps test data unique across methods and runs (shared DB, no rollback isolation).
        $token = bin2hex(random_bytes(8));
        $email = \sprintf('mc-fallback-%s@test.dev', $token);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        // status ENABLED + subscribed default: the audience SQL only targets enabled, consenting adherents.
        return Adherent::create(
            Adherent::createUuid($email),
            substr($token, 0, 7), // public_id is varchar(7) UNIQUE — 7 hex chars from the random token
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
