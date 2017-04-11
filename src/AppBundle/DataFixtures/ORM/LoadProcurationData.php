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
            'gender' => 'female',
            'first_names' => 'Fleur',
            'last_name' => 'Paré',
            'email' => 'FleurPare@armyspy.com',
            'address' => '13, rue Reine Elisabeth',
            'postalCode' => '77000',
            'city' => '77000-77288',
            'cityName' => null,
            'phone' => '33 169641061',
            'birthdate' => '1945-01-29',
            'voteCountry' => 'FR',
            'votePostalCode' => '75018',
            'voteCity' => '75018-75118',
            'voteCityName' => null,
            'voteOffice' => 'Aquarius',
            'electionPresidentialFirstRound' => false,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => false,
            'electionLegislativeSecondRound' => true,
            'reason' => ProcurationRequest::REASON_HEALTH,
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

        $manager->persist($request1 = $factory->createRequestFromArray([
            'gender' => 'male',
            'first_names' => 'William',
            'last_name' => 'Brunelle',
            'email' => 'WilliamBrunelle@dayrep.com',
            'address' => '59, Avenue De Marlioz',
            'postalCode' => '44000',
            'city' => '44000-44109',
            'cityName' => null,
            'phone' => '33 411809703',
            'birthdate' => '1964-01-16',
            'voteCountry' => 'FR',
            'votePostalCode' => '44000',
            'voteCity' => '44000-44109',
            'voteCityName' => null,
            'voteOffice' => 'Saighterse',
            'electionPresidentialFirstRound' => true,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => true,
            'electionLegislativeSecondRound' => true,
            'reason' => ProcurationRequest::REASON_HEALTH,
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
            'voteCity' => '75018-75120',
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
            'voteCity' => '75018-75118',
            'voteCityName' => null,
            'voteOffice' => 'Lycée général Zola',
            'electionPresidentialFirstRound' => true,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => true,
            'electionLegislativeSecondRound' => true,
        ]));

        $manager->persist($proxy1 = $factory->createProxyProposalFromArray([
            'referent' => $referent,
            'gender' => 'male',
            'first_names' => 'Benjamin',
            'last_name' => 'Robitaille',
            'email' => 'BenjaminRobitaille@teleworm.us',
            'address' => '47, place Stanislas',
            'postalCode' => '44100',
            'city' => '44100-44109',
            'cityName' => null,
            'phone' => '33 269692256',
            'birthdate' => '1969-10-17',
            'voteCountry' => 'FR',
            'votePostalCode' => '44100',
            'voteCity' => '44100-44109',
            'voteCityName' => null,
            'voteOffice' => 'Bentapair',
            'electionPresidentialFirstRound' => true,
            'electionPresidentialSecondRound' => true,
            'electionLegislativeFirstRound' => true,
            'electionLegislativeSecondRound' => true,
            'reliability' => 5,
            'reliabilityDescription' => 'Responsable procuration',
        ]));

        $manager->flush();

        $manager->refresh($request1);
        $manager->refresh($proxy1);
        $request1->process($proxy1, $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_4_UUID));

        $reflectionClass = new \ReflectionClass(ProcurationRequest::class);
        $reflectionProperty = $reflectionClass->getProperty('processedAt');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($request1, new \DateTime('-48 hours'));

        $manager->flush();
    }
}
