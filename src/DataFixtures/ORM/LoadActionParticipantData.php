<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Action\Action;
use App\Entity\Action\ActionParticipant;
use App\Entity\Adherent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadActionParticipantData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 50; ++$i) {
            for ($p = 1; $p <= 10; ++$p) {
                $action = $this->getReference('action-'.$i, Action::class);
                $adherent = $this->getReference('adherent-'.(31 + $p), Adherent::class);
                $manager->persist(new ActionParticipant($action, $adherent));
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadActionData::class,
        ];
    }
}
