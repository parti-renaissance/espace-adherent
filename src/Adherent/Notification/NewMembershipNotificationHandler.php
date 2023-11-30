<?php

namespace App\Adherent\Notification;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceNewAdherentsNotificationMessage;
use App\Repository\AdherentRepository;

class NewMembershipNotificationHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly MailerService $transactionalMailer,
        private readonly string $jemengageHost
    ) {
    }

    public function handle(Adherent $manager, \DateTimeInterface $from, \DateTimeInterface $to): void
    {
        $zones = $this->getZonesToNotifyForManager($manager);

        if (empty($zones)) {
            return;
        }

        $newSympathizers = $this->countNewSympathizers($zones, $from, $to);
        $newAdherents = $this->countNewAdherents($zones, $from, $to);

        if (!$newSympathizers && !$newAdherents) {
            return;
        }

        $this->sendNotification($manager, $newSympathizers, $newAdherents);
    }

    /**
     * @return Zone[]|array
     */
    private function getZonesToNotifyForManager(Adherent $manager): array
    {
        $zones = [];

        if ($manager->isPresidentDepartmentalAssembly()) {
            $zones = array_merge($zones, $manager->getPresidentDepartmentalAssemblyZones());
        }

        if ($manager->isAnimator()) {
            foreach ($manager->getAnimatorCommittees() as $committee) {
                $zones = array_merge($zones, $committee->getZonesOfType(Zone::DEPARTMENT, true));
            }
        }

        return $zones;
    }

    private function countNewSympathizers(array $zones, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this
            ->adherentRepository
            ->countNewAdherents($zones, $from, $to, false, true)
        ;
    }

    private function countNewAdherents(array $zones, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this
            ->adherentRepository
            ->countNewAdherents($zones, $from, $to, true, false)
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
        return '//'.$this->jemengageHost.'/militants';
    }
}
