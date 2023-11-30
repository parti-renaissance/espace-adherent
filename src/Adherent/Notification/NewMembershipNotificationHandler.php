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

        $newSympathizers = $this->getNewSympathizers($zones, $from, $to);
        $newAdherents = $this->getNewAdherents($zones, $from, $to);

        if (empty($newSympathizers) && empty($newAdherents)) {
            return;
        }

        $this->sendNotification($manager, \count($newSympathizers), \count($newAdherents));
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

    /**
     * @return Adherent[]|array
     */
    private function getNewSympathizers(array $zones, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this
            ->adherentRepository
            ->getNewAdherents($zones, $from, $to, false, true)
        ;
    }

    /**
     * @return Adherent[]|array
     */
    private function getNewAdherents(array $zones, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this
            ->adherentRepository
            ->getNewAdherents($zones, $from, $to, true, false)
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
