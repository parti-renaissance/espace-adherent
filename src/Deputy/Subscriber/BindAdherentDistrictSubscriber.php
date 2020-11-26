<?php

namespace App\Deputy\Subscriber;

use App\Entity\District;
use App\Entity\Geo\Zone;
use App\Membership\AdherentEvent;
use App\Membership\AdherentEvents;
use App\Repository\DistrictRepository;
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
            $districts = $this->districtRepository->findDistrictsByCoordinates(
                $adherent->getLatitude(),
                $adherent->getLongitude()
            );
            if (!empty($districts)) {
                $zone = null;
                $zoneTypes = [Zone::DISTRICT, Zone::FOREIGN_DISTRICT];

                foreach ($districts as $district) {
                    if (!\in_array($adherent->getCountry(), $district->getCountries())) {
                        continue;
                    }

                    $adherent->addReferentTag($district->getReferentTag());

                    if (!$zone) {
                        $foundZone = $district->getReferentTag()->getZone();
                        if (\in_array($foundZone->getType(), $zoneTypes, true)) {
                            $zone = $foundZone;
                        }
                    } // else { @todo inconstancy }
                }

                if ($zone) {
                    $zonesToRemove = $adherent->getZones()->filter(static function (Zone $zone) use ($zoneTypes): bool {
                        return \in_array($zone->getType(), $zoneTypes, true);
                    });

                    foreach ($zonesToRemove as $toRemove) {
                        $adherent->removeZone($toRemove);
                    }

                    $adherent->addZone($zone);
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
