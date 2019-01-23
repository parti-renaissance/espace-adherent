<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Consultation;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaConsultationData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $consultationRetirement = new Consultation(
            'Consultation sur les retraites',
            'https://fr.lipsum.com/',
            new \DateTime('-4 days'),
            new \DateTime('tomorrow'),
            2,
            true
        );

        $this->addReference('consultation-retirement', $consultationRetirement);

        $consultationHousingPolicy = new Consultation(
            'Consultation sur la politique du logement',
            'https://google.fr/',
            new \DateTime('-3 days'),
            new \DateTime('yesterday'),
            3
        );

        $this->addReference('consultation-housing-policy', $consultationHousingPolicy);

        $manager->persist($consultationRetirement);
        $manager->persist($consultationHousingPolicy);

        $manager->flush();
    }
}
