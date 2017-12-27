<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Election;
use AppBundle\Entity\ElectionRound;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadElectionData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $presidentialElections = new Election();
        $presidentialElections->setName('Élections Présidentielles 2017');
        $presidentialElections->setPlace('France');

        $presidentialFirstRound = new ElectionRound();
        $presidentialFirstRound->setLabel('1er tour des éléctions présidentielles 2017');
        $presidentialFirstRound->setDescription(<<<EOD
Dimanche 24 avril 2017 en France (15 avril pour les Français de l'étranger du continent Américain et 16 avril pour les autres Français de l'étranger)
EOD
    );
        $presidentialFirstRound->setDate(date_create_from_format('m-d-Y', '24-04-2017'));

        $presidentialSecondRound = new ElectionRound();
        $presidentialSecondRound->setLabel('2e tour des éléctions présidentielles 2017');
        $presidentialSecondRound->setDescription(<<<EOD
Dimanche 7 mai 2017 en France (29 avril pour les Français de l'étranger du continent Américain et 30 avril pour les autres Français de l'étranger)
EOD
    );
        $presidentialSecondRound->setDate(date_create_from_format('m-d-Y', '07-05-2017'));

        $presidentialElections->addRound($presidentialFirstRound);
        $presidentialElections->addRound($presidentialSecondRound);

        $legislativeElections = new Election();
        $legislativeElections->setName('Élections Législatives 2017');
        $legislativeElections->setPlace('France');

        $legislativeFirstRound = new ElectionRound();
        $legislativeFirstRound->setLabel('1er tour des éléctions législatives 2017');
        $legislativeFirstRound->setDescription(<<<EOD
Dimanche 11 juin 2017 en France (3 juin pour les Français de l'étranger du continent Américain et 4 juin pour les autres Français de l'étranger)
EOD
        );
        $legislativeFirstRound->setDate(date_create_from_format('m-d-Y', '11-06-2017'));

        $legislativeSecondRound = new ElectionRound();
        $legislativeSecondRound->setLabel('2e tour des éléctions législatives 2017');
        $legislativeSecondRound->setDescription(<<<EOD
Dimanche 18 juin 2017 en France (17 juin pour les Français de l'étranger du continent Américain et 18 juin pour les autres Français de l'étranger)
EOD
        );
        $legislativeSecondRound->setDate(date_create_from_format('m-d-Y', '18-06-2017'));

        $legislativeElections->addRound($legislativeFirstRound);
        $legislativeElections->addRound($legislativeSecondRound);

        $manager->persist($presidentialElections);
        $manager->persist($legislativeElections);
        $manager->flush();
    }
}
