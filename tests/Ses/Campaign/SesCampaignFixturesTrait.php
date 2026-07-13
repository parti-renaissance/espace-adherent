<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageReach;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Entity\AdherentMessage\MailchimpStaticSegmentMember;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Membership\ActivityPositionsEnum;
use libphonenumber\PhoneNumber;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Audience fixtures and Messenger spies shared by the SES campaign tests: the send path and the
 * reconciliation of its quarantined rows exercise the same staging rows, and duplicating this harness across
 * both test classes is what made them drift apart in the first place.
 */
trait SesCampaignFixturesTrait
{
    private int $seq = 0;

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
    ): MailchimpStaticSegmentMember {
        $member = new MailchimpStaticSegmentMember($campaign->getMailchimpStaticSegment(), $adherent, $chunkNumber);
        $member->processingStatus = $status;

        $this->manager->persist($member);

        return $member;
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
        $email = \sprintf('ses-chunk-%d@test.dev', $seq);

        // Valid, round-trippable phone: the author is re-hydrated from DB across handler invocations.
        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-%d', $seq),
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

    /** @param list<array{message: object, stamps: array}> $dispatched */
    private function spyBus(array &$dispatched): MessageBusInterface
    {
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (object $message, array $stamps = []) use (&$dispatched): Envelope {
            $dispatched[] = ['message' => $message, 'stamps' => $stamps];

            return new Envelope($message);
        });

        return $bus;
    }

    /**
     * @param list<array{message: object, stamps: array}> $dispatched
     *
     * @return list<array{message: object, stamps: array}>
     */
    private function dispatchedOfType(array $dispatched, string $class): array
    {
        return array_values(array_filter($dispatched, static fn (array $entry): bool => $entry['message'] instanceof $class));
    }

    private function hasDelayStamp(array $stamps): bool
    {
        foreach ($stamps as $stamp) {
            if ($stamp instanceof DelayStamp) {
                return true;
            }
        }

        return false;
    }
}
