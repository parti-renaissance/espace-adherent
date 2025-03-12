<?php

namespace App\Adherent\Notification;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceNewAdherentsNotificationMessage;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeMembershipRepository;

class NewMembershipNotificationHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly MailerService $transactionalMailer,
        private readonly string $jemengageHost,
    ) {
    }

    public function handle(Adherent $manager, \DateTimeInterface $from, \DateTimeInterface $to): void
    {
        $newSympathizers = $newAdherents = 0;

        if (
            $manager->isPresidentDepartmentalAssembly()
            || $manager->isDeputy()
        ) {
            $zones = $manager->isPresidentDepartmentalAssembly()
                ? $manager->getPresidentDepartmentalAssemblyZones()
                : [$manager->getDeputyZone()];

            $newSympathizers = $this->countNewSympathizersInZones($zones, $from, $to);
            $newAdherents = $this->countNewAdherentsInZones($zones, $from, $to);
        } elseif ($manager->isAnimator()) {
            foreach ($manager->getAnimatorCommittees() as $committee) {
                $newSympathizers += $this->countNewSympathizersInCommittee($committee, $from, $to);
                $newAdherents += $this->countNewAdherentsInCommittee($committee, $from, $to);
            }
        }

        if (!$newSympathizers && !$newAdherents) {
            return;
        }

        $this->sendNotification($manager, $newSympathizers, $newAdherents);
    }

    private function countNewSympathizersInZones(array $zones, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this
            ->adherentRepository
            ->countNewAdherents($zones, $from, $to, false, true)
        ;
    }

    private function countNewAdherentsInZones(array $zones, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this
            ->adherentRepository
            ->countNewAdherents($zones, $from, $to, true, false)
        ;
    }

    private function countNewSympathizersInCommittee(Committee $committee, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this
            ->committeeMembershipRepository
            ->countNewMembers($committee, $from, $to, false, true)
        ;
    }

    private function countNewAdherentsInCommittee(Committee $committee, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this
            ->committeeMembershipRepository
            ->countNewMembers($committee, $from, $to, true, false)
        ;
    }

    private function sendNotification(Adherent $adherent, int $newSympathizersCount, int $newAdherentsCount): void
    {
        $this->transactionalMailer->sendMessage(RenaissanceNewAdherentsNotificationMessage::create(
            $adherent,
            $newSympathizersCount,
            $newAdherentsCount,
            $this->generateJMEMilitantsUrl()
        ));
    }

    private function generateJMEMilitantsUrl(): string
    {
        return $this->jemengageHost.'/militants';
    }
}
