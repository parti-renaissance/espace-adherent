<?php

namespace App\DataFixtures\ORM;

use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTerritorialCouncilData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /* @var ReferentTag $referentTagFde */
        $referentTagFde = $this->getReference('referent_tag_fde');
        $this->createTerritorialCouncilFromReferentTag($manager, $referentTagFde);

        /* @var ReferentTag $referentTagCorsica */
        $referentTagCorsica = $this->getReference('referent_tag_corsica');
        $this->createTerritorialCouncilFromReferentTag($manager, $referentTagCorsica);

        $referentTags = $manager->getRepository(ReferentTag::class)->findBy([
            'type' => [
                ReferentTag::TYPE_DEPARTMENT,
                ReferentTag::TYPE_DISTRICT,
                ReferentTag::TYPE_BOROUGH,
            ],
        ]);

        foreach ($referentTags as $referentTag) {
            $this->createTerritorialCouncilFromReferentTag($manager, $referentTag);
        }

        $manager->flush();
    }

    private function createTerritorialCouncilFromReferentTag(ObjectManager $manager, ReferentTag $referentTag): void
    {
        $code = $referentTag->getCode();
        $name = sprintf('Conseil territorial du %s (%s)', $referentTag->getName(), $code);
        $territorialCouncil = new TerritorialCouncil($name, $code);
        $territorialCouncil->addReferentTag($referentTag);

        $manager->persist($territorialCouncil);
        $this->addReference('coTerr_'.$territorialCouncil->getCodes(), $territorialCouncil);
    }

    public function getDependencies(): array
    {
        return [
            LoadReferentData::class,
            LoadReferentTagData::class,
        ];
    }
}
