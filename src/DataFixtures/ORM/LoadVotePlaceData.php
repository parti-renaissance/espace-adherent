<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadVotePlaceData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($votePlaceLilleWazemmes = $this->createVotePlace(
            'Salle Polyvalente De Wazemmes',
            '59350_0113',
            '59000,59100',
            'Lille',
            "Rue De L'Abbé Aerts"
        ));

        $manager->persist($votePlaceLilleJeanZay = $this->createVotePlace(
            'Restaurant Scolaire - Rue H. Lefebvre',
            '59350_0407',
            '59350',
            'Lille',
            'Groupe Scolaire Jean Zay'
        ));

        $manager->persist($votePlaceBobignyBlanqui = $this->createVotePlace(
            'Ecole Maternelle La Source',
            '93066_0004',
            '93200,93066',
            'Saint-Denis',
            '15, Rue Auguste Blanqui'
        ));

        $this->addReference('vote-place-lille-wazemmes', $votePlaceLilleWazemmes);
        $this->addReference('vote-place-lille-jean-zay', $votePlaceLilleJeanZay);
        $this->addReference('vote-place-bobigny-blanqui', $votePlaceBobignyBlanqui);

        $manager->flush();
    }

    private function createVotePlace(
        string $name,
        string $code,
        string $postalCode,
        string $city,
        string $address
    ): VotePlace {
        $votePlace = new VotePlace();

        $votePlace->setName($name);
        $votePlace->setCode($code);
        $votePlace->setPostalCode($postalCode);
        $votePlace->setCity($city);
        $votePlace->setAddress($address);

        return $votePlace;
    }
}
