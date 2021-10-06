<?php

namespace App\DataFixtures\ORM;

use App\Entity\AdherentEmailSubscribeToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentEmailSubscribeTokenData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var AdherentEmailSubscribeToken $token */
        $manager->persist($token = AdherentEmailSubscribeToken::create(
            $this->getReference('adherent-31')->getUuid()->toString(),
            sha1('etp3DZY_mph2pzq_mkb'),
            '+1 year'
        ));

        $token->setTriggerSource(AdherentEmailSubscribeToken::TRIGGER_SOURCE_ADMIN);
        $token->setCreatedByAdministrator($this->getReference('administrator-2'));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadAdminData::class,
        ];
    }
}
