<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\AdherentMessage\Variable\Parser as VariableParser;
use App\AdherentMessage\Variable\Renderer\SesVariableRenderer;
use App\Doctrine\Utils\BulkInsertHelper;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailer\Template\Manager;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\AdherentRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Handler\SendSesCampaignChunkHandler;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Reach\CampaignReachInserter;
use App\Ses\Client\SesEmailClient;
use App\Ses\Client\SesSendOutcome;
use App\Ses\Rendering\SesMessageAssembler;
use App\Ses\Rendering\SesRecipientContextFactory;
use App\Ses\Rendering\SesRecipientEmailFactory;
use App\Ses\Unsubscribe\UnsubscribeUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class SendSesCampaignChunkTransportErrorTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    public function testTransportErrorReopensRowKeepsSentRowsAndLeavesCampaignSending(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();
        $campaignId = $campaign->getId();

        // First recipient sends; the second hits a transport failure that propagates for a Messenger retry.
        $callCount = 0;
        $client = $this->createMock(SesEmailClient::class);
        $client
            ->expects(self::exactly(2))
            ->method('sendEmail')
            ->willReturnCallback(static function () use (&$callCount): SesSendOutcome {
                if (1 === ++$callCount) {
                    return SesSendOutcome::sent('ses-msg-1');
                }

                throw new \RuntimeException('SES transport failure');
            })
        ;

        try {
            $this->createHandler($client)(new SendSesCampaignChunkMessage($campaignId, 1));
            self::fail('A transport failure must propagate so Messenger retries the chunk.');
        } catch (\RuntimeException $exception) {
            self::assertSame('SES transport failure', $exception->getMessage());
        }

        // One row delivered (stays Sent, never re-sent on retry), the failed one reopened to Added.
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sending));

        // Completion never ran: campaign still Sending, no reach recorded.
        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));
        self::assertSame(0, $this->countReach($messageId));
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function createHandler(SesEmailClient $client): SendSesCampaignChunkHandler
    {
        $memberRepository = self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class);

        return new SendSesCampaignChunkHandler(
            self::getContainer()->get(MailchimpCampaignRepository::class),
            $memberRepository,
            new SesMessageAssembler(self::getContainer()->get(Manager::class)),
            new SesRecipientEmailFactory(new VariableParser(), new SesVariableRenderer(), new SesRecipientContextFactory(), self::getContainer()->get(UnsubscribeUrlGenerator::class)),
            $client,
            new CampaignReachInserter($memberRepository, self::getContainer()->get(BulkInsertHelper::class)),
            self::getContainer()->get(AdherentRepository::class),
            self::getContainer()->get(EntityManagerInterface::class),
            self::getContainer()->get(MessageBusInterface::class),
        );
    }

    private function createCampaign(): MailchimpCampaign
    {
        $author = $this->createSubscribedAdherent();

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');

        $campaign = new MailchimpCampaign($message);
        $segment = new MailchimpStaticSegment($campaign);
        $campaign->setMailchimpStaticSegment($segment);

        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->persist($segment);

        return $campaign;
    }

    private function addMember(
        MailchimpCampaign $campaign,
        Adherent $adherent,
        int $chunkNumber,
        SegmentMemberStatusEnum $status,
    ): void {
        $member = new MailchimpStaticSegmentMember($campaign->getMailchimpStaticSegment(), $adherent, $chunkNumber);
        $member->processingStatus = $status;

        $this->manager->persist($member);
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

    private function reloadStatus(MailchimpCampaign $campaign): MailchimpStatusEnum
    {
        $this->manager->clear();

        return $this->getRepository(MailchimpCampaign::class)->find($campaign->getId())->status;
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-chunk-err-%d@test.dev', $seq);

        // Valid, round-trippable phone: the author is re-hydrated from DB across handler invocations.
        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SESE-%d', $seq),
            $email,
            'super-password',
            'female',
            'Alice',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );

        $this->manager->persist($adherent);

        return $adherent;
    }
}
