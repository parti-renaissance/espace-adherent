<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Guideline;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaGuidelineData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $guidelineMainFeature = new Guideline(
            'POUR COMMENCER : QUELLES SONT LES PRINCIPALES CARACTÉRISTIQUES DE VOTRE IDÉE ?'
        );
        $this->addReference('guideline-main-feature', $guidelineMainFeature);

        $guidelineImplementation = new Guideline(
            'POUR ALLER PLUS LOIN : VOTRE IDÉE PEUT-ELLE ÊTRE MISE EN OEUVRE ?'
        );
        $this->addReference('guideline-implementation', $guidelineImplementation);

        $manager->persist($guidelineMainFeature);
        $manager->persist($guidelineImplementation);

        $manager->flush();
    }
}
