<?php

namespace AppBundle\Deputy\Subscriber;

use AppBundle\Entity\District;
use AppBundle\Membership\AdherentEvent;
use AppBundle\Membership\AdherentEvents;
use AppBundle\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BindAdherentDistrictSubscriber implements EventSubscriberInterface
{
    /**
     * @var DistrictRepository
     */
    private $districtRepository;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->districtRepository = $em->getRepository(District::class);
        $this->em = $em;
    }

    public function updateReferentTagWithDistrict(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        if ($adherent->isGeocoded()) {
            $referentTags = $this->districtRepository->findDistrictReferentTagByCoordinates(
                $adherent->getLatitude(),
                $adherent->getLongitude()
            );

            if (!empty($referentTags)) {
                foreach ($referentTags as $referentTag) {
                    if ($adherent->getReferentTags()->contains($referentTag)) {
                        continue;
                    }
                    $adherent->addReferentTag($referentTag);
                }
                $this->em->flush();
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['updateReferentTagWithDistrict', -257],
            AdherentEvents::PROFILE_UPDATED => ['updateReferentTagWithDistrict', -257],
        ];
    }
}
