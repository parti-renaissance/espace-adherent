<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CertificationRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCertificationRequestData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1');
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-2');
        /** @var Adherent $adherent3 */
        $adherent3 = $this->getReference('adherent-3');

        // Adherent certified without certification request
        $adherent1->certify();

        // Adherent with pending certification request
        $adherent2->startCertificationRequest();

        // Adherent with approved certification request
        $adherent3->startCertificationRequest();
        $adherent3->approveCertificationRequest();

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }

    private function createCertification(Adherent $adherent, string $status): CertificationRequest
    {
        $certificationRequest = new CertificationRequest($adherent);
        $adherent->setCertificationRequest($certificationRequest);

        return $certificationRequest;
    }
}
