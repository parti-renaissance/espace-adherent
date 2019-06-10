<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumberUtil;

class LoadApplicationRequestRunningMateRequestData extends Fixture
{
    private const EMAILS = ['bruce.banner@gmail.com', 'damien.schmidt@example.ch'];

    public function load(ObjectManager $manager)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        $municipal1 = $this->getReference('municipal-chief-1');
        $municipal2 = $this->getReference('municipal-chief-2');
        $municipal3 = $this->getReference('municipal-chief-3');
        $i = 0;
        while ($i < 4) {
            $email = self::EMAILS[$i % 2];
            $runningMateRequest = new RunningMateRequest();

            switch ($i) {
                case 0:
                    $favoriteCities = [
                        $municipal1->municipalChiefManagedArea()->getCodes()[0],
                        $municipal2->municipalChiefManagedArea()->getCodes()[1],
                    ];
                    break;
                case 1:
                    $favoriteCities = [
                        $municipal2->municipalChiefManagedArea()->getCodes()[0],
                        $municipal3->municipalChiefManagedArea()->getCodes()[1],
                    ];
                    break;
                case 2:
                    $favoriteCities = [
                        $municipal1->municipalChiefManagedArea()->getCodes()[2],
                        $municipal3->municipalChiefManagedArea()->getCodes()[0],
                    ];
                    break;
                default:
                    $favoriteCities = [
                        $municipal1->municipalChiefManagedArea()->getCodes()[2],
                    ];
                    break;
            }

            $runningMateRequest->setFavoriteCities($favoriteCities);
            $runningMateRequest->setFirstName('Bruce');
            $runningMateRequest->setLastName('Banner');
            $runningMateRequest->setEmailAddress($email);
            $runningMateRequest->setPostalCode('10001');
            $runningMateRequest->setCityName('New York City');
            $runningMateRequest->setCountry('US');
            $runningMateRequest->setAddress('890 Fifth Avenue, Manhattan');
            $runningMateRequest->setPhone($phoneUtil->parse('06-06-06-06-06', 'FR'));
            $runningMateRequest->setProfession('Scientist');

            $runningMateRequest->setCurriculumName('cv.pdf');

            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-06'));
            $runningMateRequest->addFavoriteTheme($this->getReference('application-theme-08'));
            $runningMateRequest->setFavoriteThemeDetails('');
            $runningMateRequest->setCustomFavoriteTheme('Thanos destruction');

            $runningMateRequest->setIsLocalAssociationMember(false);
            $runningMateRequest->setIsPreviousElectedOfficial(false);
            $runningMateRequest->setPreviousElectedOfficialDetails('');
            $runningMateRequest->setLocalAssociationDomain('Fighting super villains');
            $runningMateRequest->setPoliticalActivistDetails('Putsch Thanos from his galactic throne');
            $runningMateRequest->setIsPoliticalActivist(false);
            $runningMateRequest->setProjectDetails('');
            $runningMateRequest->setProfessionalAssets('');

            $runningMateRequest->addReferentTag($this->getReference(sprintf('referent_tag_%s', (bool) ($i % 2) ? '75' : '62')));

            $manager->persist($runningMateRequest);
            ++$i;
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadApplicationRequestThemeData::class,
        ];
    }
}
