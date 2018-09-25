<?php

namespace AppBundle\Deputy\Subscriber;

use AppBundle\Entity\District;
use AppBundle\Membership\AdherentEvent;
use AppBundle\Membership\AdherentEvents;
use AppBundle\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChangeAddressSubscriber implements EventSubscriberInterface
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
            $referentTag = $this->districtRepository->findDistrictReferentTagByCoordinates(
                $adherent->getLatitude(),
                $adherent->getLongitude()
            );

            if ($referentTag && !$adherent->getReferentTags()->contains($referentTag)) {
                $adherent->addReferentTag($referentTag);
                $this->em->flush();
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['updateReferentTagWithDistrict', -257],
            AdherentEvents::PROFILE_UPDATED => ['updateReferentTagWithDistrict', -257],
        ];
    }
}
