<?php

namespace App\Deputy\Subscriber;

use App\Adherent\Listener\BindAdherentZoneSubscriber;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use App\Repository\DistrictRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @deprecated Will be replaced by {@see BindAdherentZoneSubscriber}
 */
class BindAdherentDistrictSubscriber implements EventSubscriberInterface
{
    private DistrictRepository $districtRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, DistrictRepository $repository)
    {
        $this->districtRepository = $repository;
        $this->em = $em;
    }

    public function updateReferentTagWithDistrict(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        if ($adherent->isForeignResident()) {
            $country = $adherent->getCountry();
            if ($district = $this->districtRepository->findDistrictCountryCode($country)) {
                $districts = [$district];
            }
        } elseif ($adherent->isGeocoded()) {
            $districts = $this->districtRepository->findDistrictsByCoordinates(
                $adherent->getLatitude(),
                $adherent->getLongitude()
            );
        }

        if (!empty($districts)) {
            foreach ($districts as $district) {
                if (!\in_array($adherent->getCountry(), $district->getCountries())) {
                    continue;
                }

                $adherent->addReferentTag($district->getReferentTag());
            }

            $this->em->flush();
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
