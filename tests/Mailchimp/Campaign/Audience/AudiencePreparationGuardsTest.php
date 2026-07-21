<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Audience;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\Handler\FinalizeCampaignAudienceHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Scope\ScopeEnum;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional proof of the finalize guards against a real DB: an audience that was not staged in
 * full, or that holds nothing sendable, must never be promoted to Ready nor reach a send.
 *
 * These guards close a blind spot the unit tests cannot see: expectedCount records how many rows
 * the audience SQL intended to stage, while BulkInsertHelper::insertIgnore downgrades any
 * FK/unique violation to a silent warning and its return value is never read. Only a real DB can
 * prove the aggregate is compared against what actually landed.
 */
#[Group('functional')]
class AudiencePreparationGuardsTest extends AbstractKernelTestCase
{
    public function testFinalizeBlocksWhenStagedCountDoesNotMatchExpected(): void
    {
        // The audience SQL intended 5 rows, only 3 landed: 2 were silently dropped at insert time.
        $campaign = $this->createCampaignInPreparation(expectedCount: 5);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Added, 3);

        $dispatched = $this->runFinalize($campaign);

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::Empty, $campaign->getBlockReason());
        self::assertFalse($campaign->isPendingSend());
        self::assertNull($campaign->getMailchimpStaticSegment()->builtAt);
        self::assertSame([], $dispatched, 'an incomplete audience must never reach a send');
    }

    public function testFinalizeBlocksWhenNothingIsPrepared(): void
    {
        // Every row landed (3 staged for 3 expected, so the completeness invariant holds) but all
        // were refused: nothing is sendable. Proves both conditions are needed — the sum alone
        // would let this through.
        $campaign = $this->createCampaignInPreparation(expectedCount: 3);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Refused, 3);

        $dispatched = $this->runFinalize($campaign);

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::Empty, $campaign->getBlockReason());
        self::assertFalse($campaign->isPendingSend());
        self::assertSame(0, $campaign->getMailchimpStaticSegment()->preparedCount);
        self::assertSame([], $dispatched, 'an audience with nothing sendable must never reach a send');
    }

    public function testFinalizeMarksReadyWhenAudienceIsCompleteAndSendable(): void
    {
        // Control case: the guards must not block a healthy audience. Added + Refused partition
        // expectedCount exactly, and something is sendable.
        $campaign = $this->createCampaignInPreparation(expectedCount: 4);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Added, 3);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Refused, 1);

        $dispatched = $this->runFinalize($campaign);

        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertNull($campaign->getBlockReason());
        self::assertNotNull($campaign->getMailchimpStaticSegment()->builtAt);
        self::assertCount(1, $dispatched);
        self::assertInstanceOf(TriggerSesCampaignMessage::class, $dispatched[0]);
    }

    /**
     * Regression proof for the 2026-07-16 incident: a hiccuping chunk must not hold back the campaign.
     * 500 errored rows out of 134 697 (0.37%, a single chunk) blocked 100% of the send, twice.
     *
     * Errored rows within both tolerances: the audience is short by a bounded, known amount, and the
     * send proceeds.
     */
    public function testFinalizeProceedsWhenErroredChunksAreWithinTolerance(): void
    {
        $campaign = $this->createCampaignInPreparation(expectedCount: 100);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Added, 98);
        // 2 errored rows spread over 2 distinct chunks: on the chunk bound (2), under the row bound (5).
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Errored, 1, chunkNumber: 2);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Errored, 1, chunkNumber: 3);

        $dispatched = $this->runFinalize($campaign);

        self::assertSame(PreparationStatusEnum::Ready, $campaign->getPreparationStatus());
        self::assertNull($campaign->getBlockReason());
        self::assertSame(98, $campaign->getMailchimpStaticSegment()->preparedCount);
        self::assertSame(2, $campaign->getMailchimpStaticSegment()->erroredCount);
        self::assertCount(1, $dispatched, 'errored rows within tolerance must not block the send');
        self::assertInstanceOf(TriggerSesCampaignMessage::class, $dispatched[0]);
    }

    /**
     * Chunk bound, isolated: 3 errored rows are 3% of the audience, well under the 5% row bound — only
     * the chunk count blocks here. More failed chunks than tolerated means the push is systematically
     * broken, not hiccuping.
     */
    public function testFinalizeBlocksWhenErroredChunksExceedTolerance(): void
    {
        $campaign = $this->createCampaignInPreparation(expectedCount: 100);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Added, 97);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Errored, 1, chunkNumber: 2);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Errored, 1, chunkNumber: 3);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Errored, 1, chunkNumber: 4);

        $dispatched = $this->runFinalize($campaign);

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::PreparationErrors, $campaign->getBlockReason());
        self::assertSame([], $dispatched, 'a systematically broken push must never reach a send');
    }

    /**
     * Row bound, isolated: a single errored chunk is within the chunk bound, but on a small audience it
     * amputates far too much (2 of 10 = 20% vs the 5% allowed). This is why the chunk count alone is not
     * enough — one chunk is 0.37% of a 134k send but a large slice of a small one.
     */
    public function testFinalizeBlocksWhenErroredRowsExceedShareOfSmallAudience(): void
    {
        $campaign = $this->createCampaignInPreparation(expectedCount: 10);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Added, 8);
        $this->stageMembers($campaign, SegmentMemberStatusEnum::Errored, 2, chunkNumber: 2);

        $dispatched = $this->runFinalize($campaign);

        self::assertSame(PreparationStatusEnum::Failed, $campaign->getPreparationStatus());
        self::assertSame(BlockReasonEnum::PreparationErrors, $campaign->getBlockReason());
        self::assertSame([], $dispatched, 'an audience amputated beyond the allowed share must never reach a send');
    }

    /**
     * @return list<object> the messages the finalize dispatched (a send command, or nothing)
     */
    private function runFinalize(MailchimpCampaign $campaign): array
    {
        $dispatched = [];
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (object $message) use (&$dispatched): Envelope {
            $dispatched[] = $message;

            return new Envelope($message);
        });

        $handler = new FinalizeCampaignAudienceHandler(
            $this->manager,
            self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class),
            $bus,
            maxErroredChunks: 2,
            maxErroredPercent: 5,
        );

        $handler(new FinalizeCampaignAudienceMessage($campaign->getId()));

        return array_values(array_filter($dispatched, static function (object $message): bool {
            return $message instanceof TriggerSesCampaignMessage || $message instanceof SendMailchimpCampaignCommand;
        }));
    }

    private function createCampaignInPreparation(int $expectedCount): MailchimpCampaign
    {
        $author = $this->makeSubscribedAdherent();

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $filter->setFirstName('GuardsAudience-'.bin2hex(random_bytes(6)));
        $message->setFilter($filter);

        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);

        $segment = new MailchimpStaticSegment($campaign);
        $segment->expectedCount = $expectedCount;
        $campaign->setMailchimpStaticSegment($segment);

        // The finalize only acts on a Preparing campaign, and refreshes it from the DB — the state
        // must be committed before the handler runs.
        $campaign->markAsPreparing($author);
        $campaign->markAsPendingSend();

        $this->manager->persist($author);
        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);
        $this->manager->flush();

        return $campaign;
    }

    private function stageMembers(
        MailchimpCampaign $campaign,
        SegmentMemberStatusEnum $status,
        int $count,
        int $chunkNumber = 1,
    ): void {
        $segment = $campaign->getMailchimpStaticSegment();

        for ($i = 0; $i < $count; ++$i) {
            $adherent = $this->makeSubscribedAdherent();
            $this->manager->persist($adherent);

            $member = new MailchimpStaticSegmentMember($segment, $adherent, $chunkNumber);
            $member->processingStatus = $status;
            $this->manager->persist($member);
        }

        $this->manager->flush();
    }

    private function makeSubscribedAdherent(): Adherent
    {
        // Random token keeps test data unique across methods and runs (shared DB, no rollback isolation).
        $token = bin2hex(random_bytes(8));
        $email = \sprintf('audience-guards-%s@test.dev', $token);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        return Adherent::create(
            Adherent::createUuid($email),
            substr($token, 0, 7), // public_id is varchar(7) UNIQUE — 7 hex chars from the random token
            $email,
            'super-password',
            'female',
            'GuardsMember',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );
    }
}
