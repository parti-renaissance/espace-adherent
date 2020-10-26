<?php

namespace App\DataFixtures\ORM;

use App\Entity\RepublicanSilence;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadRepublicanSilenceData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $entity = new RepublicanSilence();
        $entity->addReferentTag($this->getReference('referent_tag_borough_75101'));
        $entity->addReferentTag($this->getReference('referent_tag_department_91'));
        $entity->addReferentTag($this->getReference('referent_tag_department_93'));
        $entity->addReferentTag($this->getReference('referent_tag_country_SG'));
        $entity->setBeginAt(new \DateTime('-10 days'));
        $entity->setFinishAt(new \DateTime('+10 days'));

        $manager->persist($entity);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadReferentTagData::class,
        ];
    }
}
