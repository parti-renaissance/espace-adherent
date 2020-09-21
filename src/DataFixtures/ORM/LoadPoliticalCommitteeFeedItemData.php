<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPoliticalCommitteeFeedItemData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var TerritorialCouncil $coTerrParis */
        $coTerrParis = $this->getReference('coTerr_75');
        $referent = $this->getReference('adherent-19');

        $feedItem = new PoliticalCommitteeFeedItem(
            $coTerrParis,
            $referent,
            <<<EOD
<p> Lorem <strong>Ipsum</strong></p>
<ol>
    <li>dolor augue</li>
	<li>aliquam</li>
	<li>rutrum arcu nulla</li>
</ol>
<h2><strong>Vivamus nulla ligula,</strong> Suspendisse condimentum vulputate magna.</h2>
<p>Nulla viverra felis quis ullamcorper porttitor.</p>
EOD
        );
        $manager->persist($feedItem);

        for ($day = 1; $day < 12; ++$day) {
            $feedItem = new PoliticalCommitteeFeedItem(
                $coTerrParis,
                $referent,
                "<p>Message du référent $day dans le CoPol</p>",
                "-$day days"
            );
            $manager->persist($feedItem);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadTerritorialCouncilData::class,
            LoadTerritorialCouncilMembershipData::class,
        ];
    }
}
