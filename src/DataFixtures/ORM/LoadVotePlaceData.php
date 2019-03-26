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

        $manager->persist($this->createVotePlace(
            'Milan',
            '99999_0249',
            null,
            'Milan',
            'Ecole suisse de Milan',
            'IT'
        ));

        $manager->persist($this->createVotePlace(
            'Naples',
            '99999_0251',
            null,
            'Naples',
            'Consulat général de France à Naples',
            'IT'
        ));

        $this->addReference('vote-place-lille-wazemmes', $votePlaceLilleWazemmes);
        $this->addReference('vote-place-lille-jean-zay', $votePlaceLilleJeanZay);
        $this->addReference('vote-place-bobigny-blanqui', $votePlaceBobignyBlanqui);
        $this->addReference('vote-place-ecole-suisse-milan', $votePlaceLilleJeanZay);
        $this->addReference('vote-place-consulat-france-naples', $votePlaceLilleJeanZay);

        $manager->flush();
    }

    private function createVotePlace(
        string $name,
        string $code,
        ?string $postalCode,
        string $city,
        string $address,
        string $country = 'FR'
    ): VotePlace {
        $votePlace = new VotePlace();

        $votePlace->setName($name);
        $votePlace->setCode($code);
        $votePlace->setPostalCode($postalCode);
        $votePlace->setCity($city);
        $votePlace->setAddress($address);
        $votePlace->setCountry($country);

        return $votePlace;
    }
}
