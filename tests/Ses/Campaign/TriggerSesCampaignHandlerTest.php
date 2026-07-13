<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Handler\TriggerSesCampaignHandler;
use App\Ses\Campaign\Message\ReapStaleSendingRowsMessage;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class TriggerSesCampaignHandlerTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    public function testFanOutDispatchesOneMessagePerChunkAndClaimsSending(): void
    {
        $campaign = $this->createCampaign();
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->addMember($campaign, $this->createSubscribedAdherent(), 3, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $dispatched = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            // One message per chunk with work, plus the watchdog that recovers the rows a killed worker would
            // otherwise strand in Sending (see ReapStaleSendingRowsHandler).
            ->expects(self::exactly(3))
            ->method('dispatch')
            ->willReturnCallback(static function (object $msg) use (&$dispatched): Envelope {
                $dispatched[] = $msg;

                return new Envelope($msg);
            })
        ;

        $this->createHandler($bus)(new TriggerSesCampaignMessage($campaign->getId()));

        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));
        self::assertSame([1, 3], $this->dispatchedChunkNumbers($dispatched, $campaign->getId()));

        $watchdogs = array_filter($dispatched, static fn (object $msg): bool => $msg instanceof ReapStaleSendingRowsMessage);
        self::assertCount(1, $watchdogs, 'the send is watched from the moment it starts');
        self::assertSame($campaign->getId(), reset($watchdogs)->campaignId);
    }

    public function testNoSendableRecipientMarksErrorAndDoesNotFanOut(): void
    {
        $campaign = $this->createCampaign();
        // The only member is Refused -> not sendable, so the trigger fails without fanning out.
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Refused);
        $this->manager->flush();

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->createHandler($bus)(new TriggerSesCampaignMessage($campaign->getId()));

        self::assertSame(MailchimpStatusEnum::Error, $this->reloadStatus($campaign));
    }

    public function testRedeliveryDoesNotFanOutTwice(): void
    {
        $campaign = $this->createCampaign();
        // Already claimed (Sending): a redelivered trigger must be a no-op — no second fan-out.
        $campaign->status = MailchimpStatusEnum::Sending;
        $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::Added);
        $this->manager->flush();

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->createHandler($bus)(new TriggerSesCampaignMessage($campaign->getId()));

        self::assertSame(MailchimpStatusEnum::Sending, $this->reloadStatus($campaign));
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function createHandler(MessageBusInterface $bus): TriggerSesCampaignHandler
    {
        return new TriggerSesCampaignHandler(
            self::getContainer()->get(MailchimpCampaignRepository::class),
            self::getContainer()->get(MailchimpStaticSegmentMemberRepository::class),
            $bus,
            $this->manager,
        );
    }

    /**
     * @param list<object> $dispatched
     *
     * @return list<int> chunk numbers of the SendSesCampaignChunkMessage dispatched for this campaign, sorted
     */
    private function dispatchedChunkNumbers(array $dispatched, int $campaignId): array
    {
        $chunkNumbers = [];
        foreach ($dispatched as $message) {
            if (!$message instanceof SendSesCampaignChunkMessage) {
                continue;
            }

            self::assertSame($campaignId, $message->campaignId);
            $chunkNumbers[] = $message->chunkNumber;
        }
        sort($chunkNumbers);

        return $chunkNumbers;
    }

    private function createCampaign(): MailchimpCampaign
    {
        $message = new AdherentMessage();
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}</p>');

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

    private function reloadStatus(MailchimpCampaign $campaign): MailchimpStatusEnum
    {
        $this->manager->clear();

        return $this->getRepository(MailchimpCampaign::class)->find($campaign->getId())->status;
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-trigger-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SEST-%d', $seq),
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
