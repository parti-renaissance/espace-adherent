<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentChangeEmailTokenData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adherent1 = $this->getReference('adherent-1', Adherent::class);
        $adherent2 = $this->getReference('adherent-2', Adherent::class);

        // Unused token
        $token1 = AdherentChangeEmailToken::generate($adherent1);
        $token1->setEmail('michelle.dufour-new@example.ch');

        $manager->persist($token1);

        // Used token
        $token2 = AdherentChangeEmailToken::generate($adherent2);
        $token2->setEmail('carl999-new@example.fr');
        $token2->invalidate();

        $manager->persist($token2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
