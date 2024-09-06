<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentChangeEmailTokenData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1');
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-2');

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

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
