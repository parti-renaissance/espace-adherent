<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Consultation;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadConsultationData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $consultationRetraite = new Consultation(
            'Consultation sur les retraites',
            'https://fr.lipsum.com/',
            new \DateTime(),
            new \DateTime('tomorrow')
        );

        $this->addReference('consultation-retirement', $consultationRetraite);

        $manager->persist($consultationRetraite);

        $manager->flush();
    }
}
