<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Scale;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadScaleData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $europeanScale = new Scale(
            'Européenne',
            true
        );

        $this->addReference('scale-european', $europeanScale);

        $nationalScale = new Scale(
            'National',
            true
        );

        $this->addReference('scale-national', $nationalScale);

        $localScale = new Scale(
            'Local',
            true
        );

        $this->addReference('scale-local', $localScale);

        $notPublishedScale = new Scale(
            'Not published scale'
        );

        $this->addReference('scale-not-published', $notPublishedScale);

        $manager->persist($europeanScale);
        $manager->persist($nationalScale);
        $manager->persist($localScale);
        $manager->persist($notPublishedScale);

        $manager->flush();
    }
}
