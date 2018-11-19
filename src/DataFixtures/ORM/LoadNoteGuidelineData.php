<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Guideline;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadNoteGuidelineData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $guidelineMainFeature = Guideline::create(
            'POUR COMMENCER : QUELLES SONT LES PRINCIPALES CARACTÉRISTIQUES DE VOTRE IDÉE ?'
        );
        $this->addReference('guideline-main-feature', $guidelineMainFeature);

        $guidelineImplementation = Guideline::create(
            'POUR ALLER PLUS LOIN : VOTRE IDÉE PEUT-ELLE ÊTRE MISE EN OEUVRE ?'
        );
        $this->addReference('guideline-implementation', $guidelineImplementation);

        $manager->persist($guidelineMainFeature);
        $manager->persist($guidelineImplementation);

        $manager->flush();
    }
}
