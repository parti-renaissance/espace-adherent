<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadProcurationData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('app.procuration.factory');

        $manager->persist($factory->createRequestFromArray([
            'gender' => 'male',
            'first_names' => 'Timothé, Jean, Marcel',
            'last_name' => 'Baumé',
            'email' => 'timothe.baume@example.gb',
            'address' => '100 Roy Square, Main Street',
            'postalCode' => 'E14 8BY',
            'city' => null,
            'cityName' => 'London',
            'phone' => '44 9999888111',
            'birthdate' => '1972-11-23',
            'voteCountry' => 'GB',
            'votePostalCode' => 'E14 8BY',
            'voteCity' => null,
            'voteCityName' => 'London',
            'voteOffice' => 'Lycée international Winston Churchill',
            'electionPresidentialFirstRound' => true,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => false,
            'electionLegislativeSecondRound' => false,
            'reason' => ProcurationRequest::REASON_HOLIDAYS,
        ]));

        $manager->persist($factory->createRequestFromArray([
            'gender' => 'female',
            'first_names' => 'Carine, Margaux',
            'last_name' => 'Édouard',
            'email' => 'caroline.edouard@example.fr',
            'address' => '165 rue Marcadet',
            'postalCode' => '75018',
            'city' => '75018-75118',
            'cityName' => null,
            'phone' => '33 655443322',
            'birthdate' => '1968-10-09',
            'voteCountry' => 'FR',
            'votePostalCode' => '75018',
            'voteCity' => '75018-75118',
            'voteCityName' => null,
            'voteOffice' => 'Damremont',
            'electionPresidentialFirstRound' => true,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => true,
            'electionLegislativeSecondRound' => true,
            'reason' => ProcurationRequest::REASON_RESIDENCY,
        ]));

        $manager->persist($factory->createRequestFromArray([
            'gender' => 'male',
            'first_names' => 'Kevin',
            'last_name' => 'Delcroix',
            'email' => 'kevin.delcroix@example.fr',
            'address' => '165 rue Marcadet',
            'postalCode' => '75018',
            'city' => '75018-75118',
            'cityName' => null,
            'phone' => '33 988776655',
            'birthdate' => '1991-01-18',
            'voteCountry' => 'FR',
            'votePostalCode' => '92110',
            'voteCity' => '92110-92024',
            'voteCityName' => null,
            'voteOffice' => 'Mairie',
            'electionPresidentialFirstRound' => false,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => false,
            'electionLegislativeSecondRound' => true,
            'reason' => ProcurationRequest::REASON_HELP,
        ]));

        $referent = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_8_UUID);

        $manager->persist($factory->createProxyProposalFromArray([
            'referent' => $referent,
            'gender' => 'male',
            'first_names' => 'Maxime',
            'last_name' => 'Michaux',
            'email' => 'maxime.michaux@example.fr',
            'address' => '14 rue Jules Ferry',
            'postalCode' => '75018',
            'city' => '75020-75120',
            'cityName' => null,
            'phone' => '33 988776655',
            'birthdate' => '1989-02-17',
            'voteCountry' => 'FR',
            'votePostalCode' => '75018',
            'voteCity' => '75020-75120',
            'voteCityName' => null,
            'voteOffice' => 'Mairie',
            'electionPresidentialFirstRound' => false,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => false,
            'electionLegislativeSecondRound' => true,
        ]));

        $manager->persist($factory->createProxyProposalFromArray([
            'referent' => $referent,
            'gender' => 'male',
            'first_names' => 'Jean-Michel',
            'last_name' => 'Carbonneau',
            'email' => 'jm.carbonneau@example.fr',
            'address' => '14 rue Jules Ferry',
            'postalCode' => '75018',
            'city' => '75020-75120',
            'cityName' => null,
            'phone' => '33 988776655',
            'birthdate' => '1974-01-17',
            'voteCountry' => 'FR',
            'votePostalCode' => '75018',
            'voteCity' => '75020-75120',
            'voteCityName' => null,
            'voteOffice' => 'Lycée général Zola',
            'electionPresidentialFirstRound' => true,
            'electionPresidentialSecondRound' => false,
            'electionLegislativeFirstRound' => true,
            'electionLegislativeSecondRound' => false,
        ]));

        $manager->flush();
    }
}
