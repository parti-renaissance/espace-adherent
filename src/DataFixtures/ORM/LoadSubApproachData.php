<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ProgrammaticFoundation\SubApproach;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSubApproachData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $descriptionPool = [
            "En ce qui concerne la baisse de confiance conjoncturelle, on se doit d'analyser systématiquement les ouvertures imaginables, avec toute la prudence requise.",
            "Où que nous mène la fragilité conjoncturelle, il serait intéressant de se remémorer l'ensemble des décisions imaginables, pour longtemps.",
            "Afin de circonvenir à la restriction intrinsèque, je n'exclus pas de comprendre toutes les stratégies emblématiques, à long terme.",
            'Afin de circonvenir à la restriction intrinsèque, on ne peut se passer de comprendre la totalité des organisations matricielles envisageables, pour longtemps.',
        ];

        for ($i = 0; $i < 6; ++$i) {
            $subtitle = 0 === $i % 2 ? 'Subtitle lorem' : '';
            $approach = (new SubApproach(
                $i + 1,
                sprintf('Sous-axe lorem %d', $i + 1),
                $subtitle,
                $descriptionPool[$i % 4],
                0 === $i
            ));

            $measure1 = $this->getReference(sprintf('sub-approach-measure-%d', 2 * $i))
                      ->setPosition(1)
            ;
            $measure2 = $this->getReference(sprintf('sub-approach-measure-%d', 2 * $i + 1))
                      ->setPosition(2)
            ;

            $manager->persist($measure1);
            $manager->persist($measure2);

            $approach
                ->addMeasure($measure1)
                ->addMeasure($measure2)
            ;

            $manager->persist($approach);
            $this->addReference("sub-approach-$i", $approach);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadApproachMeasureData::class,
        ];
    }
}
