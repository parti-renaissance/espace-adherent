<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ProgrammaticFoundation\Approach;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApproachData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $subApproach1 = $this->getReference('sub-approach-0')->setPosition(1);
        $subApproach2 = $this->getReference('sub-approach-1')->setPosition(2);

        $manager->persist($subApproach1);
        $manager->persist($subApproach2);

        $manager->persist(
            (new Approach(
                1,
                'Améliorer le quotidien',
                "Considérant cette rigueur induite, je suggère fortement d'anticiper systématiquement les stratégies imaginables, rapidement."
            ))
            ->addSubApproach($subApproach1)
            ->addSubApproach($subApproach2)
        );

        $subApproach3 = $this->getReference('sub-approach-2')->setPosition(1);
        $manager->persist($subApproach1);

        $manager->persist(
            (new Approach(
                2,
                'Changer de méthode pour changer les choses',
                "Malgré la dualité de la situation contextuelle, il faut uniformiser l'ensemble des options imaginables, à court terme.")
            )
            ->addSubApproach($subApproach3)
        );

        $subApproach4 = $this->getReference('sub-approach-3')->setPosition(1);
        $subApproach5 = $this->getReference('sub-approach-4')->setPosition(2);

        $manager->persist($subApproach4);
        $manager->persist($subApproach5);

        $manager->persist(
            (new Approach(
                3,
                'Faire de la commune un modèle écologique', "Si vous voulez mon avis concernant la baisse de confiance actuelle, je suggère fortement d'examiner la globalité des actions déclinables, parce qu'il est temps d'agir.")
            )
            ->addSubApproach($subApproach4)
            ->addSubApproach($subApproach5)
        );

        $subApproach6 = $this->getReference('sub-approach-5')->setPosition(1);
        $manager->persist($subApproach6);

        $manager->persist(
            (new Approach(
                4,
                'Préparer l\'avenir au-delà de notre mandat', "Quelle que soit l'inertie présente, il est préférable de prendre en considération systématiquement les décisions possibles, parce que nous ne faisons plus le même métier.")
            )
            ->addSubApproach($subApproach6)
        );

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadSubApproachData::class,
        ];
    }
}
