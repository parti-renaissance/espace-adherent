<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\Message\PrepareCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Mailchimp\Campaign\MailchimpStaticSegmentServiceInterface;
use App\Membership\ActivityPositionsEnum;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use App\Scope\ScopeEnum;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use App\Ses\Client\SesEmail;
use App\Ses\Client\SesEmailClient;
use App\Ses\Client\SesSendOutcome;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;
use Tests\App\MessengerTestTrait;

/**
 * End-to-end of the SES flip: a non-statutory publication is staged Added (no Mailchimp segment push)
 * and sent through SES — never through the Mailchimp campaign path.
 *
 * The audience messages (Prepare, Finalize) and the SES fan-out (TriggerSes -> SendChunk) all route to
 * the SES transport, which test config maps to sync. So a single PrepareCampaignAudienceMessage dispatch
 * cascades the whole chain inline, exactly as in production minus the broker hops. The SES transport is
 * doubled in the container so no real AWS call happens.
 */
#[Group('functional')]
class SendPublicationViaSesTest extends AbstractKernelTestCase
{
    use MessengerTestTrait;

    private const string AUDIENCE_FIRST_NAME = 'SesFlipAudience';

    private int $seq = 0;

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    public function testPreparedAudienceIsStagedAddedWithoutMailchimpPush(): void
    {
        $campaign = $this->createCampaignWithAudience(3);
        // No pending-send: preparation stops at Ready so the staged Added rows can be observed
        // before any send claims them.

        // The Mailchimp segment service must never be touched by the SES path.
        $mailchimpSegment = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $mailchimpSegment->expects(self::never())->method('update');
        self::getContainer()->set(MailchimpStaticSegmentServiceInterface::class, $mailchimpSegment);

        $sesClient = $this->createMock(SesEmailClient::class);
        $sesClient->expects(self::never())->method('sendEmail');
        self::getContainer()->set(SesEmailClient::class, $sesClient);

        $this->runPipeline($campaign);

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        // Every recipient is staged ready to send (Added), never the legacy Pending push state.
        self::assertSame(3, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Pending));
        self::assertSame([1], $this->distinctChunkNumbers($segmentId), 'A 3-recipient audience fits in one fan-out chunk (grain 50).');

        $reloaded = $this->reloadCampaign($campaign);
        self::assertSame(PreparationStatusEnum::Ready, $reloaded->getPreparationStatus());

        // No Mailchimp send command, no legacy per-chunk push message.
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
        $this->assertMessageIsNotDispatched(ProcessAudienceChunkMessage::class);
        $this->assertMessageIsNotDispatched(TriggerSesCampaignMessage::class);
    }

    public function testPublicationIsSentEndToEndViaSesWithZeroMailchimp(): void
    {
        $campaign = $this->createCampaignWithAudience(3);
        $campaign->markAsPendingSend(); // arms the auto-send at finalize
        $this->manager->flush();

        $mailchimpSegment = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $mailchimpSegment->expects(self::never())->method('update');
        self::getContainer()->set(MailchimpStaticSegmentServiceInterface::class, $mailchimpSegment);

        $sentTo = [];
        $sesClient = $this->createMock(SesEmailClient::class);
        $sesClient
            ->expects(self::exactly(3))
            ->method('sendEmail')
            ->willReturnCallback(static function (SesEmail $email) use (&$sentTo): SesSendOutcome {
                $sentTo[] = $email->to;

                return SesSendOutcome::sent('ses-msg-'.$email->to);
            })
        ;
        self::getContainer()->set(SesEmailClient::class, $sesClient);

        $this->runPipeline($campaign);

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        // SES sent every recipient, and the campaign completed with reach recorded from the sent rows.
        self::assertCount(3, $sentTo);
        self::assertSame(3, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadCampaign($campaign)->status);
        self::assertSame(3, $this->countReach($messageId));

        // The send went through SES, never the Mailchimp campaign/segment path.
        $this->assertMessageIsDispatched(TriggerSesCampaignMessage::class);
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
        $this->assertMessageIsNotDispatched(ProcessAudienceChunkMessage::class);
    }

    /**
     * Closes the loop the other e2e leaves open (it builds the segment by hand): here the local static
     * segment is created by AudienceMessagePreparer::prepare() — locally, with zero Mailchimp — and the
     * publication then goes out through SES end to end.
     */
    public function testPrepareInitialisesSegmentLocallyThenSendsViaSesWithZeroMailchimp(): void
    {
        $campaign = $this->createCampaignWithAudienceWithoutSegment(3);
        $author = $campaign->getMessage()->getSender();

        // Local-only segment init: the Mailchimp segment service must never be touched.
        $mailchimpSegment = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $mailchimpSegment->expects(self::never())->method('create');
        $mailchimpSegment->expects(self::never())->method('update');
        self::getContainer()->set(MailchimpStaticSegmentServiceInterface::class, $mailchimpSegment);

        $sentTo = [];
        $sesClient = $this->createMock(SesEmailClient::class);
        $sesClient
            ->expects(self::exactly(3))
            ->method('sendEmail')
            ->willReturnCallback(static function (SesEmail $email) use (&$sentTo): SesSendOutcome {
                $sentTo[] = $email->to;

                return SesSendOutcome::sent('ses-msg-'.$email->to);
            })
        ;
        self::getContainer()->set(SesEmailClient::class, $sesClient);

        // prepare() creates the local static segment (no Mailchimp call), arms the auto-send and
        // dispatches the audience pipeline — which cascades inline to SES under the test sync transport.
        self::getContainer()->get(AudienceMessagePreparer::class)->prepare($campaign->getMessage(), $author);

        $prepared = $this->reloadCampaign($campaign);
        $segment = $prepared->getMailchimpStaticSegment();
        self::assertNotNull($segment, 'prepare() must initialise the local static segment.');
        self::assertNull($segment->mailchimpSegmentId, 'No Mailchimp segment id: the segment is local-only.');
        self::assertNull($prepared->getStaticSegmentId(), 'The vestigial Mailchimp static segment id stays null.');
        $segmentId = $segment->id;

        $messageId = $prepared->getMessage()->getId();

        self::assertCount(3, $sentTo);
        self::assertSame(3, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadCampaign($prepared)->status);
        self::assertSame(3, $this->countReach($messageId));

        $this->assertMessageIsDispatched(TriggerSesCampaignMessage::class);
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
        $this->assertMessageIsNotDispatched(ProcessAudienceChunkMessage::class);
    }

    /**
     * Regression of the 2026-07-16 incident (campaign 3010): a campaign left with the run state of an
     * earlier, failed Mailchimp preparation could never be re-prepared. The stale chunksTotal made
     * PrepareCampaignAudienceHandler take its resume short-circuit — which skips the rebuild — so the
     * finalize aggregated an empty table, promoted the campaign to Ready anyway, and the SES trigger
     * ended on "No sendable recipient".
     *
     * prepare() now clears the run state at the send boundary, so the stale grain cannot survive a
     * send click.
     */
    public function testStaleMailchimpRunStateDoesNotBlockSesRebuild(): void
    {
        $campaign = $this->createCampaignWithAudienceWithoutSegment(3);
        $author = $campaign->getMessage()->getSender();

        // The segment left behind by the failed Mailchimp preparation: grain 500 → 7 chunks, 2 done,
        // push errors recorded, and an expectedCount from an audience that no longer applies.
        $segment = new MailchimpStaticSegment($campaign);
        $segment->attempts = 1;
        $segment->chunksTotal = 7;
        $segment->chunksDone = 2;
        $segment->expectedCount = 3284;
        $segment->errorSummary = 'HTTP 400 on chunk of 500 emails: None of the emails provided were subscribed to the list';
        $campaign->setMailchimpStaticSegment($segment);
        $this->manager->persist($segment);
        $this->manager->flush();

        $mailchimpSegment = $this->createMock(MailchimpStaticSegmentServiceInterface::class);
        $mailchimpSegment->expects(self::never())->method('update');
        self::getContainer()->set(MailchimpStaticSegmentServiceInterface::class, $mailchimpSegment);

        $sentTo = [];
        $sesClient = $this->createMock(SesEmailClient::class);
        $sesClient
            ->expects(self::exactly(3))
            ->method('sendEmail')
            ->willReturnCallback(static function (SesEmail $email) use (&$sentTo): SesSendOutcome {
                $sentTo[] = $email->to;

                return SesSendOutcome::sent('ses-msg-'.$email->to);
            })
        ;
        self::getContainer()->set(SesEmailClient::class, $sesClient);

        self::getContainer()->get(AudienceMessagePreparer::class)->prepare($campaign->getMessage(), $author);

        $reloaded = $this->reloadCampaign($campaign);
        $rebuilt = $reloaded->getMailchimpStaticSegment();
        $segmentId = $rebuilt->id;

        // The audience was rebuilt rather than short-circuited: real rows, staged and sent.
        self::assertCount(3, $sentTo);
        self::assertSame(3, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(3, $rebuilt->expectedCount);
        self::assertSame(3, $rebuilt->preparedCount);

        // The stale Mailchimp grain is gone: 3 recipients at the SES grain of 50 make a single chunk.
        self::assertSame(1, $rebuilt->chunksTotal, 'the stale Mailchimp chunk grain (7) must not survive a send click');
        self::assertSame([1], $this->distinctChunkNumbers($segmentId));
        self::assertNull($rebuilt->errorSummary, 'the previous run error summary must be cleared');
        self::assertSame(2, $rebuilt->attempts);

        // The campaign went out through SES instead of failing on "No sendable recipient".
        self::assertSame(MailchimpStatusEnum::Sent, $reloaded->status);
        self::assertNotSame(MailchimpStatusEnum::Error, $reloaded->status);
        $this->assertMessageIsDispatched(TriggerSesCampaignMessage::class);
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
        $this->assertMessageIsNotDispatched(ProcessAudienceChunkMessage::class);
    }

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return self::getContainer()->get(MessageRecorderInterface::class);
    }

    private function runPipeline(MailchimpCampaign $campaign): void
    {
        $campaignId = $campaign->getId();
        $authorId = $campaign->getMessage()->getSender()->getId();

        // Prepare stages the audience (Added) then dispatches Finalize, which arms the auto-send and
        // dispatches TriggerSesCampaignMessage. All these messages route to the SES transport, mapped to
        // sync in test, so this single dispatch cascades the whole chain through SendSesCampaignChunkMessage
        // to the (doubled) SES client.
        self::getContainer()->get(MessageBusInterface::class)->dispatch(new PrepareCampaignAudienceMessage($campaignId, $authorId));
    }

    private function createCampaignWithAudience(int $audienceSize): MailchimpCampaign
    {
        $author = $this->makeSubscribedAdherent('Author', 'author');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        // A unique first-name filter isolates exactly the recipients created below from any fixture
        // adherent, so the real audience SQL returns a deterministic set.
        $filter = new AdherentMessageFilter();
        $filter->setFirstName(self::AUDIENCE_FIRST_NAME);
        $message->setFilter($filter);

        $campaign = new MailchimpCampaign($message);
        // The send path requires only the local segment ENTITY (no Mailchimp segment id since Phase 9).
        $segment = new MailchimpStaticSegment($campaign);
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

    /**
     * Like createCampaignWithAudience(), but WITHOUT a static segment: AudienceMessagePreparer::prepare()
     * is responsible for initialising it locally at send time.
     */
    private function createCampaignWithAudienceWithoutSegment(int $audienceSize): MailchimpCampaign
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
        $message->addMailchimpCampaign($campaign);

        $this->manager->persist($author);
        $this->manager->persist($message);
        $this->manager->persist($campaign);

        for ($i = 0; $i < $audienceSize; ++$i) {
            $this->manager->persist($this->makeSubscribedAdherent(self::AUDIENCE_FIRST_NAME, 'recipient'));
        }

        $this->manager->flush();

        return $campaign;
    }

    private function makeSubscribedAdherent(string $firstName, string $emailPrefix): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-flip-%s-%d@test.dev', $emailPrefix, $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        // status ENABLED: the audience SQL only targets enabled adherents.
        // mailchimp_status defaults to subscribed (the audience consent gate).
        return Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-%d', $seq),
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

    /**
     * @return list<int>
     */
    private function distinctChunkNumbers(int $segmentId): array
    {
        $rows = $this->getRepository(MailchimpStaticSegmentMember::class)
            ->createQueryBuilder('m')
            ->select('DISTINCT m.chunkNumber')
            ->where('IDENTITY(m.staticSegment) = :sid')
            ->setParameter('sid', $segmentId)
            ->orderBy('m.chunkNumber', 'ASC')
            ->getQuery()
            ->getSingleColumnResult()
        ;

        return array_map('intval', $rows);
    }

    private function countReach(int $messageId): int
    {
        return (int) $this->getRepository(AdherentMessageReach::class)
            ->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('IDENTITY(r.message) = :mid')
            ->setParameter('mid', $messageId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function reloadCampaign(MailchimpCampaign $campaign): MailchimpCampaign
    {
        $id = $campaign->getId();
        $this->manager->clear();

        return $this->getRepository(MailchimpCampaign::class)->find($id);
    }
}
