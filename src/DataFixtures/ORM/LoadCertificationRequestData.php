<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
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
        /** @var Adherent $adherent4 */
        $adherent4 = $this->getReference('adherent-4');

        /** @var Administrator $administrator */
        $administrator = $this->getReference('administrator-2');

        // Adherent certified without certification request
        $adherent1->certify();

        // Adherent with pending certification request
        $adherent2->startCertificationRequest();

        // Adherent with refused then approved certification request
        $adherent3->startCertificationRequest();
        $adherent3->getPendingCertificationRequest()->setProcessedBy($administrator);
        $adherent3->refuseCertificationRequest();

        $adherent3->startCertificationRequest();
        $adherent3->getPendingCertificationRequest()->setProcessedBy($administrator);
        $adherent3->approveCertificationRequest();

        // Adherent with refused certification request
        $adherent4->startCertificationRequest();
        $adherent4->getPendingCertificationRequest()->setProcessedBy($administrator);
        $adherent4->refuseCertificationRequest();

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
        ];
    }
}
