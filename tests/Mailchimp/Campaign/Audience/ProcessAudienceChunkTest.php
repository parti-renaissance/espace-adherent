<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Audience;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\Handler\ProcessAudienceChunkHandler;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Scope\ScopeEnum;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional proof of how a chunk push maps Mailchimp's answer onto per-row statuses, against a real DB
 * and through the real Driver — only the HTTP transport is doubled.
 *
 * The 2026-07-16 incident: Mailchimp answered 200 with total_added=0 on a chunk of 500 healthy,
 * subscribed emails (twice on 270 chunks). The handler threw, Messenger replayed the same 0 three
 * times, and the failure subscriber buried the 500 rows as errored — which blocked the whole campaign
 * at finalize. `total_added` is an aggregate of the same unreliable family as a segment's member_count
 * (which reported 0 while holding 132k members that same night): on a 200, `errors[]` is the only
 * per-email authority.
 */
#[Group('functional')]
class ProcessAudienceChunkTest extends AbstractKernelTestCase
{
    private const int REMOTE_SEGMENT_ID = 777;

    /**
     * The incident, reproduced: a 200 answer claiming nothing was added, with no per-email error, must
     * leave every row Added and raise nothing. Errored rows are what blocked the campaign.
     */
    public function testTotalAddedZeroWithoutPerEmailErrorsStillAddsEveryRow(): void
    {
        $campaign = $this->createCampaignWithPendingChunk(3);

        $this->runChunk($campaign, ['total_added' => 0, 'total_removed' => 0, 'errors' => []]);

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(3, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Errored));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Refused));
    }

    /**
     * total_added must never override the per-email truth: rows Mailchimp actually named as refused are
     * Refused, the rest are Added — even though the aggregate claims zero.
     */
    public function testPerEmailErrorsAreHonouredEvenWhenTotalAddedIsZero(): void
    {
        $campaign = $this->createCampaignWithPendingChunk(3);
        $refusedEmail = $this->firstPendingEmail($campaign);

        $this->runChunk($campaign, [
            'total_added' => 0,
            'errors' => [
                ['email_address' => $refusedEmail, 'error' => 'This email is not subscribed to the list'],
            ],
        ]);

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Refused));
        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Errored));
    }

    /**
     * The HTTP 400 "all refused" path stays untouched: the whole batch is rejected list-side, which is a
     * legitimate refusal, not an infrastructure failure — so it is recorded, never thrown.
     */
    public function testAllRefusedBatchIsRecordedAsRefused(): void
    {
        $campaign = $this->createCampaignWithPendingChunk(2);

        $this->runChunk(
            $campaign,
            ['errors' => [['field' => 'members_to_add', 'message' => 'None of the emails were subscribed']]],
            statusCode: 400,
        );

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Refused));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Errored));
    }

    /**
     * A genuine transport-level failure must still raise, so Messenger retries: unlike a lying aggregate,
     * a 500 carries no information about what happened to the rows.
     */
    public function testServerErrorStillThrowsSoMessengerRetries(): void
    {
        $campaign = $this->createCampaignWithPendingChunk(2);

        $this->expectException(\RuntimeException::class);

        $this->runChunk($campaign, ['detail' => 'Internal error'], statusCode: 500);
    }

    private function runChunk(MailchimpCampaign $campaign, array $body, int $statusCode = 200): void
    {
        $client = new MockHttpClient(new MockResponse(json_encode($body), ['http_code' => $statusCode]));

        // Real Driver over a doubled transport: the request building, status handling and body decoding
        // all run for real.
        $driver = new Driver($client, 'test-list-id');
        $driver->setLogger(new NullLogger());

        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static fn (object $m): Envelope => new Envelope($m));

        $handler = new ProcessAudienceChunkHandler(
            $this->manager,
            self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class),
            $driver,
            self::getContainer()->get(MailchimpObjectIdMapping::class),
            $bus,
        );

        $handler(new ProcessAudienceChunkMessage($campaign->getId(), 1));
    }

    private function firstPendingEmail(MailchimpCampaign $campaign): string
    {
        $emails = self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class)
            ->findPendingEmailsByChunk($campaign->getMailchimpStaticSegment()->id, 1);

        return (string) reset($emails);
    }

    private function createCampaignWithPendingChunk(int $audienceSize): MailchimpCampaign
    {
        $author = $this->makeSubscribedAdherent();

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $filter->setFirstName('ChunkAudience-'.bin2hex(random_bytes(6)));
        $message->setFilter($filter);

        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
        $campaign->setStaticSegmentId(self::REMOTE_SEGMENT_ID);

        $segment = new MailchimpStaticSegment($campaign);
        $segment->mailchimpSegmentId = self::REMOTE_SEGMENT_ID;
        $campaign->setMailchimpStaticSegment($segment);

        // The handler only acts on a Preparing campaign and refreshes it from the DB.
        $campaign->markAsPreparing($author);

        $this->manager->persist($author);
        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);

        for ($i = 0; $i < $audienceSize; ++$i) {
            $adherent = $this->makeSubscribedAdherent();
            $this->manager->persist($adherent);

            $member = new MailchimpStaticSegmentMember($segment, $adherent, 1);
            $member->processingStatus = SegmentMemberStatusEnum::Pending;
            $this->manager->persist($member);
        }

        $this->manager->flush();

        return $campaign;
    }

    private function makeSubscribedAdherent(): Adherent
    {
        // Random token keeps test data unique across methods and runs (shared DB, no rollback isolation).
        $token = bin2hex(random_bytes(8));
        $email = \sprintf('audience-chunk-%s@test.dev', $token);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        return Adherent::create(
            Adherent::createUuid($email),
            substr($token, 0, 7), // public_id is varchar(7) UNIQUE — 7 hex chars from the random token
            $email,
            'super-password',
            'female',
            'ChunkMember',
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
