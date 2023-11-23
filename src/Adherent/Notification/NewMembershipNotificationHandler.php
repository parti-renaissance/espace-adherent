<?php

namespace App\Adherent\Notification;

use App\Entity\Adherent;
use App\Entity\AdherentNotification;
use App\Entity\Geo\Zone;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceNewAdherentsNotificationMessage;
use App\Repository\AdherentRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;

class NewMembershipNotificationHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ZoneRepository $zoneRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly MailerService $transactionalMailer,
        private readonly string $jemengageHost
    ) {
    }

    public function handle(Adherent $manager): void
    {
        $zones = $this->getZonesToNotifyForManager($manager);

        $newSympathizers = $this->getNewSympathizers($zones);
        $newAdherents = $this->getNewAdherents($zones);

        if (empty($newSympathizers) && empty($newAdherents)) {
            return;
        }

        $this->sendNotification($manager, \count($newSympathizers), \count($newAdherents));

        $this->saveNotificationHistories($newSympathizers, NotificationTypeEnum::NEW_SYMPATHISER);
        $this->saveNotificationHistories($newAdherents, NotificationTypeEnum::NEW_MEMBERSHIP);
    }

    /**
     * @return Zone[]|array
     */
    private function getZonesToNotifyForManager(Adherent $manager): array
    {
        $zones = [];

        if ($manager->isSenator()) {
            $zone = $this->zoneRepository->findOneBy([
                'type' => Zone::DEPARTMENT,
                'code' => $manager->getSenatorArea()->getDepartmentTag(),
            ]);

            if ($zone) {
                $zones[] = $zone;
            }
        }

        if ($manager->isDeputy()) {
            $zone = $manager->getDeputyZone();

            if ($zone) {
                $zones[] = $zone;
            }
        }

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
    private function getNewSympathizers(array $zones): array
    {
        return $this
            ->adherentRepository
            ->getAdherentsWithoutNotificationType(
                $zones,
                NotificationTypeEnum::NEW_SYMPATHISER,
                false,
                true
            )
        ;
    }

    /**
     * @return Adherent[]|array
     */
    private function getNewAdherents(array $zones): array
    {
        return $this
            ->adherentRepository
            ->getAdherentsWithoutNotificationType(
                $zones,
                NotificationTypeEnum::NEW_MEMBERSHIP,
                true,
                false
            )
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

    private function saveNotificationHistories(array $adherents, NotificationTypeEnum $notificationType): void
    {
        foreach ($adherents as $adherent) {
            $this->entityManager->persist(new AdherentNotification($adherent, $notificationType));
        }

        $this->entityManager->flush();
    }
}
